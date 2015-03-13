<?php
require_once('jqgridmodel.php');
require_once('kf_editstatus.php');
require_once('ganon.php');
require_once(str_replace("\\", "/", dirname(__FILE__)).'/cycle_report.php');
require_once(str_replace("\\", "/", dirname(__FILE__)).'/cycle_contrast_export.php');
require_once(str_replace("\\", "/", dirname(__FILE__)).'/cycle_oobt_export.php');
require_once(str_replace("\\", "/", dirname(__FILE__)).'/cycle_combine_export.php');
require_once(str_replace("\\", "/", dirname(__FILE__)).'/logfileprocess.php');

define('RELEASE_LINE', 0);
define('COMPILER_CONFIG_LINE', 1);
define('BOARD_MACHINE_LINE', 2);
define('MODULE_LINE', 3);

class xt_zzvw_cycle extends jqGridModel{
    public function init($controller, array $options = null){
		$week = $this->generateWeekList();
		$cart_data = new stdClass;
		$cart_data->filters =  '{"groupOp":"AND","rules":[{"field":"status_id","op":"eq","data":1}]}';
        $options['db'] = 'xt';
        $options['table'] = 'zzvw_cycle';
		$options['linktype'] = 'infoLink';
        $options['columns'] = array(
			'id',
			'os_id'=>array('query'=>true, 'hidden'=>true),
			'board_type_id'=>array('label'=>'Board', 'query'=>true, 'hidden'=>true,),
			'chip_id'=>array('query'=>true, 'hidden'=>true),
			'prj_id'=>array('query'=>true, 'width'=>160),
			'compiler_id'=>array('query'=>true, 'queryoptions'=>array('advanced'=>true), 'width'=>100),
			'rel_id'=>array('query'=>true, 'editrules'=>array('required'=>true), 'queryoptions'=>array('advanced'=>true), 'width'=>150),
			'cycle_type_id'=>array('label'=>'Cycle Type', 'query'=>true, 'hidden'=>true, 'queryoptions'=>array('advanced'=>true)),
			'build_target_id'=>array('label'=>'Build Target', 'query'=>true, 'queryoptions'=>array('advanced'=>true), 'width'=>120),
			'testcase_type_id'=>array('label'=>'Case Type', 'query'=>true, 'editable'=>true, 'view'=>true, 'hidden'=>true, 'queryoptions'=>array('advanced'=>true)),
			'group_id'=>array('label'=>'Group Type', 'editable'=>true, 'view'=>true, 'hidden'=>true),
			//required在kf_tool中添加，不在这里加的原因是，会造成gridinlineedit出错 
			'week'=>array('label'=>'Week', 'editrules'=>array('required'=>true), 'edittype'=>'select','editable'=>true, 'type'=>'select', 'view'=>true, 'excluded'=>true, 'hidden'=>true, 'hidedlg'=>true, 'editoptions'=>array('value'=>$week)),
			'myname'=>array('label'=>'MyName', 'editrules'=>array('required'=>true), 'edittype'=>'text', 'editable'=>true, 'type'=>'text','view'=>true, 'excluded'=>true, 'hidden'=>true, 'hidedlg'=>true),
			'name'=>array('query'=>true, 'width'=>290),
			'description'=>array('width'=>200),
			'cycle_category_id'=>array('label'=>'Category', 'query'=>true, 'hidden'=>true, 'queryoptions'=>array('advanced'=>true)),
			'cycle_status_id'=>array('label'=>'Cycle Status', 'query'=>true, 'hidden'=>true, 'editable'=>false, 'queryoptions'=>array('value'=>'1', 'advanced'=>true)),
			'start_date'=>array('hidden'=>true),
			'end_date'=>array('hidden'=>true),
			'tester_ids'=>array('query'=>true, 'hidden'=>true, 'queryoptions'=>array('querytype'=>'select', 'advanced'=>true), 'cart_db'=>'useradmin', 'cart_table'=>'users', 'cart_data'=>json_encode($cart_data)),
			'test_env_id'=>array('hidden'=>true),
			'creater_id'=>array('query'=>true, 'width'=>80),
			'pass_cases'=>array('label'=>'Passed', 'editable'=>false, 'view'=>false, 'excluded'=>true, 'width'=>85),
			'fail_cases'=>array('label'=>'Failed', 'editable'=>false, 'view'=>false, 'excluded'=>true, 'width'=>40),
			'total_cases'=>array('label'=>'Total', 'editable'=>false, 'view'=>false, 'excluded'=>true, 'width'=>50),
			'*'=>array('hidden'=>true, 'view'=>false)
		);

        $options['gridOptions']['subGrid'] = true;
		$options['gridOptions']['label'] = 'cycle';
		$options['gridOptions']['inlineEdit'] = false;
        $options['ver'] = '1.0';
        parent::init($controller, $options);
    } 
	
	public function getButtons(){
		$buttons = parent::getButtons();
		$buttons['freeze'] = array('caption'=>'Freeze', 'title'=>'Freeze the selected cycles');
		$buttons['contrast_export'] = array('caption'=>'Contrast Report', 'title'=>'Contrast with different cycles');
		$buttons['combine_export'] = array('caption'=>'Combine Report(Same Prj)', 'title'=>'Combine with different cycles(Same Prj)');
		$buttons['clone'] = array('caption'=>'Clone', 'title'=>'Clone cycles');
		$buttons['del_cycle'] = array('caption'=>'Del', 'title'=>'Del cycles');
		$isAdmin = $this->userAdmin->isAdmin($this->currentUser);
		if(!$isAdmin){
			unset($buttons['freeze']);
			unset($buttons['activate']);
			unset($buttons['inactivate']);
		}
		return $buttons;
	}
	
	public function freeze(){
		$params = $this->tool->parseParams();
		$this->db->update('cycle', array('cycle_status_id'=>CYCLE_STATUS_FROZEN), "id in (".implode(',', json_decode($params['element'])).")");
		if(!isset($params['flag']))
			$this->buttons();
//		$this->information_refresh();
	}
	
	public function unfreeze(){
		$params = $this->tool->parseParams();
//		print_r($params);
		$params['element'] = json_decode($params['element']);
		// update the cycle_status
		$this->db->update('cycle', array('cycle_status_id'=>CYCLE_STATUS_ONGOING), "id in (".implode(',', $params['element']).")");
        if(!isset($params['flag']))
			$this->buttons();
//		$this->information_refresh();
		
	}
	
	public function select_item(){
		$params = $this->tool->parseParams();
//print_r($params);		
		if ($params['condition'] == 'add2cycle'){
			$sql = "SELECT id, name FROM cycle where creater_id=".$this->currentUser." AND cycle_status_id=".CYCLE_STATUS_ONGOING." AND isactive=".ISACTIVE_ACTIVE;
//			print_r($sql);
			$res = $this->db->query($sql);
			$cycles = $res->fetchAll();
//print_r($cycles);
			$this->renderView('select_item.phtml', array('type'=>'Cycle', 'items'=>$cycles), '/jqgrid');
		}
	}

