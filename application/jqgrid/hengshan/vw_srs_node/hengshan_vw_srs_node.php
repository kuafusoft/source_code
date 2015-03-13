<?php

//require_once('common.php');
require_once('jqgridmodel.php');
require_once(str_replace("\\", "/", dirname(__FILE__)).'/srs_diff_report.php');

class hengshan_vw_srs_node extends jqGridModel{
    protected $categoryIndex = -1;
    protected $category = array();
    protected $excelTitle = array();
    protected $inverseTitle = array();
    protected $itemsIndex = 0;
    protected $isTagModel = false;
    protected $prjIds = array();
    protected $categoryIds = array();
    public function init($controller, array $options = null){
        $options['db'] = 'hengshan';
        $options['table'] = 'vw_srs_node';
        $options['relations']['belongsto'] = array('srs_category', 'prj'=>array('conditions'=>'prj.isactive=1 AND prj.prj_status_id=1' ));
            
		$options['columns'] = array(
            'id'=>array('editable'=>false),
            'project'=>array('editable'=>false),
            'category'=>array('editable'=>false),
            'srs_category_id'=>array('hidden'=>true, 'hidedlg'=>true),
            'code',
            'content',
            'link_status'=>array('editable'=>false),
            'prj_id'=>array('hidden'=>true, 'hidedlg'=>true),
            '*'=>array('hidden'=>true, 'editable'=>false)
        );

        $options['construct'] = 'vw_srs_node_construct';
        $options['ver'] = '1.0';
        $options['order'] = array('project'=>'asc', 'category'=>'asc', 'code'=>'asc');

        parent::init($controller, $options);
        
        $res = $this->db->query("SELECT * FROM prj");
        while($row = $res->fetch())
            $this->prjIds[$row['name']] = $row['id'];
    } 

    function contextMenu(){
        $menu = array(
            'information'=>array('Show Information'),
            'linkcase'=>array('Link Cases'),
//            'unlink'=>array('Unlink'),
            'linkproject'=>array('Link to Other Projects')
        );
        $menu = array_merge($menu, parent::contextMenu());
        return $menu;
    }
    
