<?php

require_once('jqgridmodel.php');
require_once('kf_editstatus.php');
require_once(str_replace("\\", "/", dirname(__FILE__)).'/prj_export.php');
require_once(str_replace("\\", "/", dirname(__FILE__)).'/prjs_export.php');
require_once(str_replace("\\", "/", dirname(__FILE__)).'/prj_diff_report.php');

//require_once(APPLICATION_PATH."/jqgrid/sys_req/vw_prj_srs_node/sys_req_vw_prj_srs_node.php");

if (!defined('PRJ_ONGOING')) define('PRJ_ONGOING', 1);
if (!defined('PRJ_COMPLETE')) define('PRJ_COMPLETE', 2);

class hengshan_prj extends jqGridModel{
    private $editStatus;
    protected $categoryIndex = -1;
    protected $excel_Category = array();
    protected $excelTitle = array();
    protected $inverseTitle = array();
    protected $itemsIndex = 0;
    public function init($controller, array $options = null){
        $userAdmin = new Application_Model_Useradmin($this);
        $blankItem = true;
        $active = true;
        $userList = $userAdmin->getUserList();
        $activeUserList = $userAdmin->getUserList(false, $active);
        $searchUserList = $userAdmin->getUserList($blankItem);
        
        $options['db'] = 'hengshan';
        $options['table'] = 'prj';
        $options['relations']['belongsto'] = array('platform', 'os', 'prj_status', 'edit_status');//, 'useradmin.users'=>array('foreignKey'=>'creater_id'));
//        $options['relations']['hasandbelongstomany'] = array('prj');
//        $options['relations']['hasone'] = array('srs_node'=>array('foreignKey'=>'published_id'));
        $options['columns'] = array(
            'id'=>array('editable'=>false),
            'name',
            'description',
            'platform_id',
            'os_id',
            'creater_id'=>array('label'=>'Creater', 'editable' =>false, 'hidden'=>true,
                'formatter'=>'select', 'formatoptions'=>array('value'=>$userList),
                'edittype'=>'select', 'editoptions'=>array('value'=>$activeUserList),
                'stype'=>'select', 'searchoptions'=>array('value'=>$searchUserList)),
            'first_manager_id'=>array('label'=>'First Manager', 'editable' =>false,
                'formatter'=>'select', 'formatoptions'=>array('value'=>$userList),
                'edittype'=>'select', 'editoptions'=>array('value'=>$activeUserList),
                'stype'=>'select', 'searchoptions'=>array('value'=>$searchUserList)),
            'second_manager_id'=>array('label'=>'Second Manager', 'editable' =>true, 'hidden'=>true,
                'formatter'=>'select', 'formatoptions'=>array('value'=>$userList),
                'edittype'=>'select', 'editoptions'=>array('value'=>$activeUserList),
                'stype'=>'select', 'searchoptions'=>array('value'=>$searchUserList)),
            'first_srs_drafter_id'=>array('label'=>'First SRS Drafter', 'editable' =>false,
                'formatter'=>'select', 'formatoptions'=>array('value'=>$userList),
                'edittype'=>'select', 'editoptions'=>array('value'=>$activeUserList),
                'stype'=>'select', 'searchoptions'=>array('value'=>$searchUserList)),
            'second_srs_drafter_id'=>array('label'=>'Second SRS Drafter', 'editable' =>true, 'hidden'=>true,
                'formatter'=>'select', 'formatoptions'=>array('value'=>$userList),
                'edittype'=>'select', 'editoptions'=>array('value'=>$activeUserList),
                'stype'=>'select', 'searchoptions'=>array('value'=>$searchUserList)),
            'first_review_manager_id'=>array('label'=>'First Review Manager',
                'formatter'=>'select', 'formatoptions'=>array('value'=>$userList),
                'edittype'=>'select', 'editoptions'=>array('value'=>$activeUserList),
                'stype'=>'select', 'searchoptions'=>array('value'=>$searchUserList)),
            'second_review_manager_id'=>array('label'=>'Second Review Manager', 'hidden'=>true,
                'formatter'=>'select', 'formatoptions'=>array('value'=>$userList),
                'edittype'=>'select', 'editoptions'=>array('value'=>$activeUserList),
                'stype'=>'select', 'searchoptions'=>array('value'=>$searchUserList)),
            'first_test_manager_id'=>array('label'=>'First Test Manager',
                'formatter'=>'select', 'formatoptions'=>array('value'=>$userList),
                'edittype'=>'select', 'editoptions'=>array('value'=>$activeUserList),
                'stype'=>'select', 'searchoptions'=>array('value'=>$searchUserList)),
            'second_test_manager_id'=>array('label'=>'Second Test Manager', 'hidden'=>true,
                'formatter'=>'select', 'formatoptions'=>array('value'=>$userList),
                'edittype'=>'select', 'editoptions'=>array('value'=>$activeUserList),
                'stype'=>'select', 'searchoptions'=>array('value'=>$searchUserList)),
            'first_dev_manager_id'=>array('label'=>'First Dev Manager',
                'formatter'=>'select', 'formatoptions'=>array('value'=>$userList),
                'edittype'=>'select', 'editoptions'=>array('value'=>$activeUserList),
                'stype'=>'select', 'searchoptions'=>array('value'=>$searchUserList)),
            'second_dev_manager_id'=>array('label'=>'Second Dev Manager', 'hidden'=>true,
                'formatter'=>'select', 'formatoptions'=>array('value'=>$userList),
                'edittype'=>'select', 'editoptions'=>array('value'=>$activeUserList),
                'stype'=>'select', 'searchoptions'=>array('value'=>$searchUserList)),
            'edit_status_id'=>array('editable'=>false),
            'prj_status_id'=>array('editable'=>false),
            'isactive'=>array('editable'=>false)
        );

        $options['gridOptions']['subGrid'] = true;
//        $options['construct'] = 'hengshan.prj.construct';
        $options['ver'] = '1.0';
        parent::init($controller, $options);
        $this->editStatus = new KF_EditStatus($this->db, array('item_table'=>'prj'));
    } 