	public function script(){
		$params = $this->tool->parseParams();
//		print_r($params);
		$ret = '';
        $rename = 'cycle_'.$params['element'][0];
		$res = $this->db->query("SELECT name FROM cycle WHERE id=".$params['element'][0]);
		$cycle_info = $res->fetch();
        if ($cycle_info){
            $rename = str_replace('/', '_', $cycle_info['name']);
            $rename .= '_'.(($params['script_type'] == 1) ? 'Auto' : 'AutoMan');
            $realFileName = SCRIPT_ROOT.'/'.$rename.'_'.rand();
            $download = array("rename"=>$rename, "filename"=>$realFileName, "remove"=>1);
        	$sql = "SELECT * FROM zzvw_cycle_detail".
        		" WHERE cycle_id =".$params['element'][0]." AND auto_level_id=".$params['script_type'];
        	$result = $this->db->query($sql);
            $str = '';
        	while ($row = $result->fetch()){
				if(!empty($row["command"]))
					$str .= $row["testcase_id"] . " " . $row["command"] . "\n";
        	}
            if (!empty($str)){
                $handle = @fopen($realFileName, 'wb');
                if ($handle){
                    if (fwrite($handle, $str)){
                        fclose($handle);
                        $ret = json_encode($download);
                    }
                }
            }
        }
        return $ret;
	}
	
	protected function _saveOne($db, $table, $pair){
		if (!empty($pair['tester_ids']) && is_array($pair['tester_ids']))
			$pair['tester_ids'] = implode(',', $pair['tester_ids']);
		return parent::_saveOne($db, $table, $pair);
	}
	
	protected function _save($db, $table, $pair){
		return parent::_save($db, 'cycle', $pair);
	}
	
	public function cloneit(){
		$valuePairs = $this->tool->parseParams();
		$vs = $this->tool->extractData($valuePairs, 'cycle');
		$orig_id = $vs['id'];
		unset($vs['id']);
		$vs['cycle_status_id'] = CYCLE_STATUS_ONGOING;
		$vs['isactive'] = ISACTIVE_ACTIVE;	
		$vs['creater_id'] = $this->currentUser;
		$vs['cloned_id'] = $orig_id;	
	
		// save
		$affectedID = $this->_save('xt', 'cycle', array($vs));
		// save the cycle_detail info
		$searchCondition = $this->getSearchCondition($valuePairs, $orig_id );
		$p_sql = "SELECT prj_id FROM cycle WHERE id = $orig_id";
		$res = $this->db->query($p_sql);
		$cycle = $res->fetch();
		if($vs['prj_id'] != $cycle['prj_id']){
			$c_sql = "SELECT GROUP_CONCAT(DISTINCT testcase_id) AS caselist FROM cycle_detail WHERE cycle_id =".$orig_id;
			$res = $this->db->query($c_sql);
			$caselist = $res->fetch();
			if(!empty($caselist['caselist'])){
				$t_sql = "SELECT GROUP_CONCAT(DISTINCT testcase_id) AS testcase_id FROM prj_testcase_ver".
					" WHERE prj_id=".$vs['prj_id'].
					" AND testcase_id IN (".$caselist['caselist'].")".
					" AND edit_status_id IN (".EDIT_STATUS_PUBLISHED.", ".EDIT_STATUS_GOLDEN.")";
				$res = $this->db->query($t_sql);
				$testcase = $res->fetch();
				if(!empty($testcase['testcase_id'])){
					$sql = "INSERT INTO cycle_detail (cycle_id, testcase_ver_id, testcase_id, codec_stream_id, test_env_id, build_result_id)".
							" SELECT $affectedID, testcase_ver_id, testcase_id, codec_stream_id, test_env_id, 1".
							" FROM cycle_detail".
							" WHERE cycle_id = $orig_id".
							" AND testcase_id in (".$testcase['testcase_id'].")";
				}
			}
		}
		else{
			$sql = "INSERT INTO cycle_detail (cycle_id, testcase_ver_id, testcase_id, codec_stream_id, test_env_id, build_result_id)".
					" SELECT $affectedID, testcase_ver_id, testcase_id, codec_stream_id, test_env_id, 1 FROM cycle_detail WHERE cycle_id=".$orig_id;
		}
		if(!empty($sql)){
			foreach($searchCondition as $k=>$sc){
				if(!empty($sc))
					$sql.= " AND ".$sc;
				if(stripos($sc, "codec_stream_priority") == 0)//位置是0
					unset($searchCondition[$k]);
			}
		}
		$this->db->query($sql);
		$this->tool->log('save', $valuePairs);
		$this->updateTestCaseVer($affectedID, $vs['prj_id']);		
		$errorCode['code'] = ERROR_OK;
		$errorCode['msg'] = $affectedID;
		return $errorCode;
	}
	
	public function cloneall(){
		$valuePairs = $this->tool->parseParams();
// print_r($valuePairs);
		if(!empty($valuePairs['myname'])){
			$valuePairs['element'] = json_decode($valuePairs['element']);
			$res = $this->db->query("SELECT * FROM cycle WHERE id in (".implode(',', $valuePairs['element']).")");
			while($row = $res->fetch()){
				$old_id = $row['id'];
				unset($row['id']);
				if(!empty($valuePairs['rel_id']))
					$row['rel_id'] = $valuePairs['rel_id'];
				$row['start_date'] = date('Y-m-d');
				if(!empty($valuePairs['start_date']))
					$row['start_date'] = $valuePairs['start_date'];
				$row['end_date'] = 0;
				if(!empty($valuePairs['end_date']))
					$row['end_date'] = $valuePairs['end_date'];
				if(!empty($valuePairs['myname']))
					$row['name'] = $row['name'].'_'.$valuePairs['myname'];
				else
					$row['name'] = $row['name'].'_clone';
				$row['cycle_status_id'] = CYCLE_STATUS_ONGOING;
				$row['isactive'] = ISACTIVE_ACTIVE;	
				$row['creater_id'] = $this->currentUser;
				$row['cloned_id'] = $old_id;
				//check unique
				$affectedID = $this->_save('xt', 'cycle', array($row));
				$sql = "INSERT INTO cycle_detail (cycle_id, testcase_ver_id, testcase_id, codec_stream_id, test_env_id, tester_id, build_result_id)".
					" SELECT $affectedID, testcase_ver_id, testcase_id, codec_stream_id, test_env_id, tester_id, 1 FROM cycle_detail WHERE cycle_id=".$old_id;
				$this->db->query($sql);
				$this->updateTestCaseVer($affectedID);
				$this->tool->log('save', $valuePairs);
				// $errorCode['code'] = ERROR_OK;
				// $errorCode['msg'] = $affectedID;
				// return $errorCode;
			}
		}
	}
	
	private function getSearchCondition($valuePairs, $cycle_id){
		//result search
		if(is_array($valuePairs['result_type'])){
			$valuePairs['result_type'] = implode(',', $valuePairs['result_type']);
			if($valuePairs['result_type'] == 'all')
				$searchCondition[0] = "";
			else
				$searchCondition1[0] = "result_type_id in ({$valuePairs['result_type']})";
		}
		//case priority search
		if(is_array($valuePairs['testcase_priority'])){
			$valuePairs['testcase_priority'] = implode(',', $valuePairs['testcase_priority']);
			if($valuePairs['testcase_priority'] == 'all')
				$searchCondition[1] = "";
			else
				$searchCondition[1] = "testcase_priority_id in ({$valuePairs['testcase_priority']})";
		}
		//stream priority search
		if(isset($valuePairs['stream_priority']) && is_array($valuePairs['stream_priority'])){
			$valuePairs['stream_priority'] = implode(',', $valuePairs['stream_priority']);
			if($valuePairs['stream_priority'] == 'all')
				$searchCondition[2] = "";
			else
				$searchCondition[2] = "codec_stream_priority in ({$valuePairs['stream_priority']})";
		}

		if(isset($valuePairs['tag']) && $valuePairs['tag']){
			$res = $this->db->query("SELECT `element_id` FROM `tag` WHERE `id`=".$valuePairs['tag']);
			$stream = $res ->fetch();
			$res = $this->db->query("SELECT GROUP_CONCAT(id) as id FROM cycle_detail WHERE cycle_id=".$cycle_id." AND codec_stream_id in (".$stream['element_id'].")");
			$element = $res->fetch();
			$searchCondition[3] = "id in (".$element['id'].")";
		}
		return $searchCondition;
	}
	