    public function linkproject(){
        $params = $this->tool->parseParams('srs_linkproject');
//print_r($params);
        $result = $this->db->query("SELECT * FROM vw_srs_node WHERE id={$params['element']}");
        $row = $result->fetch();
        $nodeId = $row['srs_node_id'];
        $nodeInfoId = $row['srs_node_info_id'];
//print_r($row);        
        if ($this->controller->getRequest()->isPost()){
            $result = $this->db->query("SELECT * FROM vw_srs_node WHERE srs_node_info_id=".$nodeInfoId);
            $existed = array();
            while($e = $result->fetch())
                $existed[] = $e['prj_id'];
            
            if (!empty($params['projects'])){
                $added = array_diff($params['projects'], $existed);
//print_r($added);
                foreach($added as $project){
                    $this->insertPrjLink($nodeId, $nodeInfoId, $project);
                    //$this->db->insert('prj_srs_node_info', array('prj_id'=>$project, 'srs_node_info_id'=>$params['element']));
                }
            }
            if (!empty($params['unlinked'])){
                $removed = array_intersect($existed, $params['unlinked']);
//print_r($removed);                
				if (!empty($removed)){
					foreach($removed as $prj){
						// Insert a record into history table
						$this->db->insert('prj_srs_node_info_history', array('srs_node_info_id'=>$nodeInfoId, 'prj_id'=>$prj, 'link_status_id'=>3));
						$history_id = $this->db->lastInsertId();
						// update the prj_srs_node_info table to the insertId
						$this->db->update('prj_srs_node_info', array('prj_srs_node_info_history_id'=>$history_id), "prj_id=$prj and srs_node_id=$nodeId");
					}
				}
/*
                if (!empty($removed))
                    $res = $this->db->query("UPDATE prj_srs_node_info_history history LEFT JOIN prj_srs_node_info link ON history.id=link.prj_srs_node_info_history_id ".
                        " SET history.link_status_id=3 WHERE link.srs_node_id=$nodeId AND link.prj_id in (".implode(',', $removed).")");
*/					
/*                    
                $row = $res->fetch();
                $historyId = $row['prj_srs_node_info_history_id'];
                $this->db->update('prj_srs_node_info_history', array('link_status_id'=>3), "id=$historyId");
                foreach($removed as $project){
                    $res = $this->db->query("SELECT * FROM vw_srs_node WHERE prj_id=:prj_id AND srs_node_id=:srs_node_id", array('prj_id'=>$project, "srs_node_id"=>$nodeId));
                    $row = $res->fetch();
                    $historyId = $row['prj_srs_node_info_history_id'];
                    $this->db->update('prj_srs_node_info_history', array('link_status_id'=>3), "id=$historyId");
                }
*/                
            }
        }
        else{
            $sql = "SELECT prj.id, prj.name, link.id as linked FROM prj LEFT JOIN vw_srs_node link ON link.prj_id=prj.id".
                "  AND link.srs_node_info_id=$nodeInfoId WHERE prj.isactive=1";
//print_r($sql);                
            $res = $this->db->query($sql);
            $rows = $res->fetchAll();
//print_r($rows);            
            $this->renderView('srs_linkproject.php', array('projects'=>$rows));
        }
    }
/*    
    public function unlink(){
        $params = $this->tool->parseParams('srs_unlink');
        $this->db->delete('prj_srs_node_info', "id=".$params['element']);
    }
*/    
    public function comment(){
        $params = $this->tool->parseParams();
        $commentTable = $this->get('table').'_comment';
        $indexField = 'prj_srs_node_info_id';
        if ($this->controller->getRequest()->isPost()){
            // insert the record
            $vp = array($indexField=>$params['element'], 'comment'=>$params['comment'], 'commentator_id'=>$this->currentUser);
            $this->db->insert($commentTable, $vp);
        }
        else{
            $userTable = $this->userTable;
            $result = $this->db->query("SELECT comment.*, user.nickname as commentator FROM $commentTable comment left join $userTable user ON user.id=comment.commentator_id WHERE $indexField=".$params['element']." ORDER BY created DESC");
//print_r($result);
            $params['comments'] = $result->fetchAll();
            $this->renderView('comment.php', $params);
        }
    }
    
    // review:
    // send an email to reviewer and
    // update the record status and
    // insert a record to review table
    public function askReview(){
        $params = $this->tool->parseParams('srs_review');
        if ($this->controller->getRequest()->isPost()){
//print_r($params);        
            $notice = " This is for ".$params['project'];
            $this->review->askReview($params['element'], null, $params['reviewer'], $notice);
        }
        else{
            $sql = "SELECT code, content, project FROM vw_srs_node_prj WHERE id=".$params['element'];
            $res = $this->db->query($sql);
            $row = $res->fetch();
            // get the reviewer list
            $userAdmin = new Application_Model_Useradmin($this);
            $row['reviewers'] = $userAdmin->getReviewerList('srs');
            $this->renderView('srs_askreview.php', $row);
        }
    }
    