    public function contextMenu(){
        $menu = array(
            'publish'=>array('Publish'),
            'complete'=>array('Complete'),
            'cloneit'=>array('Clone the project'),
            'importsrs'=>array('Import SRS'),
            'exportsrs'=>array('Export SRS'),
            'importsrscasemapfile'=>array('Import SRS-Case Mapping'),
            'exportsrscasemapfile'=>array('Download SRS-Case Mapping'),
            'testreport'=>array('Generate Test Report'),
        );
        $menu = array_merge($menu, parent::contextMenu());
        return $menu;
    }
    
    public function getButtons(){
        $buttons = array(
            'prj_diff'=>array('caption'=>'Diff',
                         'buttonimg'=>'',
                         'title'=>'Diff SRS Among Projects',
//                         'onClickButton'=>'hengshan_prj_buttonActions',
                        ),
        );
        return array_merge($buttons, parent::getButtons());
    }
    
    public function publish(){
        $params = $this->tool->parseParams('prj_publish');
        return $this->editStatus->publish($params['element']);
/*
//print_r($params);
        $db = $this->get('db');
        $table = $db.'.prj';
//print_r($table);
        $v = array('edit_status_id'=>5);
        $this->db->update($table, $v, "id=".$params['element']);
        return;
*/        
    }
    
    public function complete(){
        $params = $this->tool->parseParams('prj_complete');
        $db = $this->get('db');
        $table = $db.'.prj';
        $v = array('prj_status_id'=>PRJ_COMPLETE, 'finishdate'=>date('Y-m-d'));
        $this->db->update($table, $v, "id=".$params['element']);
        return;
    }
    
    public function cloneit(){
        $params = $this->tool->parseParams('prj_cloneit');
        $sql = "SELECT * FROM prj WHERE id=".$params['element'];
        $res = $this->db->query($sql);
        $row = $res->fetch();
        if ($this->controller->getRequest()->isPost()){
            $row['name'] = $params['name'];
            unset($row['id']);
            $row['created'] = date('Y-m-d H:i:s');
            $row['edit_status_id'] = 1;
            $row['prj_status_id'] = 1;
            $this->db->insert('prj', $row);
            $new_prj_Id = $this->db->lastInsertId('prj');
            // clone the prj-srs links
            $sql = "INSERT INTO prj_srs_node_info_history (srs_node_info_id, prj_id, link_status_id)".
                " SELECT srs_node_info_id, $new_prj_Id, 1 ".
                " FROM prj_srs_node_info_history".
                " WHERE link_status_id=1 AND prj_id=".$params['element'];
            $this->db->query($sql);
            $sql = "INSERT INTO prj_srs_node_info (srs_node_id, prj_srs_node_info_history_id, prj_id, isactive)".
                " SELECT A.srs_node_id, B.id, $new_prj_Id, 1 ".
                " FROM prj_srs_node_info_history B LEFT JOIN srs_node_info A ON A.id=B.srs_node_info_id ".
                " WHERE B.link_status_id=1 AND B.prj_id=$new_prj_Id";
            $this->db->query($sql);
/*
            // clone the prj_srs_case mappings
            $sql = "INSERT INTO prj_srs_node_info_testcase (prj_srs_node_info_id, testcase_id)".
                " select C.id as prj_srs_node_info_id, B.testcase_id ".
                " FROM prj_srs_node_info C Left join prj_srs_node_info A on A.id=C.srs_node_info_id and A.prj_id=".$params['element']." AND C.prj_id=$new_prj_Id".
                " left join prj_srs_node_info_testcase B on A.id=B.prj_srs_node_info_id".
                " WHERE Not IsNull(testcase_id)";
            $this->db->query($sql);
*/            
        }
        else{
            $this->renderView('cloneit.php', array('prj_name'=>$row['name'], 'prj_description'=>$row['description']));
        }
    
    }
    