	private function updateTestCaseVer($cycleid, $prj_id){
		if(empty($prj_id)){
			//从cycle里面取出prj_id来
			$sql = "SELECT prj_id FROM cycle WHERE id=".$cycleid;
			$res = $this->db->query($sql);
			$info = $res->fetch();
			$prj_id = $info['prj_id'];
		}
		$sql = "SELECT DISTINCT testcase_id as testcase_id FROM cycle_detail WHERE cycle_id=".$cycleid;
		$res = $this->db->query($sql);
		while($row = $res->fetch()){
			$ver_sql = "SELECT testcase_ver_id FROM prj_testcase_ver".
				" WHERE testcase_id=".$row['testcase_id'].
				" AND prj_id=".$prj_id.
				" AND edit_status_id".
				" IN (".EDIT_STATUS_PUBLISHED.", ".EDIT_STATUS_GOLDEN.")";
			$ver_res = $this->db->query($ver_sql);
			$ver = $ver_res->fetch();
			if($ver){
				$data = array('testcase_ver_id'=>$ver['testcase_ver_id']);
				$this->db->update('cycle_detail', $data, "cycle_id=".$cycleid." AND testcase_id=".$row['testcase_id']);
			}
		}
	}
	

	
   protected function getMoreInfoForRow($row){
		$res0 = $this->db->query("SELECT group_concat(distinct codec_stream_id) as codec_stream_id FROM cycle_detail WHERE cycle_id={$row['id']}");
		$data = $res0->fetch();
		if($data['codec_stream_id'] == '0'){
// print_r($data['codec_stream_id']."\n");		
			$res = $this->db->query("SELECT COUNT(*) as cases FROM cycle_detail WHERE cycle_id={$row['id']}");
			$info = $res->fetch();
			$row['total_cases'] = $info['cases'];
			
			$res = $this->db->query("SELECT COUNT(*) as cases FROM cycle_detail WHERE cycle_id={$row['id']} and result_type_id=".RESULT_TYPE_PASS);
			$info = $res->fetch();
			$row['pass_cases'] = $info['cases'];
			$passrate = 0;
			$color = 'red';
			if ($row['total_cases'] > 0){
				$passrate = number_format($row['pass_cases']/$row['total_cases'] * 100, 2);
				if ($passrate >= 80)
					$color = 'blue';
				else if ($passrate >= 60)
					$color = 'gray';
			}
			
			$row['pass_cases'] = sprintf("<span style='color:$color'>%-4d[%5.2f%%]</span>", $row['pass_cases'], $passrate);	
			$res = $this->db->query("SELECT COUNT(*) as cases FROM cycle_detail WHERE cycle_id={$row['id']} and result_type_id=".RESULT_TYPE_FAIL);
			$info = $res->fetch();
			$row['fail_cases'] = $info['cases'];
		}
		else{
// print_r($data['codec_stream_id']."\n");
			$streamList = explode(",", $data['codec_stream_id']);
// print_r($row['id']);
// print_r($streamList);
			$row['total_cases'] = 0;
			$row['pass_cases'] = 0;
			$row['fail_cases'] = 0;
			foreach($streamList as $v){
				if($v == 0){
					$res = $this->db->query("SELECT COUNT(*) as cases FROM cycle_detail WHERE codec_stream_id = 0 AND cycle_id={$row['id']}");
					$info = $res->fetch();
					$row['total_cases'] += $info['cases'];
					
					$res = $this->db->query("SELECT COUNT(*) as cases FROM cycle_detail WHERE codec_stream_id = 0 AND cycle_id={$row['id']} and result_type_id=".RESULT_TYPE_PASS);
					$info = $res->fetch();
					$row['pass_cases'] += $info['cases'];

					$res = $this->db->query("SELECT COUNT(*) as cases FROM cycle_detail WHERE codec_stream_id = 0 AND cycle_id={$row['id']} and result_type_id=".RESULT_TYPE_FAIL);
					$info = $res->fetch();
					$row['fail_cases'] += $info['cases'];
				}
				else{
// print_r($v."\n");
					$res1 = $this->db->query("SELECT group_concat(distinct result_type_id) as result_type_id FROM cycle_detail WHERE codec_stream_id in ( ".$v." ) AND cycle_id={$row['id']}");
					if($info1 = $res1->fetch()){

						$row['total_cases'] += 1;
						if($info1['result_type_id'] == '1')
							$row['pass_cases'] += 1;	
						else {
							$info1['result_type_id'] = ", ".$info1['result_type_id'];
							if(strpos($info1['result_type_id'], '2'))
								$row['fail_cases'] += 1;
						}
					}
				}
			}
// if($row['id'] == 131){
// print_r($row['total_cases']."\n");
// print_r($row['pass_cases']."\n");
// print_r($row['fail_cases']."\n");
// print_r("yyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyy");
// }	
			$passrate = 0;
			$color = 'red';
			if ($row['total_cases'] > 0){
				$passrate = number_format($row['pass_cases']/$row['total_cases'] * 100, 2);
				if ($passrate >= 80)
					$color = 'blue';
				else if ($passrate >= 60)
					$color = 'gray';
			}
			$row['pass_cases'] = sprintf("<span style='color:$color'>%-4d[%5.2f%%]</span>", $row['pass_cases'], $passrate);
		}
		return $row;
	}
	
	protected function getViewEditButtons($params){
		// check if current user is the owner or admin
		if(!empty($params['id'])){
			$res = $this->db->query("SELECT * FROM cycle WHERE id=".$params['id']);
			$cycle = $res->fetch();
		}
		
		$style = 'position:relative;float:right';
		$display = $style;
		$hide = $style.';display:none';	
		$btns = parent::getViewEditButtons($params);
		$btns['unfreeze'] = array('label'=>'unFreeze', 'style'=>'dposition:relative;float:left');
		if (isset($cycle['cycle_status_id']) && $cycle['cycle_status_id'] == CYCLE_STATUS_ONGOING){
			$btns['addcase'] = array('label'=>'Add Cases', 'style'=>'dposition:relative;float:left');
			$btns['freeze'] = array('label'=>'Freeze', 'style'=>'dposition:relative;float:left');
			//$btns['unfreeze'] = array('label'=>'unFreeze', 'style'=>'dposition:relative;float:left');
			$isTester = false;
			$isOwner = $this->isOwner('cycle', $params);//传的参数不是很合理
			$isAdmin = $this->userAdmin->isAdmin($this->currentUser);
			if(isset($cycle['tester_ids'])){
				$testers = explode(',', $cycle['tester_ids']);
				foreach($testers as $tester){
					if($this->currentUser == $tester)
						$isTester = true;
				}
			}
			if($isOwner || $isAdmin){
				$btns['uploadfile'] = array('label'=>'Upload', 'style'=>'dposition:relative;float:left', 'onclick'=>'XT.grid_query_upload("xt", "zzvw_cycle", '.$params['id'].')');
			}
			if(!$isOwner && !$isAdmin){// && !$isTester){
				unset($btns['addcase']);
				unset($btns['edit']);
				unset($btns['freeze']);
				unset($btns['unfreeze']);
			}
		}

		if ($params['id']){
			$btns['script'] = array('type'=>'select', 'style'=>'dposition:relative;float:left', 
				'options'=>array(0=>'==Generate Script==', 1=>'Auto Script', 'Auto/Manual Script', 'Skywalker Script'));
		}		
		if (isset($btns['freeze'])){
			if(isset($cycle['cycle_status_id']) && $cycle['cycle_status_id'] == CYCLE_STATUS_ONGOING)
				$btns['freeze']['style'] .= ';display:inline';
			else
				$btns['freeze']['style'] .= ';display:none';
		}
			
		if (isset($btns['unfreeze'])){
			if(isset($cycle['cycle_status_id']) && $cycle['cycle_status_id'] == CYCLE_STATUS_FROZEN){
				$btns['unfreeze']['style'] .= ';display:inline';
				unset($btns['edit']);
			}
			else
				$btns['unfreeze']['style'] .= ';display:none';
		}
		if (isset($cycle['cycle_status_id']) && $cycle['cycle_status_id'] == CYCLE_STATUS_FROZEN){
			unset($btns['save']);
		}
		return $btns;
	}
	