    public function review(){
        $params = $this->tool->parseParams('srs_review');
//        print_r($params);
        if ($this->controller->getRequest()->isPost()){
print_r($params);        
            $this->review->review($params['element'], $this->currentUser, $params['srs_review_result'], $params['srs_review_comment'], $params['submit']);
        }
        else{
//print_r($params);
            $sql = "SELECT node.code, info.content, review.notice, review.comment FROM srs_node_info info ".
                " Left JOIN srs_node node ON info.srs_node_id=node.id ".
                " LEFT JOIN srs_node_info_review review ON review.srs_node_info_id=info.id ".
                " WHERE review.id=".$params['element'];
            $res = $this->db->query($sql);
            $row = $res->fetch();
            $row['result'] = $this->review->getResultList();
            $this->renderView('srs_review.php', $row);
        }
    }
    
//	05:47:35 05:47:35 05:47:45 05:47:45
    public function information(){
        $params = $this->tool->parseParams('srs_information');
        $view = new Zend_View();
        $view->setScriptPath(APPLICATION_PATH.'/jqgrid/hengshan/vw_srs_node');
//print_r($params);    
        $sql = "SELECT * FROM vw_srs_node WHERE id=".$params['element'];
        $res = $this->db->query($sql);
        $row = $res->fetch();
        $view->category = $row['category'];
        $view->code = $row['code'];
        $view->content = $row['content'];
        $sql = "SELECT * ".
            " FROM vw_srs_node_history".
            " WHERE srs_node_id=".$row['srs_node_id'].
            " ORDER BY history_updated DESC";
//print_r($sql);            
        $res = $this->db->query($sql);
        $rows = $res->fetchAll();
//print_r($rows);        
        $view->history = $rows;
        
        $userTable = $this->userTable;
        $commentTable = "prj_srs_node_info_comment";
        $indexField = "prj_srs_node_info_id";
        $result = $this->db->query("SELECT comment.*, concat(user.nickname, '(', user.username, ')') as commentator FROM $commentTable comment left join $userTable user ON user.id=comment.commentator_id WHERE $indexField=".$params['element']." ORDER BY created DESC");
//print_r($result);
        $view->comment = $result->fetchAll();
		/*        
        $result = $this->db->query("SELECT tc.testcaseid, tc.name, tc.module ".
            " FROM prj_srs_node_info_testcase link LEFT JOIN xiaotian.vw_testcase tc ON link.testcase_id=tc.id".
            " WHERE link.prj_srs_node_info_id=".$params['element']);
        $rows = $result->fetchAll();
        $view->testcase = $rows;
*/        
        echo $view->render('srs_information.php');
    }
    
    public function linkcase(){
        $params = $this->tool->parseParams('srs_linkCase');
//        print_r($params);
        if ($this->controller->getRequest()->isPost()){
//print_r($params);
            if (!empty($params['cases'])){
                $resul = $this->db->query("select prj_srs_node_info_id FROM vw_srs_node_prj WHERE id=".$params['element']);
                $row = $result->fetch();
                if (!empty($row['prj_srs_node_info_id'])){
                    $prj_info_id = $row['prj_srs_node_info_id'];
                    $result = $this->db->query("SELECT tc.testcase_id FROM prj_srs_node_info_testcase tc WHERE prj_srs_node_info_id=$prj_info_id");
                    $existedIds = array();
                    while($row = $result->fetch()){
                        $existedIds[] = $row['testcase_id'];
                    }
                    $newIds = array_diff($params['cases'], $existedIds);
                    foreach($newIds as $caseId){
                        $this->db->insert('prj_srs_node_info_testcase', array('prj_srs_node_info_id'=>$prj_info_id, 'testcase_id'=>$caseId));
                    }
                }
            }
        }
        else{
            $view = new Zend_View();
            $view->setScriptPath(APPLICATION_PATH.'/jqgrid/sys_req/vw_prj_srs_node');
            echo $view->render('srs_linkcase.php');
        }
//        return ERROR_OK;         
    }


    protected function _tag($db, $table, $tag, $pair){
        $historyIds = array();
        $res = $this->db->query("SELECT prj_srs_node_info_history_id FROM {$this->options['table']} WHERE id IN ({$pair['element_id']})");
        while($row = $res->fetch()){
            $historyIds[] = $row['prj_srs_node_info_history_id'];
        }
        $pair['element_id'] = implode(',', $historyIds);
        return parent::_tag($db, $table, $tag, $pair);
    }
    
    public function getButtons(){
        $buttons = array(
            'importsrs'=>array('caption'=>'Import',
                         'buttonimg'=>'',
                         'title'=>'Import SRS From Excel',
                         'onClickButton'=>'srs_buttonActions',
                        ),
            'diff'=>array('caption'=>'Diff',
                         'buttonimg'=>'',
                         'title'=>'Diff among tags',
                         'onClickButton'=>'srs_buttonActions',
                        ),
            'link'=>array('caption'=>'(un)Link',
                         'buttonimg'=>'',
                         'title'=>'Link to Project',
                         'onClickButton'=>'srs_buttonActions',
                        ),
                        
        );
        return array_merge($buttons, parent::getButtons());
    }
    