    public function importsrs(){
        $params = $this->tool->parseParams('prj_importsrs');
        $sql = "SELECT * FROM prj WHERE id=".$params['element'];
        $res = $this->db->query($sql);
        $row = $res->fetch();
        if ($this->controller->getRequest()->isPost()){
        	if (isset($_FILES['importsrs'])){
        	    $ret = $this->analyzeSrsFile($_FILES['importsrs']['tmp_name']);
        	    $prj_srs = new sys_req_vw_prj_srs_node($this->controller, array());
        	    $prj_srs->insertSRS2($ret, $params['element']);
        	}
        }
    }
    
    public function exportsrs(){
        $params = $this->tool->parseParams('prj_exportsrs');
        $result = $this->db->query("Select * from prj WHERE id=".$params['element']);
        $row = $result->fetch();
		$sheetTitles = array("System Requirements");
		$reprot_params = array('title'=>'System Requirements For '.$row['name'],
			'element'=>$params['element'],
			'db'=>$this->db,
		);
		$export = new prj_export($sheetTitles, $reprot_params);
		$export->report(array(0));
		$fileName = $export->save("srs_for_".$params['element']);
		return json_encode(array('filename'=>$fileName));
    }
    
    public function export(){
        $params = $this->tool->parseParams();
//print_r($params);
        if (!empty($params['element'])){
    		$sheetTitles = array("History", "System Requirements");
    		$report_params = array('title'=>'System Requirements',
    			'element'=>json_decode($params['element']),
    			'db'=>$this->db,
    		);
    		$export = new prjs_export($sheetTitles, $report_params);
    		$export->report(array(0, 1));
    		$fileName = $export->save("srs_for_".implode('_', $report_params['element']));
    		return $fileName;
    		return json_encode(array('filename'=>$fileName));
        }
    }
    
    public function prj_diff(){
        $params = $this->tool->parseParams();
        $prjs = implode(',', $params['element']);
//print_r($prjs);        
		$sheetTitles = array("Project Diff");
		$report_params = array('title'=>'Project Diff',
			'element'=>$prjs,
			'db'=>$this->db,
		);
		$diff_export = new prj_diff_report($sheetTitles, $report_params);
		$diff_export->report(array(0));
		$fileName = $diff_export->save("prj_diff_$prjs");
		return json_encode(array('filename'=>$fileName));
    }

    public function importsrscasemapfile(){
    
    }
    
    public function exportsrscasemapfile(){
    
    }
    
    protected function _saveOne($db, $table, $pair){
        $user = new Application_Model_Useradmin($this->controller);
        $userInfo = $user->getUserInfo();
        $pair['creater_id'] = $userInfo->id;
        return parent::_saveOne($db, $table, $pair);
    }
    
    protected function analyzeSrsFile($fileName){
        $reader = new Spreadsheet_Excel_Reader();
        $reader->setUTFEncoder('iconv');
        $reader->setOutputEncoding('UTF-8');
        $reader->read($fileName);
        foreach($reader->sheets as $k=>$data){
        	$sheetTitle = strtolower($reader->boundsheets[$k]['name']);
        	if (in_array($sheetTitle, array('title & history', 'index', 'references', 'appendex')))
        		continue;
        		
            foreach($data['cells'] as $line=>$row){
            	if (empty($row))
            		continue;
            	
		    	if (!empty($row[1]) && $row[1] == 'Back to Index')
		    		continue;
    		
                $this->analyzeRow($line, $row);
			}
        }
//        print_r($this->excel_Category);
        return $this->excel_Category;
    }
    
    protected function analyzeRow($line, $row){
//print_r($row);    
		if (count($row) == 1 && !empty($row[$this->inverseTitle['Requirements Text / Data']])){ // Category
			$this->categoryIndex ++;
			$this->excel_Category[$this->categoryIndex] = array('code'=>'', 'content'=>$row[$this->inverseTitle['Requirements Text / Data']], 'items'=>array());
			$this->itemsIndex = 0;
		}
    	else if (isset($row[1]) && ((strtolower($row[1]) == 'component' && strtolower($row[2]) == 'identifier') || strtolower($row[1]) == 'identifier')){
			if (empty($this->excelTitle)){ 
    			$this->excelTitle = $row;
    			foreach($row as $key=>$v)
					$this->inverseTitle[$v] = $key;
//print_r($this->excelTitle);
//print_r($this->inverseTitle);					
			}			
		}
		else if (!empty($row[$this->inverseTitle['Requirements Text / Data']])){
			foreach($this->excelTitle as $key=>$v){
				$rv = '';
				if (empty($row[$key])){
					if($this->itemsIndex != 0){
//print_r("itemsIndex = $this->itemsIndex, key = $key, prev = ".$this->excel_Category[$this->categoryIndex]['items'][$this->itemsIndex - 1][$v]);
						$rv = $this->excel_Category[$this->categoryIndex]['items'][$this->itemsIndex - 1][$v];
					}
				}
				else
					$rv = $row[$key];
				$this->excel_Category[$this->categoryIndex]['items'][$this->itemsIndex][$v] = $rv;
			}
			$this->itemsIndex ++;
		}
	}
}