	protected function getInformationViewParams($params){
		$db = $this->get('db');
		$table = $this->get('table');
		$view_params = parent::getInformationViewParams($params);	
		if (!empty($params['element'])){
			$view_params['tabs']['cycle_detail'] = array('dir'=>'xt/zzvw_cycle', 'label'=>'Cycle Cases', 'disabled'=>!$params['element']);//, 'detail'=>$res->fetchAll());
			$view_params['tabs']['reports'] = array('label'=>'Reports', 'disabled'=>!$params['element']);
		}
		return $view_params;
	}
	
	public function list2(){
		$ret = $this->getList();

		foreach($ret['rows'] as &$row){
			$res = $this->db->query("SELECT COUNT(*) as tot FROM cycle_detail WHERE cycle_id={$row['id']}");
			$tot = $res->fetch();
			$row['total_cases'] = $tot['tot'];
			$res = $this->db->query("SELECT result_type, COUNT(*) as tot FROM zzvw_cycle_detail WHERE cycle_id={$row['id']} group by result_type_id");
			while($tot = $res->fetch()){
				$row['total_'.strtolower($tot['result_type'])] = $tot['tot'];
			}
			if (!isset($row['total_fail']))$row['total_fail'] = 0;
			if (!isset($row['total_pass']))$row['total_pass'] = 0;
//print_r($row);			
		}
		
		$this->renderView("list_cycle.phtml", array('rows'=>$ret['rows']));
	}
	
	public function rel_prj_list(){
		$sql = "SELECT rel.id as rel_id, rel.name, rel_category.name as category, rel.release_time ".
			" FROM rel left join rel_category on rel.rel_category_id=rel_category.id".
			" WHERE 1";
		$res = $this->db->query($sql);
		$rel = $res->fetchAll();
		$res = $this->db->query("SELECT prj.id as prj_id, prj.name, chip.name as chip, os.ab as compiler FROM prj left join chip on prj.chip_id=chip.id left join os on prj.os_id=os.id");
		$prj = $res->fetchAll();
		$this->renderView("rel_prj.phtml", array('rel'=>$rel, 'prj'=>$prj));
	}
	
	public function rel_prj_report(){
		$params = $this->tool->parseParams();
//print_r($params);		
		$rel_id = $params['rel'];
		$prj_id = $params['prj'];
		$ret = array();
		$rels = array();
		$prjs = array();
		$sql = "SELECT rel, prj, count(*) as total_cases from zzvw_cycle_detail ".
			" WHERE rel_id in ($rel_id) AND prj_id in ($prj_id) ".
			" GROUP BY prj_id, rel_id".
			" ORDER BY release_time DESC";
		$res = $this->db->query($sql);
		while($row = $res->fetch()){
			if (empty($ret[$row['rel']])){
				$ret[$row['rel']] = $row;
				$ret[$row['rel']][$row['prj']] = $row['total_cases'];
			}
			else{
				$ret[$row['rel']][$row['prj']] = $row['total_cases'];
			}
			$rels[$row['rel']] = $row['rel'];
			$prjs[$row['prj']] = $row['prj'];
		}
		$sql = "SELECT rel, prj, count(*) as passed from zzvw_cycle_detail ".
			" WHERE rel_id in ($rel_id) AND prj_id in ($prj_id) AND result_type_id=1 ".
			" GROUP BY prj_id, rel_id".
			" ORDER BY release_time DESC";
		$res = $this->db->query($sql);
		while($row = $res->fetch()){
			$ret[$row['rel']][$row['prj']] = $row['passed'] * 100 / $ret[$row['rel']][$row['prj']] ."%  ({$row['passed']} / {$ret[$row['rel']][$row['prj']]})";
		}
		$this->renderView("rel_prj_report.phtml", array('rows'=>$ret, 'rel'=>$rels, 'prj'=>$prjs));
	}
	
	public function get_detail(){
		$params = $this->tool->parseParams();
		$res = $this->db->query("SELECT * FROM zzvw_cycle_detail WHERE cycle_id={$params['element']}");
		$rows = $res->fetchAll();
		$this->renderView("list_detail.phtml", array('rows'=>$rows));
	}
	
	public function report_detail(){
		$ret = array();
		$cycle = array();
		$params = $this->tool->parseParams();
		$cycle_ids = $params['element'];
		$res = $this->db->query("SELECT * FROM zzvw_cycle_detail where cycle_id in ($cycle_ids) order by code ASC");
		while($row = $res->fetch()){
//print_r($row);			
			if (empty($ret[$row['code'].'_'.$row['testcase_ver_id']]))
				$ret[$row['code'].'_'.$row['testcase_ver_id']] = array('code'=>$row['code'], 'module'=>$row['testcase_module'], 'testpoint'=>$row['testcase_testpoint'], $row['cycle']=>$row['result_type']);
			else
				$ret[$row['code'].'_'.$row['testcase_ver_id']][$row['cycle']] = $row['result_type'];
			$cycle[$row['cycle']] = $row['cycle'];
		}
		$this->renderView('report_detail.phtml', array('rows'=>$ret, 'cycle'=>$cycle));
	}
	
	public function diff_detail(){
		$ret = array();
		$cases = array();
		$params = $this->tool->parseParams();
		$cycle_ids = $params['element'];
		
		$res = $this->db->query("SELECT * FROM zzvw_cycle_detail where cycle_id in ($cycle_ids) order by code ASC");
		while($row = $res->fetch()){
			if (empty($ret[$row['cycle']]))
				$ret[$row['cycle']] = array('cycle'=>$row['cycle'], $row['code'].$row['testcase_ver_id']=>$row['result_type'], 'code'=>$row['code']);
			else
				$ret[$row['cycle']][$row['code'].$row['testcase_ver_id']] = $row['result_type'];
			$cases[$row['code'].$row['testcase_ver_id']] = $row['code'];
		}
		$this->renderView('diff_detail.phtml', array('rows'=>$ret, 'cases'=>$cases));
	}
	