    public function importsrs(){
        $params = $this->tool->parseParams('srs_importsrs');
        if ($this->controller->getRequest()->isPost()){
//print_r("time = ".date('H:i:s')." AT LINE ".__LINE__);        
        	if (isset($_FILES['import_srs'])){
        	    $this->analyzeSrsFile($_FILES['import_srs']['tmp_name']);
//print_r("time = ".date('H:i:s')." AT LINE ".__LINE__);        
        	    $ret = $this->checkCategory();
//print_r($this->category);
//print_r($ret);        	    
//print_r("time = ".date('H:i:s')." AT LINE ".__LINE__);        

		        if(empty($ret['error'])){
	        	    $ret['success'] = $this->insertSRS($ret);
				}
        	}
        }
		return json_encode($ret);									
    }
    
    protected function analyzeSrsFile($fileName){
		/**  Identify the type of $inputFileName  **/
		$inputFileType = PHPExcel_IOFactory::identify($fileName);
		/**  Create a new Reader of the type that has been identified  **/
		$reader = PHPExcel_IOFactory::createReader($inputFileType);
		$reader->setReadDataOnly(true);
  		$objExcel = $reader->load($fileName);
		$ignore = array('title & history', 'index', 'references', 'appendix');
		foreach($objExcel->getWorksheetIterator() as $index=>$sheet){
			$title = strtolower($sheet->getTitle());
//		print_r($title);
			if (in_array($title, $ignore))continue;
			foreach ($sheet->getRowIterator() as $row) {
			  	$data = array();
				$cellIterator = $row->getCellIterator();
//			  	$cellIterator->setIterateOnlyExistingCells(false); // This loops all cells,
			  	foreach ($cellIterator as $n=>$cell) {
			  		$cellV = trim($cell->getValue());
			  		if (!empty($cellV))
			  			$data[$n] = $cellV;
			  	}
//print_r($data);
            	if (empty($data))
            		continue;
            	
		    	if (!empty($data[0]) && $data[0] == 'Back to Index')
		    		continue;

				$this->analyzeRow($data);
			}
		}
    }
    
	
    protected function analyzeRow($row){
//print_r($row);
		if (isset($row[0]) && ((strtolower($row[0]) == 'component' && strtolower($row[1]) == 'identifier') || strtolower($row[0]) == 'identifier')){
			foreach($row as $key=>$v){
				$this->excelTitle[$key] = $v;
				$this->inverseTitle[$v] = $key;
			}
		}
		else if (!empty($row[$this->inverseTitle['Requirements Text / Data']])){
			if (empty($row[$this->inverseTitle['Identifier']])){ // category
				$this->categoryIndex ++;
				$this->category[$this->categoryIndex] = array('identifier'=>'', 'Requirements Text / Data'=>$row[$this->inverseTitle['Requirements Text / Data']], 'items'=>array());
				$this->itemsIndex = 0;
			}
			else{
				foreach($this->excelTitle as $key=>$v){
					$rv = '';
					if (empty($row[$key])){
						if($this->itemsIndex != 0){
	//print_r("itemsIndex = $this->itemsIndex, key = $key, prev = ".$this->category[$this->categoryIndex]['items'][$this->itemsIndex - 1][$v]);
							$rv = $this->category[$this->categoryIndex]['items'][$this->itemsIndex - 1][$v];
						}
					}
					else
						$rv = $row[$key];
					$this->category[$this->categoryIndex]['items'][$this->itemsIndex][$v] = $rv;
				}
				$this->itemsIndex ++;
			}
		}
	}

