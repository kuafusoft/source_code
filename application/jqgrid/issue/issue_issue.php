<?php

require_once("Spreadsheet/Excel/reader.php");
require_once('jqgridmodel.php');

class issue_issue extends jqGridModel{
    var $currentUser;
    public function init($controller, $options){
        $options['db'] = 'issue';
        $options['table'] = 'issue';
        $options['relations']['belongsto'] = array('issue_state', 'issue_type', 'product');

        $options['columns'] = array(
            'id'=>array('editable'=>false, 'hidden'=>true),
            'product_id',
            'issue_type_id',
            'summary',
            'detail',
            'issue_state_id',
            'assigned_to'=>array('is_userId'=>true),
            'keywords',
            'submitter_id'=>array('is_userId'=>true),
            'created',
        );
        $options['ver'] = '1.0';
        parent::init($controller, $options);
    } 

    function contextMenu(){
        $menu = array(
            'information'=>array('Show Information'),
            'interview'=>array('Interview'),
            'attach'=>array('Attach Resume'),
            'download'=>array('Download Resume'),
        );
        $menu = array_merge($menu, parent::contextMenu());
        return $menu;
    }
    
    public function getButtons(){
        $buttons = array(
            'upload'=>array('caption'=>'upload',
                         'buttonimg'=>'',
                         'title'=>'upload resume',
                         'onClickButton'=>'candidate_buttonActions',
                        ),
        );
        return array_merge($buttons, parent::getButtons());
    }
    
    public function information(){
        $params = $this->tool->parseParams('candidate_information');
        $view = new Zend_View();
        $viewInfo = array();
//print_r($params);    
        $sql = "SELECT * FROM candidate WHERE id=".$params['element'];
        $res = $this->db->query($sql);
        $row = $res->fetch();
        $viewInfo['baseInformation'] = $row;
                 
        // get the interview data                 
        $sql = "SELECT * from vw_interview where candidate_id=".$params['element'];
//print_r($sql);            
        $res = $this->db->query($sql);
        $rows = $res->fetchAll();
        $viewInfo['interview'] = $rows;
        
        $this->renderView('candidate_information.php', $viewInfo);
    }
    
    public function interview(){
        $params = $this->tool->parseParams('candidate_interview');
        if ($this->controller->getRequest()->isPost()){
            $userAdmin = new Application_Model_Useradmin($this);
            $userInfo = $userAdmin->getUserInfo();
            $currentUser = $userInfo->id;
            $interview = array('comment'=>$params['comment'], 'interview_date'=>date('Y-m-d'), 'candidate_id'=>$params['element'], 'interviewer_id'=>$currentUser);
            print_r($params);
            $this->db->insert('interview', $interview);
            $interview_id = $this->db->lastInsertId();
            foreach($params['tags'] as $key=>$v){
                $this->db->insert('interview_result', array('interview_id'=>$interview_id, 'interview_tag_id'=>$v, 'grade_id'=>$params['grades'][$key]));
            }
        }
        else{
            $result = $this->db->query("SELECT * FROM candidate WHERE id=".$params['element']);
            $candidate = $result->fetch();
            $result = $this->db->query("SELECT * FROM grade");
            $grades = $result->fetchAll();
            $result = $this->db->query("SELECT * FROM interview_tag");
            $tags = $result->fetchAll();
            $result = $this->db->query("SELECT * FROM interview_tag_category");
            $tagCategories = $result->fetchAll();
            $this->renderView('candidate_interview.php', compact('candidate', 'grades', 'tags', 'tagCategories'));
        }
    }
    
    public function upload(){
        $uploadDir = UPLOAD_ROOT."/resume";
        $dest = $uploadDir."/".$_FILES['candidate_resume']['name'];
        $dest = $this->tool->moveFile($_FILES['candidate_resume']['tmp_name'], $dest);
        $path_parts = pathinfo($dest);
//print_r($path_parts);
        switch($path_parts['extension']){
            case 'xls': // check if it's the 51job format resume
                $this->parseXls($dest);
                break;
            case 'doc':
                break;
            default:
                break;
                
        }
    }
    
    public function download(){
        $params = $this->tool->parseParams('candidate_attach');
        print_r($params);
        $result = $this->db->query("SELECT * FROM candidate WHERE id=".$params['id']);
        $row = $result->fetch();
        if (!empty($row['cv_doc'])){
        	$file = @ fopen($row['cv_doc'],"r"); 
        //	PrintDebug($argValues["filename"]);
    	    if (!$file){
    		    echo "Can not open file:".$row['cv_doc']; 
    	    } 
            else{ 
        		Header("Content-type: application/octet-stream");
        		Header("Content-Disposition: attachment; filename=\"".basename($row['cv_doc'])."\"");
        		while (!feof ($file)) { 
        			echo fread($file,500000); 
        		} 
        		fclose ($file); 
        	}
        }
    }
    
    public function attach(){
        $params = $this->tool->parseParams('candidate_attach');
//        print_r($params);
//print_r($_FILES);
        $uploadDir = UPLOAD_ROOT."/resume";
        $dest = $uploadDir."/".$_FILES['attach_resume']['name'];
        $dest = $this->tool->moveFile($_FILES['attach_resume']['tmp_name'], $dest);
        $this->db->update('candidate', array('cv_doc'=>$dest), "id=".$params['element']);
    }
    
    public function getTags(){
        $params = $this->tool->parseParams('candidate_getTags');
        if (empty($params['category_id']))
            $result = $this->db->query("SELECT * FROM interview_tag WHERE 1");
        else
            $result = $this->db->query("SELECT * FROM interview_tag WHERE interview_tag_category_id=".$params['category_id']);
        
        print_r(json_encode($result->fetchAll()));
    }
    
    public function parseXls($fileName){
        $reader = new Spreadsheet_Excel_Reader();
        $reader->setUTFEncoder('iconv');
        $reader->setOutputEncoding('UTF-8');
        $reader->read($fileName);
        foreach($reader->sheets as $k=>$data){
            if (strtolower($reader->boundsheets[$k]['name']) == 'userlist'){ // suppose it's 51job resume
                $titles = array('name'=>1, 'cv_no'=>2, 'desired_position'=>3, 'desired_employer'=>4, 'desired_city'=>5, 
                    'gender'=>7, 'birthdate'=>8, 'current_city'=>9, 'hukou'=>10, 'work_started_year'=>11, 
                    'education_degree'=>12, 'graduate_school'=>13, 'speciality'=>14, 'mobile'=>15, 'email'=>16,
                    'last_employer'=>19, 'last_position'=>20, 'current_salary'=>21, 'expected_salary'=>22);
                foreach($data['cells'] as $line=>$row){
                    if (empty($row))
                        continue;
                    if ($line == 1){
                        continue;
                    }
                    
                    try{
                        $candidate = array('cv_doc'=>$fileName);
                        foreach($titles as $key=>$v){
                            $candidate[$key] = $row[$v];
                        }
//print_r($candidate);
                        // transfer education_degree to id
                        $candidate['education_degree_id'] = 1;
                        unset($candidate['education_degree']);    
                        
                        $candidate['work_started_year'] = 1; 
                        $candidate['gender'] = 1;               
                        if (!is_numeric ($candidate['expected_salary']))
                            $candidate['expected_salary'] = 0;    
                        $this->db->insert('candidate', $candidate);
                    }catch(Exception $e){
                        print_r("row[$line] = ");
                        print_r($row);
                        print_r($e->getMessage());
                    }
                }                    
            }
        }

    }
}