	public function sync(){
		$taskFile = "\\\\10.192.225.199\\vnvserver\\task.txt";
		$handle  = fopen($taskFile, "rb");
		if (!$handle)
			die("Error to open the file $taskFile");
		$lines = array();
		$line = 0;
		while(!feof($handle)){
			$lines[$line ++] = trim(fgets($handle));
		}
//	print_r($lines);
		fclose($handle);
		// 0: time-->create release
		$rel = array('name'=>$lines[RELEASE_LINE], 'rel_category_id'=>1, 'release_time'=>$lines[RELEASE_LINE]);
		$rel_id = $this->tool->rowExist($rel, 'rel', 'xt');
		if (!$rel_id)
			$rel_id = $this->tool->insert($rel, 'rel', 'xt');
		
		// 1: compiler-config line
		$boards = array();
		list($compiler, $config) = explode(' ', $lines[COMPILER_CONFIG_LINE]);
		// check if the compiler exist
		$os_id = $this->tool->rowExist(array('ab'=>$compiler), 'os', 'xt');
		if (!$os_id)
			$os_id = $this->tool->insert(array('name'=>'Compiler:'.$compiler, 'ab'=>$compiler), 'os', 'xt');

		// 3: module line
		$modules = explode(' ', $lines[MODULE_LINE]);

		// 2: board-machine line
		$prj_ids = array();
		$board_machines = explode(' ', $lines[BOARD_MACHINE_LINE]);
		foreach($board_machines as $v){
			list($machine, $board) = explode(':', $v);
			$boards[$board] = array('machine'=>$machine, 'chip'=>substr($board, 3));
			// check if the chip exist
			$chip_id = $this->tool->rowExist(array('ab'=>$boards[$board]['chip']), 'chip', 'xt');
			if(!$chip_id){
				$chip_id = $this->tool->insert(array('name'=>$boards[$board]['chip'], 'ab'=>$boards[$board]['chip']), 'chip', 'xt');
			}
			// check if the project exist
			$prj = array('board_id'=>BOARD_TYPE_TOWER, 'chip_id'=>$chip_id, 'os_id'=>$os_id, 'name'=>'twr-'.$boards[$board]['chip'].'-'.$compiler);
			$prj_id = $this->tool->rowExist($prj, 'prj', 'xt');
			if (!$prj_id){
				$prj_id = $this->tool->insert($prj, 'prj', 'xt');
			}
			// create the cycle
			$cycle = array('prj_id'=>$prj_id, 'cycle_type_id'=>CYCLE_TYPE_FUNCTION, 'rel_id'=>$rel_id, 'start_date'=>date('Y-m-d'), 'name'=>$board.'-'.$compiler.'-'.$config.'-'.$lines[RELEASE_LINE]);
			$cycle_id = $this->tool->rowExist($cycle, 'cycle', 'xt');
			if (!$cycle_id)
				$cycle_id = $this->tool->insert($cycle, 'cycle', 'xt');
			
			$detail = $this->parse_result($cycle_id, $prj_id, $machine, $board, $compiler, $config, $modules);
//	print_r($detail);		
		}		
		print_r("rel_id = $rel_id\n");
	}
	
	protected function parse_result($cycle_id, $prj_id, $machine, $board, $compiler, $config, $modules){
		$cycle = array();
		foreach($modules as $m){
			$root = '\\\\'.$machine.'\\artifacts\\ar_int_'.$m.'_'.$board.'_'.$compiler.'_'.$config;
			$files = scandir($root);
			$dir = $root.'\\'.$files[count($files) - 1].'\\target\\test-results';
			$files = scandir($dir);
			foreach($files as $file){
				if (preg_match("/^($m)_(\w+)_$board.xml$/i", $file, $matches)){
					// check if the module exist
					$module_id = $this->tool->rowExist(array('name'=>$m), 'testcase_module', 'xt');
					if (!$module_id)
						$module_id = $this->tool->insert(array('name'=>$m), 'testcase_module', 'xt');
					// check if the testcase_testpoint exist
					$testpoint = array('testcase_module_id'=>$module_id, 'name'=>$matches[2]);
					$testpoint_id = $this->tool->rowExist($testpoint, 'testcase_testpoint', 'xt');
					if (!$testpoint_id)
						$testpoint_id = $this->tool->insert($testpoint, 'testcase_testpoint', 'xt');
					
					$xml = file_get_dom($dir.'\\'.$file);
					if (!empty($xml)){
						$time = '';
						$props = $xml('testsuite > properties > property');
						foreach($props as $prop){
				//print_r($prop->html());			
							$name = $prop->getAttribute('name');
				//print_r($name);			
							if ($name == 'timestamp')
								$time = $prop->getAttribute('value');
						}
						$testcases = $xml('testcase');
						foreach($testcases as $tc){
							$name = $tc->getAttribute('name');
							// check if the testcase exist
							$testcase = array('code'=>$name, 'summary'=>$name, 'testcase_type_id'=>TESTCASE_TYPE_MQX, 'testcase_source_id'=>TESTCASE_SOURCE_FSL, 
								'testcase_testpoint_id'=>$testpoint_id, 'testcase_category_id'=>TESTCASE_CATEGORY_FUNCTION);
							
							$tc_id = $this->tool->rowExist($testcase, 'testcase', 'xt', $data);
							if (!$tc_id)
								$tc_id = $this->tool->insert($testcase, 'testcase', 'xt');
							$ver = array('testcase_id'=>$tc_id);
							$testcase_ver_id = $this->tool->rowExist($ver, 'testcase_ver', 'xt');
							if (!$testcase_ver_id){
								$ver['command'] = '';
								$ver['objective'] = '';
								$ver['expected_result'] = '';
								$ver['update_comment'] = '';
								$ver['review_comment'] = '';
								$ver['created'] = date('Y-m-d H:i:s');
								$testcase_ver_id = $this->tool->insert($ver, 'testcase_ver', 'xt');
							}
							$prj_ver = array('prj_id'=>$prj_id, 'testcase_id'=>$tc_id);
							$prj_ver_id = $this->tool->rowExist($prj_ver, 'prj_testcase_ver', 'xt', $row);
							if(!$prj_ver_id){
								$prj_ver['testcase_ver_id'] = $testcase_ver_id;
								$prj_ver_id = $this->tool->insert($prj_ver, 'prj_testcase_ver', 'xt');
							}
							else{
								$testcase_ver_id = $row['testcase_ver_id'];
							}
							
							$failure = $tc('failure', 0);
							if (!empty($failure)){
								$result = $failure->getAttribute('type');
								$comment = $result."\n".$failure->getInnertext();
							}
							else{
								$result = $comment = $tc->getInnertext();
							}
							if(empty($result))
								$result_type_id = RESULT_TYPE_PASS;
							else 
								$result_type_id = RESULT_TYPE_FAIL;
							$detail = compact('testcase_ver_id', 'cycle_id', 'result_type_id', 'comment');//, 'time');
							$detail_id = $this->tool->rowExist($detail, 'cycle_detail', 'xt');
							if (!$detail_id)
								$this->saveDetail($detail);
						}
					}
				}
			}
		}			
		return $cycle;
	}