	public function insertSRS($preCode){
		foreach($this->category as $key=>$v){
//print_r("time = ".date('H:i:s:u')." AT LINE ".__LINE__.", key = $key\n");        
			if (empty($v['items'])) // pass the null category
				continue;
			$categoryId = array();
			$category = array('content'=>$v['Requirements Text / Data']);
			if(count($v['precode']) > 1){ // create the parent category
				$category_code = implode(':', $v['precode']);
				$category['pid'] = $categoryId[$category_code] = $this->getCategoryId(array('code'=>$category_code, 'content'=>$v['Requirements Text / Data']));
			}
			foreach($v['precode'] as $s){
				$category['code'] = $s;
				$categoryId[$s] = $this->getCategoryId($category);
			}
//print_r("time = ".date('H:i:s:u')." AT LINE ".__LINE__.", key = $key\n");        
			foreach($v['items'] as $k=>$item){
				$item['srs_category_id'] = $categoryId[$item['uniq_code']];
				$item['srs_category_code'] = $category['code'];
				$this->insertSRSItem($item);
			}
//print_r("time = ".date('H:i:s:u')." AT LINE ".__LINE__.", key = $key\n");        
		}
//print_r("time = ".date('H:i:s')." AT LINE ".__LINE__);        
//print_r($this->categoryIds);
		foreach($this->categoryIds as $v){
		    if (!empty($v['current_code'])){
                $currentCode = $v['current_code'];
                $nextCode = $v['nextcode'];
                $step = $v['step'];
                if ($currentCode > $nextCode){
                    $nextCode = (int)(($currentCode + $row['step']) / $row['step']) * $row['step'];
                    $this->db->update('srs_category', array('nextcode'=>$nextCode), 'id='.$v['id']);
                }
            }
        }
//print_r("time = ".date('H:i:s')." AT LINE ".__LINE__);        
		return true;
	}
	
    private function insertSRSItem($item){
    	$nodeInfoId = $this->insertSRSNodeInfo($item);

        $ignore = array('component', 'identifier', 'requirements text / data');
        $notSupport = array('no', 'n/a', 'na');
        foreach($item as $key=>$v){
        	if (in_array(strtolower($key), $ignore))
				continue;
//print_r("$key===$v\n");
			if (in_array(strtolower($v), $notSupport))
				continue;
//print_r($item);
			$key = str_replace("\n", ' ', $key);

			if (!empty($this->prjIds[$key])){
				$prjId = $this->prjIds[$key];
                $this->insertPrjLink($nodeInfoId['node_id'], $nodeInfoId['node_info_id'], $prjId);
			}
		}
		return true;        
    }

	private function insertSRSNodeInfo($item){
        $nodeId = $id = 0;
        $result = $this->db->query('SELECT * FROM srs_node WHERE code = :code AND srs_category_id=:srs_category_id', array('code'=>$item['Identifier'], 'srs_category_id'=>$item['srs_category_id']));
        if ($node = $result->fetch()){
            $nodeId = $node['id'];
            $v = array('srs_node_id'=>$node['id'], 'content'=>$item['Requirements Text / Data']);
            $result = $this->db->query('SELECT * FROM srs_node_info WHERE srs_node_id=:srs_node_id AND content = :content', $v);
            if ($row = $result->fetch()){
                $id = $row['id'];
            }
            else{ // the code existed, but the content is not the same, then we should create new version
                $v['creater_id'] = $this->currentUser;
                $this->db->insert('srs_node_info', $v);
                $id = $this->db->lastInsertId();
            }
        }
        else{
            $this->db->insert('srs_node', array('srs_category_id'=>$item['srs_category_id'], 'code'=>$item['Identifier']));
            $nodeId = $this->db->lastInsertId();
            $this->db->insert('srs_node_info', array('srs_node_id'=>$nodeId, 'content'=>$item['Requirements Text / Data'], 'creater_id'=>$this->currentUser));
            $id = $this->db->lastInsertId();
        }
//print_r($id);
        $this->updateNextCodeForCategory($item);
		return array('node_id'=>$nodeId, 'node_info_id'=>$id);
	}
	
	private function insertPrjLink($node_id, $node_info_id, $prjId){
	    $linkId = 0;
	    $res = $this->db->query("SELECT link.id, link.prj_id, link.srs_node_id, link.prj_srs_node_info_history_id, history.srs_node_info_id, history.link_status_id FROM prj_srs_node_info link LEFT JOIN prj_srs_node_info_history history on link.prj_srs_node_info_history_id=history.id  WHERE link.prj_id=$prjId and link.srs_node_id=$node_id and history.link_status_id=1");
	    if ($linkInfo = $res->fetch()){
//print_r($linkInfo);
	        $linkId = $linkInfo['id'];
	        if ($linkInfo['srs_node_info_id'] != $node_info_id)
    	       $this->db->update('prj_srs_node_info_history', array('link_status_id'=>2), "id={$linkInfo['prj_srs_node_info_history_id']}");
    	    else
    	       return $linkInfo['id'];
        }
		$historyLinkId = 0;
//print_r("node_info_id=$node_info_id, prj = $prjId");
        $this->db->insert('prj_srs_node_info_history', array('link_status_id'=>1, "prj_id"=>$prjId, "srs_node_info_id"=>$node_info_id));
        $historyLinkId = $this->db->lastInsertId();
		if(!empty($historyLinkId)){
            if (!empty($linkId)){
                $this->db->update("prj_srs_node_info", array('prj_srs_node_info_history_id'=>$historyLinkId), "id=$linkId");
            }
            else{
                $this->db->insert("prj_srs_node_info", array('prj_id'=>$prjId, 'srs_node_id'=>$node_id, 'prj_srs_node_info_history_id'=>$historyLinkId));
                $linkId = $this->db->lastInsertId();
            }
        }
		return $linkId;
	}
	
    public function updateNextCodeForCategory($item){
//print_r($item);
        if (preg_match('/(.*?)_(\d*)$/', $item['Identifier'], $matches)){
            $category_code = $matches[1];
            $curCode = (int)$matches[2];
//print_r($this->categoryIds);            
            if (!isset($item['srs_category_code']))
                $item['srs_category_code'] = $category_code;
            $this->categoryIds[$item['srs_category_code']]['current_code'] = $curCode;
        }
/*        
            if ($curCode >= $row['nextcode']){
                $nextCode = (int)(($curCode + $row['step']) / $row['step']) * $row['step'];
                $this->db->update('srs_category', array('nextcode'=>$nextCode), 'id='.$row['srs_category_id']);
            }
        }
*/        
    }
    
    protected function checkCategory(){
		$error = array();
		$warning = array();
		$categoryId = array();
		$code = array();
		$preCode = array();
//print_r($this->category);		
		foreach($this->category as $key=>&$v){
			$preCode_k = array();
			if (empty($v['items'])) // if there's no items, pass
				continue;
			// check the items, get the category code
			// if there're more than 1 code under the category, then we should create a parent category to control the all categories
			foreach($v['items'] as $k=>$item){
				$code[] = $item['Identifier'];
				if (preg_match('/^(.*?)_\d+$/', $item['Identifier'], $matches)){
					$preCode_k[] = $matches[1];
					$v['items'][$k]['uniq_code'] = $matches[1];
				}
				else{
//print_r($v);
//print_r($item);				
					$error[] = "Item code is wrong:".$item['Identifier'].", content = ".$item['Requirements Text / Data'].", category is ".$v['Requirements Text / Data'];									
				}
			}
	
			$preCode_k = array_unique($preCode_k);
			$v['precode'] = $preCode_k;
//print_r($preCode_k);			
			$codeCount = count($preCode_k);
			foreach($preCode_k as $k1=>$s1){
				if($codeCount >1){			
				// if the difference of code is small (Just only one letter difference), we should set it in warning array
					foreach($preCode_k as $k2=>$s2){
						if($k1 >= $k2)
							continue;
						$similar = similar_text($s1, $s2, $percent);
						if($percent >= 80 && abs(strlen($s1) - strlen($s2)) < 2){
//print_r($v);						
							$warning[] = "Might typo: code1 = $s1, code2 = $s2, category is ".$v['Requirements Text / Data']; 
						}
					}
				}
			}
		}
		$dup_code = $this->tool->array_dup($code);
		foreach($dup_code as $c){
			$error[] = "Duplicate Code:$c";
		}
		$ret = array();
		if (!empty($error))
			$ret['error'] = $error;
		if (!empty($warning))
			$ret['warning'] = $warning;
		return $ret;
	}