	private function saveDetail($detail){
		$detail_id = $this->_saveOne('xt', 'cycle_detail', $detail);
		$res = $this->db->query("SELECT * FROM zzvw_cycle_detail WHERE id=$detail_id");
		$row = $res->fetch();
		$last_result = array('testcase_id'=>$row['testcase_id'], 'prj_id'=>$row['prj_id'], 'rel_id'=>$row['rel_id']);
		$last_id = $this->tool->rowExist($last_result, 'testcase_last_result', 'xt');
		if (!empty($detail['result_type_id']) && ($detail['result_type_id'] == RESULT_TYPE_PASS || $detail['result_type_id'] == RESULT_TYPE_FAIL)){
			$last_result['cycle_detail_id'] = $detail_id;
			$last_result['id'] = $last_id;
			$this->_saveOne('xt', 'testcase_last_result', $last_result);
		}
		else{
			if ($last_id){
				// 需要将前一个结果置入
				$res = $this->db->query("SELECT id FROM zzvw_cycle_detail WHERE prj_id={$row['prj_id']} AND rel_id={$row['rel_id']} AND (result_type_id=".RESULT_TYPE_PASS." OR result_type_id=".RESULT_TYPE_FAIL.")");
				if ($row = $res->fetch())
					$this->db->update('xt.testcase_last_result', array('cycle_detail_id'=>$row['id']), "id=$last_id");
				else
					$this->db->del('xt.testcase_last_result', "id=$last_id");
			}
		}
	}
			
	protected function _report($params){
		$sheetTitles = array("Cover","General Result");
		$report_params = array('title'=>'Test Report For Cycle',
			'element'=>$params['element'],
			'db'=>$this->db,
		);
		$report = new cycle_report($sheetTitles, $report_params);
		$report->report(array(1,0));
		$fileName = $report->save("cycle_report_for_".$report_params['element'].'.xlsx', REPORT_ROOT.'/'.$params['table_name']);
		return $fileName;
	}
	public function contrast_export(){//可能多个cycle结合的report
		$params = $this->tool->parseParams();
		$sheetTitles = array("Cover","General Result");
		$report_params = array('title'=>'Test Report For Cycle',
			'element'=>$params['element'],
			'results'=>array('Blank','Fail','Pass', 'Total'),
			'db'=>$this->db,
		);
		$params['element'] = implode("_", json_decode($params['element']));
		$cycle_export = new cycle_contrast_export($sheetTitles, $report_params);
		$cycle_export->report(array(1,0));
		$fileName = $cycle_export->save("cycle_report_for_".$params['element'].'.xlsx', EXPORT_ROOT);
		return $fileName;
	}
	public function oobt_export(){//可能多个cycle结合的report
		$params = $this->tool->parseParams();
		$report_params = array('title'=>'Test Report For Cycle',
			'element'=>$params['element'],
			'db'=>$this->db,
		);
		$len = count(json_decode($params['element']));
		for($i=0;$i<$len;$i++)
			$index[$i] = $i;
		$params['element'] = implode("_", json_decode($params['element']));
		foreach($index as $k=>$v){
			$sheetTitles[$k] = 'General Result '.$v;
		}
		$cycle_export = new cycle_oobt_export($sheetTitles, $report_params);
		$cycle_export->report($index);
		$fileName = $cycle_export->save("cycle_report_for_".$params['element'].'.xlsx', EXPORT_ROOT);
		return $fileName;
	}
	public function exports1_1(){//可能多个cycle结合的report
		$sheetTitles = array("General Result");
		$params = $this->tool->parseParams();
		$report_params = array('title'=>'Test Report For Cycle',
			'element'=>$params['element'],
			'results'=>array('Blank','Fail','Pass', 'Total'),
			'db'=>$this->db,
		);
		$params['element'] = implode("_", json_decode($params['element']));
		$cycle_export = new cycle_export1($sheetTitles, $report_params);
		$cycle_export->report(array(0));
		$fileName = $cycle_export->save("cycle_report_for_".$params['element'].'.xlsx', EXPORT_ROOT);
		return $fileName;
	}
	public function exports1_2(){//可能多个cycle结合的report
		$params = $this->tool->parseParams();
		$i = 0;
		$element =  json_decode($params['element']);
		$report_params = array('title'=>'Test Report For Cycle',
			'element'=>$params['element'],
			'results'=>array('Blank','Fail','Pass', 'Total'),
			'db'=>$this->db,
		);
		$res = $this->db->query("SELECT DISTINCT prj_id FROM cycle WHERE id IN (".implode(",", json_decode($params['element'])).")");
		
		while($info = $res->fetch()){
			$index[$i] = $i;
			$prj[] = $info['prj_id'];//得到所有的prj
			$i++;
		}
		$res = $this->db->query("SELECT name FROM prj WHERE id IN (".implode(",", $prj).")");
		while($info = $res->fetch())
			$sheetTitles = $info['name'];//得到所有的prj
		$res = $this->db->query("SELECT id, prj_id FROM cycle WHERE prj_id IN (".implode(",", $prj).")");
		while($info = $res->fetch()){
			foreach($element as $k=>$v){
				if($v == $info['id']){
					if(!isset($cyclelist[$info['prj_id']]))
						$cyclelist[$info['prj_id']] = $info['id'];
					else
						$cyclelist[$info['prj_id']] = $cyclelist[$info['prj_id']].', '.$info['id'];
				}
			}
		}
		$cycle_export = new cycle_export1($sheetTitles, $report_params);
		$index[0] = 0;
		$cycle_export->report($index);
		$fileName = $cycle_export->save("cycle_report_for_".$params['element'].'.xlsx', EXPORT_ROOT);
		return $fileName;
	}
	public function combine_export(){//可能多个cycle结合的report
		$params = $this->tool->parseParams();
		$sheetTitles = array("Cover","General Result");
		$report_params = array('title'=>'Test Report For Cycle',
			'element'=>$params['element'],
			'results'=>array('Fail','Pass'),
			'db'=>$this->db,
		);
		$params['element'] = implode("_", json_decode($params['element']));
		$cycle_export = new cycle_combine_export($sheetTitles, $report_params);
		$cycle_export->report(array(1,0));
		$fileName = $cycle_export->save("cycle_report_for_".$params['element'].'.xlsx', EXPORT_ROOT);
		return $fileName;
	}
	
	public function buttons(){
		$params = $this->tool->parseParams();
		$params['element'] = json_decode($params['element']);
		$view_params['id'] = isset($params['element']) ? $params['element'] : (isset($params['id']) ? $params['id'] : 0);
		if (is_array($view_params['id']))
			$view_params['id'] = implode(',', $view_params['id']);
		$view_buttons = $this->getViewEditButtons($view_params);
		$view_params = array('btn'=>$view_buttons, 'editable'=>true);
		$this->renderView('button_edit.phtml', $view_params);
	}
	
	public function addtional(){
		$params = $this->tool->parseParams();
		if ($this->controller->getRequest()->isPost()){
                                                
		}
		else{
			//clone只针对一个cycle，所以id只有一个，不用搞成字符串的格式
			if (!empty($params['element'])){			
				$sql ="SELECT id, name FROM result_type";
				$res = $this->db->query($sql);
				$res_tp = array();
				$res_tp['all'] = 'All';
				while($info = $res->fetch()){
					$res_tp[$info['id']] = $info['name'];
				}
				$sql ="SELECT id, name FROM testcase_priority";
				$res = $this->db->query($sql);
				$tc_pr = array();
				$tc_pr['all'] = 'All';
				while($info = $res->fetch()){
					$tc_pr[$info['id']] = $info['name'];
				}	
				$cols = array(
					array('id'=>'result_type', 'name'=>'result_type', 'label'=>'TestCase Result', 'editable'=>true, 'DATA_TYPE'=>'int', 'type'=>'checkbox', 'editoptions'=>array('value'=>$res_tp), 'defval'=>'all'),
					array('id'=>'testcase_priority', 'name'=>'testcase_priority', 'label'=>'TestCase Priority', 'editable'=>true, 'DATA_TYPE'=>'int', 'type'=>'checkbox', 'editoptions'=>array('value'=>$tc_pr), 'defval'=>'all'),
					array('id'=>'codec_stream_clone', 'name'=>'codec_stream_clone', 'label'=>'Codec Stream Clone', 'editable'=>true, 'DATA_TYPE'=>'int', 'type'=>'checkbox', 'editoptions'=>array('value'=>array(1=>'YES'))),
				);
				$this->renderView('addtional_clone.phtml', array('cols'=>$cols));
			}	
		}
	}
	public function codecStreamClone(){
		$params = $this->tool->parseParams();
		if ($this->controller->getRequest()->isPost()){
                                                
		}
		else{
			//clone只针对一个cycle，所以id只有一个，不用搞成字符串的格式
			if (!empty($params['element'])){			
				$sql ="SELECT id, name FROM testcase_priority";
				$res = $this->db->query($sql);
				$tc_pr = array();
				$tc_pr['all'] = 'All';
				while($info = $res->fetch()){
					$tc_pr[$info['id']] = $info['name'];
				}
				
				$sql ="SELECT `id`, `name` FROM `tag` WHERE `table`='xt.codec_stream'";
				$res = $this->db->query($sql);
				$tag = array();
				$tag[0] = "";
				while($info = $res->fetch()){
					$tag[$info['id']] = $info['name'];
				}
				$cols = array(
					array('name'=>'stream_priority', 'label'=>'Stream Priority', 'editable'=>true, 'DATA_TYPE'=>'int', 'type'=>'checkbox', 'editoptions'=>array('value'=>$tc_pr), 'defval'=>'all'),
					array('name'=>'tag', 'label'=>'Tag', 'editable'=>true, 'DATA_TYPE'=>'int', 'type'=>'select', 'editoptions'=>array('value'=>$tag), 'defval'=>'all')
				);
				$this->renderView('addtional_clone.phtml', array('cols'=>$cols, 'fors'=>'codec'));
			}	
		}
	}
	public function askcycle(){
		//if ($this->controller->getRequest()->isPost()){
            //clone只针对一个cycle，所以id只有一个，不用搞成字符串的格式
				$cycles[0] = '';
				$sql ="SELECT id, name FROM cycle";
				$res = $this->db->query($sql);
				while($cycle = $res->fetch()){
					$cycles[$cycle['id']] = $cycle['name'];
				}	
				$cols = array(
					array('name'=>'cycle_id', 'label'=>'Cycle', 'editable'=>true, 'DATA_TYPE'=>'int', 'type'=>'select', 'editoptions'=>array('value'=>$cycles)),
				);
				$this->renderView('addtional_clone.phtml', array('cols'=>$cols));                                    
		//}
		//else{
		//}
	}
	
	private function generateWeekList($preWeek = 8, $postWeek = 10){
		$currentYear = (int)date('y');
		$currentWorkWeek = (int)date('W');
		$refData = array();
		for($i = $currentWorkWeek - $preWeek; $i < $currentWorkWeek + $postWeek; $i ++){
			$j = $i;
			$year = $currentYear;
			if ($i > 52){
				$j = $i - 52;
				$year = $currentYear + 1;
			}
			else if ($i <= 0){
				$j = $i + 52;
				$year = $currentYear - 1;
			}
			if ($j < 10)
				$refData[$i] = $year.'WK0'.$j;
			else
				$refData[$i] = $year.'WK'.$j;
		}
		return $refData;
	}
	public function getTesters(){
		$params = $this->tool->parseParams();
		$where = "1";
		if($params['value'])
			$where = "id=".$params['value'];
		$sql = "SELECT tester_ids FROM cycle WHERE ".$where;
		$res = $this->db->query($sql);
		$tester = $res->fetch();
		$tester_ids = explode(",", $tester['tester_ids']);
		foreach($tester_ids as $key=>$val){
			if(empty($val))
				unset($tester_ids[$key]);
		}
		$tester['tester_ids'] = implode(",", $tester_ids);
		$users = $this->userAdmin->getUsers($tester['tester_ids']);
		$testers = array();
		if (!empty($users)){
			$i = 0;
			foreach($users as $user){
				$testers[$i]['id'] = $user['id'];
				$testers[$i++]['name'] = $user['nickname'];
			}
		}
		$testers[$i]['id'] = 100;
		$testers[$i++]['name'] = 'Blank';
		return $testers;
	}
	
	public function getrel(){
		$params = $this->tool->parseParams();
		$where = "1";
		if($params['value'])
			$where = "os_id=".$params['value'];
		$sql = "SELECT id, name FROM rel WHERE ".$where;
		$res = $this->db->query($sql);
		$rel = $res->fetchAll();
		return $rel;
	}
	
	public function getUpload(){
		$params = $this->tool->parseParams();
		if ($this->controller->getRequest()->isPost()){	
		}
		else{
			$upload_type = array('', 'case', 'cycle');
			$cols[0] = array('id'=>'upload_type', 'name'=>'upload_type', 'label'=>'Pls Select', 'editable'=>true, 'DATA_TYPE'=>'int', 'type'=>'select', 'editoptions'=>array('value'=>$upload_type), 'editrules'=>array('required'=>true));
			$os[0] = '';
			$sql ="SELECT id, name FROM os WHERE isactive=1";
			$res = $this->db->query($sql);
			while($info = $res->fetch()){
				$os[$info['id']] = $info['name'];
			}
			$cols[1] = array('id'=>'os_id', 'name'=>'os_id', 'label'=>'OS', 'editable'=>true, 'DATA_TYPE'=>'int', 'type'=>'select', 'editoptions'=>array('value'=>$os), 'editrules'=>array('required'=>true));
			if(isset($params['element'])){
				$sql ="SELECT prj.os_id as os_id FROM cycle LEFT JOIN prj ON cycle.prj_id=prj.id WHERE cycle.id=".$params['element'];
				$res = $this->db->query($sql);
				$info = $res->fetch();
				$cols[1]['defval'] = $info['os_id'];
			}
			$format = array('', 'txt', 'excel', 'yml', 'html', 'zip');
			$cols[] = array('id'=>'file_format', 'name'=>'file_format', 'label'=>'Foramt', 'editable'=>true, 'DATA_TYPE'=>'text', 'type'=>'select', 'editoptions'=>array('value'=>$format), 'editrules'=>array('required'=>true));	
			$cols[] = array('id'=>'cycle_logfile', 'name'=>'cycle_logfile', 'label'=>'Report File', 'editable'=>true, 'DATA_TYPE'=>'text', 'type'=>'file', 'editrules'=>array('required'=>true));	
			$this->renderView('upload.phtml', array('cols'=>$cols, 'f'=>'post')); 
		}
	}
	
	public function uploadlogfile(){
		$params = $this->tool->parseParams();
		if ($this->controller->getRequest()->isPost()){	
			$logFile = $params['cellName'];
			$strLogFilePath = LOG_ROOT;
print_r($strLogFilePath);
			if(isset($params['element'])){
				//save a logfile
				if(!empty($params['element'])){
					$res = $this->db->query("SELECT id, name FROM cycle WHERE id=".$params['element']);
					$info = $res->fetch();
					$path = LOG_ROOT."/".$info['name'].'_'.$params['element']."/".str_replace(' ', '', basename($_FILES[$logFile]['name']));
					if (!file_exists($path)){
						$path = LOG_ROOT.'/tmp/'.str_replace(' ', '', basename($_FILES[$logFile]['name']));
					}
				}
			}
			else
				$path = LOG_ROOT.'/tmp/'.str_replace(' ', '', basename($_FILES[$logFile]['name']));	
			if (isset($_FILES[$logFile])){
				if (!file_exists($strLogFilePath)){
					if (PHP_OS == "WINNT"){
						mkdir($strLogFilePath, 0700, true);
					}
					else{
						system('/bin/mkdir -p '.escapeshellarg($strLogFilePath) . ' -m 777');
					}
				}
				$ret = move_uploaded_file($_FILES[$logFile]["tmp_name"], $path);
				if($ret == 1){
					print_r("sucess!");
					if (PHP_OS == "Linux")
						system('chmod 777 '. $path);
				}
			}
		}
		else{
		}
	}
	