    private function getCategoryId($category){
        // check if the category existes
//print_r($category);
//print_r("time = ".date('H:i:s:u')." AT LINE ".__LINE__.", category = \n");
//print_r($category);        
        $categoryId = 0;
        $v = array('code'=>$category['code'], 'content'=>$category['content']);
        if (!isset($this->categoryIds[$category['code']]['id'])){
            $result = $this->db->query('SELECT * FROM srs_category WHERE code = :code', /* AND content = :content',*/ array('code'=>$category['code']));
            if ($row = $result->fetch()){ // existed
                $categoryId = $row['id'];
                $nextcode = $row['nextcode'];
                $step = $row['step'];
            }
            else{   // not existed
            	if (!empty($category['pid']))
            		$v['pid'] = $category['pid'];
                $this->db->insert('srs_category', $v);
                $categoryId = $this->db->lastInsertId();                
                $nextcode = 5;
                $step = 5;
            }
            $this->categoryIds[$category['code']] = array('id'=>$categoryId, 'nextcode'=>$nextcode, 'step'=>$step);
        }
//print_r("time = ".date('H:i:s:u')." AT LINE ".__LINE__."\n");        
        return $this->categoryIds[$category['code']]['id'];
    }
    
    protected function _saveOne($db, $table, $pair){
		$pair['Identifier'] = $pair['code'];
		$pair['Requirements Text / Data'] = $pair['content'];
		$nodeInfoId = $this->insertSRSNodeInfo($pair);
		$affectedID = $this->insertPrjLink($nodeInfoId['node_id'], $nodeInfoId['node_info_id'], $pair['prj_id']);
    	return $affectedID;
	}

    public function getNextCode(){
        $params = $this->tool->parseParams('srs_getNextCode');
//print_r($params);
        $sql = "SELECT code, nextcode FROM srs_category WHERE id=".$params['element'];
        $res = $this->db->query($sql);
        $row = $res->fetch();
        $nextCode = sprintf("%s_%08d", $row['code'], $row['nextcode']);
        return $nextCode;
    }
    
    /*
    if tag search, then wen must switch to prj_srs_node_info_history as the main table, otherwise, use the prj_srs_node_info.
    
    */
    public function getSql($params, $limited = false){
    	// check if it's tag search
    	$this->isTagModel = false;
		if (!empty($params['searchConditions'])){
	    	$this->isTagModel = $this->isTagSearch($params['searchConditions']);
	    }
//print_r("tagSearch = $tagSearch");	    
    	if ($this->isTagModel)
			$this->options['table'] = 'vw_srs_node_history vw_srs_node';
		else
			$this->options['table'] = 'vw_srs_node';
		return parent::getSql($params, $limited);
	}

	private function isTagSearch($params){
//print_r($params);
		$ret = false;
		if (is_array($params)){
			foreach($params as $key=>$v){
				if (is_array($v)){
					$ret = $this->isTagSearch($v);
					if ($ret)
						break;
				}
				else{
					if ($key == 'field' && $v == '__interTag'){
						$ret = true;
						break;
					}
				}
			}
		}
		return $ret;
	}

    public function diff(){
        $params = $this->tool->parseParams();

        if ($this->controller->getRequest()->isPost()){
			$sheetTitles = array("System Requirements");
			$reprot_params = array('title'=>'Test Result Report',
				'tag'=>$params['tag'],
				'db'=>$this->db,
			);
			$diff_export = new srs_diff_report($sheetTitles, $reprot_params);
			$diff_export->report(array(0));
			$fileName = $diff_export->save("srs_diff_".$params['tag']);
			return json_encode(array('filename'=>$fileName));
        }
        else{
        	$result = $this->db->query("SELECT * FROM tag WHERE `table`='hengshan.vw_srs_node' and (creater_id={$this->currentUser} OR public=1)");
            $this->renderView('srs_diff.php', array('tag'=>$result));
        }
    }
    
    public function link(){
        $params = $this->tool->parseParams();

        if ($this->controller->getRequest()->isPost()){
			$sheetTitles = array("System Requirements");
			$reprot_params = array('title'=>'Test Result Report',
				'tag'=>$params['tag'],
				'db'=>$this->db,
			);
			$diff_export = new srs_diff_report($sheetTitles, $reprot_params);
			$diff_export->report(array(0));
			$fileName = $diff_export->save("srs_diff_".$params['tag']);
			return json_encode(array('filename'=>$fileName));
        }
        else{
        	$result = $this->db->query("SELECT * FROM tag WHERE `table`='hengshan.vw_srs_node' and (creater_id={$this->currentUser} OR public=1)");
            $this->renderView('srs_batch_link.php', array('tag'=>$result));
        }
    }
    
}