	public function process_logfile(){
		$params = $this->tool->parseParams();
		if ($this->controller->getRequest()->isPost()){	
			$logFile = '';
			if(!empty($params['cellName']))
				$logFile = $params['cellName'];
			$path = LOG_ROOT.'/tmp';
			if(isset($params['element'])){
				if(!empty($params['element'])){
// print_r("update");
// print_r("\n");
					$res = $this->db->query("SELECT id, name FROM cycle WHERE id=".$params['element']);
					$info = $res->fetch();
					$path = LOG_ROOT."/".$info['name'].'_'.$params['element'];	
					if (!file_exists($path)){
						$path = LOG_ROOT.'/tmp/';
					}
				}
			}
			else{
print_r("generate");
print_r("\n");
				$params['element'] = '';
			}
			if(!empty($params[$logFile])){
				$path = $path.'/'.str_replace(' ', '', basename($params[$logFile]));
print_r($path);
print_r("\n");
				if (file_exists($path)){
//process
print_r("process");
print_r("\n");
					$logfileprocess = new logfileprocess($this->get('db'));
					$logfileprocess->uploadLogForCycle($path, $params);
				}
			}
			else
				print_r('no logfile params input');
		}
		else{
		}
	}
	
	public function getCloneAll(){
		$params = $this->tool->parseParams();
		if ($this->controller->getRequest()->isPost()){	
		}
		else{
			$rel[0] = '';
			$res = $this->db->query("SELECT id, name FROM rel");
			while($row = $res->fetch()){
				$rel[$row['id']] = $row['name'];
			}
			$cols = array(
				array('id'=>'myname', 'name'=>'myname', 'label'=>'My name', 'editable'=>true, 'DATA_TYPE'=>'text', 'type'=>'text', 'editrules'=>array('required'=>true)),
				array('id'=>'rel_id', 'name'=>'rel_id', 'label'=>'Rel', 'editable'=>true, 'DATA_TYPE'=>'text', 'type'=>'select', 'editoptions'=>array('value'=>$rel), 'editrules'=>array('required'=>true)),
				array('id'=>'start_date', 'name'=>'start_date', 'label'=>'Start Date', 'editable'=>true, 'DATA_TYPE'=>'text', 'type'=>'date', 'editrules'=>array('required'=>true)),
				array('id'=>'end_date', 'name'=>'end_date', 'label'=>'End Date', 'editable'=>true, 'DATA_TYPE'=>'text', 'type'=>'date', 'editrules'=>array('required'=>true))
			);
			$this->renderView('new_element.phtml', array('cols'=>$cols), '/jqgrid'); 
		}
	}
	
	public function checkMyname(){
		$params = $this->tool->parseParams();
		if ($this->controller->getRequest()->isPost()){
			if(!empty($params['myname'])){
				$name = '';
				$res = $this->db->query("SELECT name FROM cycle WHERE id in (".implode(',', json_decode($params['element'])).")");
				while($row = $res->fetch()){
					$myname = $row['name']."_".$params['myname'];
					$name_res = $this->db->query("SELECT id FROM cycle WHERE name='".$myname."'");
					$rows = $name_res->rowCount();
					if($rows)
						$name[] = $myname."\n";	
				}
				if(!empty($name))
					return json_encode($name);
			}
		}
		else{
		}
	}
	
	protected function calcSql($params, $doLimit = false){
		$main = $this->tool->generateMainSql($params);
        $where = $this->tool->generateWhere($params['searchConditions']);
		$order = 'id desc';
		$new_order = $this->tool->getOrderSql($params);
		if(!empty($new_order) && $new_order != 'id asc' && $new_order != $order)
			$order = $new_order;
		$limit = '';
        if ($doLimit && !empty($params['limit']))
            $limit = $this->tool->getLimitSql($params['limit']);

		return compact('main', 'where', 'order', 'limit');
	}
	
	private function getGroupCond(){
		$res = $this->userAdmin->db->query("select group_concat(distinct groups_id) as groups_id from groups_users where users_id = ".$this->currentUser);
		$info = $res->fetch();
		$cond['field'] = 'group_id';
		$cond['op'] = 'in';
		$cond['value'] = $info['groups_id'];
		return $cond;
	}
	
	public function getFormat(){
		$params = $this->tool->parseParams();
        if ($this->controller->getRequest()->isPost()){
			$data = array();
			switch($params['value']){
				case 9://mqx
					$data[0]['id'] = 5;
					$data[0]['name'] = 'zip';
					break;
				case 7://android jb42--codec
					$data[0]['id'] = 2;
					$data[0]['name'] = 'excel';
					break;
				case 11://psdk
					// $data[0]['id'] = 3;
					// $data[0]['name'] = 'yml';
					$data[1]['id'] = 4;
					$data[1]['name'] = 'html';
					break;
				default:
					break;
			}
			if(!empty($data))
				return $data;
		}
		else{
		}
	}
	
	 // public function getList(){ // 有很大的优化空间，尤其是两次查询，第一次仅仅得到总记录数，第二次加入limit条件继续查，有没有可能改成只查一次？但似乎第二次查询的时间很短
        // $this->config();
        // $ret = array();
        // $params = $this->tool->parseParams('getList');
// //print_r($params);		
// //$time = array();		
// //$time[] = date('H:i:s:u');
        // // save the rownum to cookie
        // $rownum = $params['limit']['rows'];
		// if ($rownum == 0)
			// $rownum = 'ALL';
        // $cookie = array('type'=>'rowNum', 'name'=>$this->get('db').'_'.$this->get('table'), 'content'=>json_encode(array('rowNum'=>$rownum)));
        // $this->userAdmin->saveCookie($cookie);
        
		// $sqls = $this->calcSql($params, false);
		// return;
		// $mainFields = $sqls['main']['fields'];
		// $sqls['main']['fields'] = "`{$this->get('table')}`.`id`";
        // $sql = $this->getSql($sqls, false);//true);
		// $sqls['main']['fields'] = $mainFields;
		// $res = $this->db->query($sql);
		// $ret['records'] = $res->rowCount();
		// $res->closeCursor();
        // $ret['page'] = $params['page'];
        // if ($params['limit']['rows'] > 0)
            // $ret['pages'] = ceil($ret['records'] / $params['limit']['rows']);
		// else
			// $ret['pages'] = 1;
		
		// $sqls['limit'] = $this->tool->getLimitSql($params['limit']);
		// $sql = $this->getSql($sqls);

		// $res = $this->db->query($sql);
        // $rows = array();
		// $sqlKeys = $this->tool->getSqlKeys();
        // while($row = $res->fetch()){
            // $row = $this->getMoreInfoForRow($row);
			// if (!empty($sqlKeys))
				// $row = $this->hilightKeys($row, $sqlKeys);
            // $rows[] = $row;
        // }
		// $res->closeCursor();
        // $ret['rows'] = $rows;
        // $ret['sql'] = $sql;
        // return $ret;
    // }
	
}
