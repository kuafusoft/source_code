<?php

require_once('jqgridmodel.php');
require_once('kf_editstatus.php');
require_once(str_replace("\\", "/", dirname(__FILE__)).'/playlist.php');

class xt_zzvw_cycle_detail extends jqGridModel{
    public function init($controller, array $options = null){
        $options['db'] = 'xt';
        $options['table'] = 'zzvw_cycle_detail';
        $options['columns'] = array(
			'id'=>array('view'=>false),
			'c_f'=>array('hidden'=>true, 'view'=>false, 'excluded'=>true, 'edittype'=>'text', 'editable'=>true, 'hidedlg'=>true),
			'cycle_id'=>array('hidden'=>true, 'editable'=>false, 'view'=>false),
			'testcase_id'=>array('editable'=>false, 'formatter'=>'text', 'hidden'=>true, 'view'=>false),
			'code'=>array('label'=>'Testcase', 'editable'=>false, 'query'=>true, 'hidedlg'=>true, 'unique'=>false, 'width'=>90),
			'ver'=>array('label'=>'Ver', 'editable'=>false, 'hidden'=>true, 'width'=>20),
			//'summary'=>array('label'=>'Summary', 'editable'=>false, 'width'=>65),
			'testcase_module_id'=>array('label'=>'Module','query'=>true, 'editable'=>false, 'hidden'=>true),
			'testcase_testpoint_id'=>array('label'=>'Testpoint', 'editable'=>false, 'hidden'=>true),
			//'result_type_type_id'=>array('editable'=>false, 'hidden'=>true, 'hidedlg'=>true),
			'test_env_id'=>array('label'=>'Test-Env', 'query'=>true, 'width'=>50),
			'result_type_id'=>array('label'=>'Result', 'query'=>true, 'width'=>30), 
			'testcase_priority_id'=>array('label'=>'Priority', 'query'=>true, 'editable'=>false, 'hidden'=>true, 'cols'=>6, 'queryoptions'=>array('querytype'=>'checkbox', 'value'=>'1,2,3')),
			'auto_level_id'=>array('label'=>'Auto Level', 'query'=>true, 'editable'=>false, 'width'=>50),
			'duration_minutes'=>array('hidden'=>true),
			'precondition'=>array('label'=>'Precondition', 'view'=>true, 'hidden'=>true, 'editable'=>false),
			'objective'=>array('hidden'=>true, 'editable'=>false),
			'expected_result'=>array('label'=>'Expected Result', 'view'=>true, 'hidden'=>true),
			'steps'=>array('label'=>'Steps', 'view'=>true, 'hidden'=>true),
			'comment'=>array('label'=>'CR Comment', 'editable'=>false, 'width'=>100),
			'issue_comment'=>array('label'=>'Issue Comment', 'hidden'=>true, 'editable'=>false),
			'defect_ids'=>array('label'=>'CRID', 'query'=>true, 'formatter'=>'text', 'required'=>false, 'width'=>50),
			'codec_stream_id'=>array('editable'=>false, 'view'=>false, 'hidden'=>true, 'hidedlg'=>true),
			'codec_stream_name'=>array('label'=>'Stream', 'view'=>false, 'editable'=>false, 'query'=>true, 'hidden'=>true, 'queryoptions'=>array('advanced'=>true), 'width'=>100),
			'streamid'=>array('label'=>'S-ID', 'hidden'=>true, 'view'=>false, 'editable'=>false, 'width'=>80),
			'codec_stream_location'=>array('label'=>'Location', 'view'=>false, 'hidden'=>true, 'editable'=>false, 'width'=>100),
			'codec_stream_priority'=>array('label'=>'S-Priority', 'view'=>false, 'editable'=>false, 'query'=>true, 'hidden'=>true, 'cols'=>6, 'queryoptions'=>array('querytype'=>'checkbox', 'advanced'=>true)),
			'codec_stream_result'=>array('label'=>'S-Res', 'hidden'=>true, 'view'=>false, 'excluded'=>true, 'editable'=>false, 'width'=>80),
			'codec_stream_format_id'=>array('label'=>'S-Format', 'hidden'=>true, 'view'=>false, 'editable'=>false, 'query'=>true, 'hidedlg'=>true, 'queryoptions'=>array('advanced'=>true)),
			'codec_stream_type_id'=>array('label'=>'S-Type', 'hidden'=>true, 'view'=>false, 'editable'=>false, 'query'=>true, 'hidedlg'=>true, 'queryoptions'=>array('advanced'=>true)),
			'stream_note'=>array('label'=>'S-Note', 'editable'=>false, 'view'=>false, 'hidden'=>true, 'width'=>100),
			'deadline'=>array('hidden'=>true, 'required'=>false),
			'finish_time'=>array('label'=>'Finished', 'editable'=>false, 'width'=>70),
			'tester_id'=>array('label'=>'Testor', 'query'=>true, 'queryoptions'=>array('advanced'=>true), 'width'=>35),
			'build_result_id'=>array('label'=>'B-Res', 'editable'=>false, 'width'=>30),
			'act'=>array('label'=>'Tips', 'excluded'=>true, 'editable'=>false, 'hidden'=>true, 'width'=>50),
			//'*'=>array('hidden'=>true, 'editable'=>false, 'view'=>false),
		);
//        $options['gridOptions']['subGrid'] = true;	
		$options['gridOptions']['label'] = 'cycle cases';
		$options['gridOptions']['subGrid'] = true;
		$options['gridOptions']['inlineEdit'] = false;
        $options['ver'] = '1.0';
        parent::init($controller, $options);
    } 
	
	public function getButtons(){
		$params = $this->tool->parseParams();
		$cycle = array();
		if (!empty($params['parent'])){
			$res = $this->db->query("SELECT * FROM cycle WHERE id=".$params['parent']);
			$cycle = $res->fetch();
		}
		$buttons = parent::getButtons();	
		unset($buttons['add']);		
		if (isset($cycle['cycle_status_id']) && $cycle['cycle_status_id'] == CYCLE_STATUS_ONGOING){
			//admin && cycle owner
			$isOwner = false;
			$isTester = false;
			$isAdmin = $this->userAdmin->isAdmin($this->currentUser);
			//$isOwner = $this->isOwner('cycle', $params['parent']);//该函数有点问题？？？？
			if(isset($cycle['creater_id']) && $this->currentUser == $cycle['creater_id'])
				$isOwner = true;
			if(isset($cycle['tester_ids'])){
				$testers = explode(',', $cycle['tester_ids']);
				foreach($testers as $tester){
					if($this->currentUser == $tester)
						$isTester = true;
				}
			}
			
			if($isOwner || $isAdmin || $isTester){
				$buttons['set_result'] = array('caption'=>'Set Result', 'title'=>'Set the test results');
				$buttons['set_build_result'] = array('caption'=>'Set Build Result', 'title'=>'Set the Build results');
			}
			if($isOwner || $isAdmin){
				// if($cycle['group_id'] == 3){
					$buttons['add_del_stream_actions'] = array('caption'=>'Add (or Del) Actions', 'title'=>'Action To Stream ');
					$buttons['add_del_env'] = array('caption'=>'Add (or Del) Test Env', 'title'=>'Add or Del Env');
				// }
				$buttons['set_tester'] = array('caption'=>'Assign Tester', 'title'=>'Set the tester for the cases');
				$buttons['removecase'] = array('caption'=>'Del Records', 'title'=>'Delete records in cycle');
				//$buttons['case_res'] = array('caption'=>'Codec Stream', 'title'=>'Add Codec Stream');
			}
			
		}
		$buttons['script'] = array('caption'=>'Generate Script', 'title'=>'Generate kinds of scripts');
		$buttons['playlist'] = array('caption'=>'Generate Playlist', 'title'=>'Generate kinds of playlist');
		return $buttons;
	}
	
	public function set_result(){
		$params = $this->tool->parseParams();		
		$params['element'] = json_decode($params['element']);//检查$params是否非空
		$params['c_f'] = json_decode($params['c_f']);
		$element = '';
		$element = $this->caclIDs();
		$res = $this->db->query("SELECT id FROM cycle_detail WHERE id in (".implode(',', $element).")");
		while($row = $res->fetch()){
			$this->db->update('cycle_detail', array('result_type_id'=>$params['select_item'], 'finish_time'=>date("Y-m-d H:i:s")), "id=".$row['id']);		
			$this->updatelastresult($row['id'], $params['select_item']);
		}
		//为save one 准备的
		if(count($params['element']) == 1){
			$res = $this->db->query("SELECT id, result_type_id, finish_time FROM cycle_detail WHERE id=".$params['element'][0]);
			$data = $res->fetch();
			$data['codec_stream_result'] = 'All Blank';
			$res = $this->db->query("SELECT id, name FROM result_type WHERE id=".$data['result_type_id']);
			if($result = $res->fetch())
				$data['codec_stream_result'] = 'All '.$result['name'];
			return json_encode($data);
		}
	}
	
	// private function 
	private function caclIDs(){	
		$elements = '';
		$params = $this->tool->parseParams();		
		$params['element'] = json_decode($params['element']);//检查$params是否非空
		$params['c_f'] = json_decode($params['c_f']);
		foreach($params['element'] as $k=>$v){
			if(!empty($v)){
				if($params['c_f'][$k]==1){
					//是虚行，找到codec_stream_id， 找到对应id
					$res = $this->db->query("SELECT cycle_id, codec_stream_id, test_env_id FROM cycle_detail WHERE id=".$v);
					if($info = $res->fetch()){
						if(!empty($info['codec_stream_id'])){
							$sql = "SELECT id FROM zzvw_cycle_detail WHERE cycle_id=".$info['cycle_id'].
								" AND codec_stream_id=".$info['codec_stream_id'].
								" AND test_env_id=".$info['test_env_id'];
							// foreach($params as $key=>$val){
								// if($key != "id" && $key != "element" && $key != "c_f" && $key != "flag" && $key != "codec_stream_name" && $key != "cycle_id" && $key != "replaced" && $key != "logfile_upload" && $key != "new_comment" && $key != "purpose" && $key != "cellName" && $key != "code" && $key != "new_issue_comment"){
									// if(!empty($val)){
										// $str = " AND ".$key."=".$val;
										// $sql .= $str;
									// }
								// }
							// }
							$detail_res = $this->db->query($sql);
							while($detail = $detail_res->fetch())
								$elements[] = $detail['id'];
						}
					}
				}
				else{
					//不是虚行
					$elements[] = $v;
				}
			}
		}
		return $elements;
	}
	
	private function updatelastresult($id, $result){
		$res = $this->db->query("SELECT detail.id as id, detail.testcase_id as testcase_id, detail.codec_stream_id as codec_stream_id, cycle.prj_id as prj_id, cycle.rel_id as rel_id".
			" FROM cycle_detail detail LEFT join cycle on cycle_id=cycle.id WHERE detail.id=".$id);			
		while($row = $res->fetch()){
			$tcres = $this->db->query("SELECT id FROM testcase_last_result WHERE testcase_id=".$row['testcase_id']." AND prj_id=".$row['prj_id']." AND rel_id=".$row['rel_id']." AND codec_stream_id=".$row['codec_stream_id']);
			if($data = $tcres->fetch())
				$this->db->update('testcase_last_result', array('result_type_id'=>$result, 'cycle_detail_id'=>$row['id'], 'codec_stream_id'=>$row['codec_stream_id'], 'tested'=>date("Y-m-d H:i:s")), "id=".$data['id']);
			else
				$this->db->insert('testcase_last_result', array('testcase_id'=>$row['testcase_id'], 'cycle_detail_id'=>$row['id'], 'result_type_id'=>$result, 'prj_id'=>$row['prj_id'], 'rel_id'=>$row['rel_id'], 'codec_stream_id'=>$row['codec_stream_id'], 'tested'=>date("Y-m-d H:i:s")));
			$this->db->update('testcase', array('last_run'=>date("Y-m-d H:i:s")), "id=".$row['testcase_id']);	
		}
	}
	
	public function set_tester(){
		$params = $this->tool->parseParams();
		// element and result_type_id
		$params['element'] = json_decode($params['element']);
		$params['c_f'] = json_decode($params['c_f']);
		$element = '';
		$element = $this->caclIDs();
		$res = $this->db->query("SELECT id FROM cycle_detail WHERE id in (".implode(',', $element).")");
		while($row = $res->fetch())
			$this->db->update('cycle_detail', array('tester_id'=>$params['select_item']), 'id='.$row['id']);
		if(count($params['element']) == 1)
			return 'success';//需要改？？？,全部改成功才能算success
	}
	
	//还得再看一下,怎么能使他不准使用print_r呢，还是判断条件最好不用
	public function removecases(){
		$params = $this->tool->parseParams();
		$cycle = '';
		$has_result = array();
		$no_result = array();
		$params['element'] = json_decode($params['element']);//检查$params是否非空
		$params['c_f'] = json_decode($params['c_f']);
		if (!empty($params['element'])){
			foreach($params['element'] as $k=>$v){
				$this->caclRecord($v, $params['c_f'][$k], $cycle, $has_result, $no_result);
			}
		}
		//cycle的owner admin才可以删除case
		$isAdmin = $this->userAdmin->isAdmin($this->currentUser);
		if(($cycle['creater_id'] && $this->currentUser == $cycle['creater_id']) || $isAdmin){ 
			if(!$params['flag']){
				if($no_result){
					//删除detail_step, 用到cycle_detail_id，删除与cycle_detail_id相关的所有detail_step
					$this->db->delete('cycle_detail_step', "cycle_detail_id in (".implode(',', $no_result).")");
					$this->db->delete('cycle_detail', "id in (".implode(',', $no_result).") AND cycle_id = {$cycle['id']}");//$cycle_id可以去掉的
				}
				if($has_result){
					$res = $this->db->query("SELECT code FROM zzvw_cycle_detail WHERE id in (".implode(",", $has_result).")");
					$code = $res->fetchAll();
					return json_encode($code);
				}
			}
			else{
				//删detail_step
				$this->db->delete('cycle_detail_step', "cycle_detail_id in (".implode(',', $has_result).")");
				//删除有结果的
				$this->db->delete('cycle_detail', "id in (".implode(',', $has_result).") AND cycle_id = {$cycle['id']}");//$cycle_id可以不加的
			}
		}
	}
	
	private function caclRecord($id, $c_f, &$cycle, &$has_result, &$no_result){
		if(!empty($id)){
			if($c_f==1){
				//是虚行，找到codec_stream_id， 根据此来设置其他的值
				$res = $this->db->query("SELECT cycle_id, codec_stream_id, test_env_id FROM cycle_detail WHERE id=".$id);
				if($info = $res->fetch()){
					if(!empty($info['codec_stream_id'])){
						$sql = "SELECT id, cycle_id FROM zzvw_cycle_detail WHERE cycle_id=".$info['cycle_id'].
							" AND codec_stream_id=".$info['codec_stream_id'].
							" AND test_env_id=".$info['test_env_id'];
						// $params = $this->tool->parseParams();
						// foreach($params as $k=>$v){
							// if($k != "element" && $k != "c_f" && $k != "flag" && $k != "codec_stream_name"){
								// if(!empty($v)){
									// $str = " AND ".$k."=".$v;
									// $sql .= $str;
								// }
							// }
						// }
						$detail_res = $this->db->query($sql);//" AND test_env_id=".$info['test_env_id']);
						while($detail = $detail_res->fetch()){
							if(empty($cycle) && !empty($detail['cycle_id'])){

								$cycle_res = $this->db->query("SELECT id, creater_id FROM cycle WHERE id=".$detail['cycle_id']);
								$cycle = $cycle_res->fetch();
							}
							if(empty($detail['result_type_id'])){
									$no_result[] = $detail['id'];
							}
							else{
								$has_result[] = $detail['id'];
							}
						}
					}
				}
			}
			else{
				//不是虚行，只要填写自己的就可以了
				$res = $this->db->query("SELECT * FROM cycle_detail WHERE id =".$id);
				if($detail = $res->fetch()){
					if(empty($cycle) && !empty($detail['cycle_id'])){
						$cycle_res = $this->db->query("SELECT id, creater_id FROM cycle WHERE id=".$detail['cycle_id']);
						$cycle = $cycle_res->fetch();
					}
					if(!empty($detail['result_type_id']) && $detail['result_type_id']){
							$has_result[] = $detail['id'];
					}
					else{
						$no_result[] = $detail['id'];
					}
				}
			}
		}
	}
//在分析一下？？？？	
	public function addcase(){
		//只添加case，env和codec在case 添加成功之后在detail中添加
		$params = $this->tool->parseParams();
print_r($params);
		$params['element'] = json_decode($params['element']);
		$params['ver_ids'] = json_decode($params['ver_ids']);
		$ver_ids = array();
		foreach($params['ver_ids'] as $k=>$v){
			if($v != 'undefined'){
				$ver_id = $v;
				$isexist = false;
				$testcase_id = $params['element'][$k];
				$sql = "SELECT * FROM zzvw_cycle_detail WHERE cycle_id=".$params['cycle_id']." AND testcase_id=".$testcase_id;
				$detail_res = $this->db->query($sql);
				while($detail_row = $detail_res->fetch()){//update all testcase_id 的ver
					$isexist = true;
					//ver不等时，update
					if(!empty($detail_row['codec_stream_id'])){
						if(isset($codec[$detail_row['testcase_id']]))
							continue;
						else{
							$codec[$detail_row['testcase_id']] = $detail_row['testcase_id'];
							$res = $this->db->query("SELECT test_env_id FROM cycle WHERE id=".$params['cycle_id']);
							if($info = $res->fetch()){
								$data = array('cycle_id'=>$params['cycle_id'], 'testcase_ver_id'=>$ver_id, 
									'testcase_id'=>$testcase_id, 'result_type_id'=>0, 'test_env_id'=>$info['test_env_id'], 'finish_time'=>0);
								$this->db->insert('cycle_detail', $data);
							}
						}
					}
					else{
						$datas = array();
						if ($detail_row['testcase_ver_id'] != $ver_id)
							$datas['testcase_ver_id'] = $ver_id;
						//如果result_type_id不为0时，如果replaced，则置0，
						if ($detail_row['result_type_id'] != 0){
							if ($params['replaced']){//replace所有case的result_type_id为0
								$datas['result_type_id'] = 0;
								$datas['finish_time'] = 0;
							}
						}
						if(!empty($datas))
							$this->db->update('cycle_detail', $datas, "id=".$detail_row['id']);
					}
				}
				if(!$isexist){
					$res = $this->db->query("SELECT test_env_id FROM cycle WHERE id=".$params['cycle_id']);
					if($info = $res->fetch()){
						$data = array('cycle_id'=>$params['cycle_id'], 'testcase_ver_id'=>$ver_id, 
							'testcase_id'=>$testcase_id, 'result_type_id'=>0, 'test_env_id'=>$info['test_env_id'], 'finish_time'=>0);
						$this->db->insert('cycle_detail', $data);
					}
				}
			}
		}
	}
	
	public function addCaseForCodec(){
		//设置一个default的trickmode action，将这个default的action加到进去，加到stream底下，或者，什么都不加，只加上stream，试一下子。
		$params = $this->tool->parseParams();
		$params['element'] = json_decode($params['element']);
		$params['testcase_id'] = json_decode($params['testcase_id']);
		//$params['element'] = implode(",", $params['element']);
		$res = $this->db->query("SELECT prj_id FROM cycle WHERE id=".$params['cycle_id']);
		$cycle = $res->fetch();
		foreach($params['testcase_id'] as $testcase_id){
			$t_sql = "SELECT testcase_ver_id FROM prj_testcase_ver WHERE testcase_id=".$testcase_id." AND prj_id=".$cycle['prj_id'].
				" AND edit_status_id in (".EDIT_STATUS_PUBLISHED." ,".EDIT_STATUS_GOLDEN.")";
			$t_res = $this->db->query($t_sql);
print_r('xxx');
			if($ver = $t_res->fetch()){
print_r($ver);
				foreach($params['element'] as $stream){	
					$sql = "SELECT id, testcase_ver_id, result_type_id FROM cycle_detail WHERE cycle_id=".$params['cycle_id'].
						" AND testcase_id=".$testcase_id.
						" AND test_env_id=".$params['test_env_id'].
						" AND codec_stream_id=".$stream;
					$res = $this->db->query($sql);
					if($info = $res->fetch()){
						//其实不需要查testcase_ver_id,因为case是不可用的，就只是作为add stream用的
						$datas = array();
						if ($info['testcase_ver_id'] != $ver['testcase_ver_id'])
							$datas['testcase_ver_id'] = $ver['testcase_ver_id'];
						//如果result_type_id不为0时，如果replaced，则置0，
						if ($info['result_type_id'] != 0){
							if ($params['replaced']){//replace所有case的result_type_id为0
								$datas['result_type_id'] = 0;
								$datas['finish_time'] = 0;
							}
						}
						if(!empty($datas))
							$this->db->update('cycle_detail', $datas, "id=".$info['id']);
					}
					else{
						$data = array('cycle_id'=>$params['cycle_id'], 'testcase_ver_id'=>$ver['testcase_ver_id'], 'testcase_id'=>$testcase_id, 
							'result_type_id'=>0, 'test_env_id'=>$params['test_env_id'], 'codec_stream_id'=>$stream, 'finish_time'=>0);
						$this->db->insert('cycle_detail', $data);
					}
				}
			}
		}
print_r("done");
	}
	
//认真修改	
	public function newaddcase(){
		$params = $this->tool->parseParams();
		$params['element'] = json_decode($params['element']);
		$params['c_f'] = json_decode($params['c_f']);
		$sql = "SELECT prj_id FROM cycle_detail detail LEFT JOIN cycle ON cycle.id = detail.cycle_id WHERE detail.id=".$params['element'][0];
		$res = $this->db->query($sql);
		$info = $res->fetch();
		$params['prj_id'] = $info['prj_id'];
		$records = '';
		//把取id
		$records = $this->caclIDs();
print_r($records);
		//处理
		$res = $this->db->query('SELECT * FROM zzvw_cycle_detail WHERE id in ('.implode(',',$records).') AND cycle_id!='.$params['cycle_id']);
		//判断是否有该条记录
		while($detail = $res->fetch()){
//print_r(1);
// print_r($detail);		
			//判断是否有该ver
			//testcase + prj + editstatus唯一确定一个ver，要么是publish要么是golden，不会同时存在
			//具有相同testcase_id和prj_id的不用重复去ver，只要取一次就可以了
			if(!isset($vers[$detail['testcase_id']][$detail['prj_id']])){
				$vers_res = $this->db->query("SELECT * FROM prj_testcase_ver WHERE testcase_id=".$detail['testcase_id']." AND prj_id=".$params['prj_id'].
					"  AND edit_status_id in (".EDIT_STATUS_PUBLISHED." ,".EDIT_STATUS_GOLDEN.")");
				$vers[$detail['testcase_id']][$detail['prj_id']] = $vers_res->fetch();
			}
			//ver去到之后，要添加限制条件，找到对应的detail，来更新或者insert
			if ($vers[$detail['testcase_id']][$detail['prj_id']]){
				$ver = $vers[$detail['testcase_id']][$detail['prj_id']]['testcase_ver_id'];
				$sql = "SELECT * FROM cycle_detail WHERE cycle_id=".$params['cycle_id']." AND testcase_id=".$detail['testcase_id']." AND test_env_id=".$detail['test_env_id'];
				if(!empty($detail['codec_stream_id']))
					$sql .= " AND codec_stream_id=".$detail['codec_stream_id'];
				$detail_res = $this->db->query($sql);
				if($detail_row = $detail_res->fetch()){//testcase + env + codec_stream 唯一确定一条result记录
print_r('exists');
print_r("\n");
					$datas = array();
					//ver是否是最新的
					if ($detail_row['testcase_ver_id'] != $ver)
						$datas['testcase_ver_id'] = $ver;
					//如果result_type_id不为0时，如果replaced，则置0，
					if ($detail_row['result_type_id'] != 0){
						if ($params['replaced']){//replace所有case的result_type_id为0
							$datas['result_type_id'] = 0;
							$datas['finish_time'] = 0;
						}
					}
					if(!empty($datas))
						$this->db->update('cycle_detail', $datas, "id=".$detail_row['id']);
				}
				// else if($env){
				else {
print_r('does not exists');
print_r("\n");
					$data = array('cycle_id'=>$params['cycle_id'], 'testcase_ver_id'=>$ver, 'testcase_id'=>$detail['testcase_id'], 
					'result_type_id'=>0, 'test_env_id'=>$detail['test_env_id'], 'codec_stream_id'=>$detail['codec_stream_id'], 'finish_time'=>0);
					$this->db->insert('cycle_detail', $data);
				}
				unset($ver);
			}
		}	
	}
	//改$pair, 查看是否有codec_stream_id, 找出来, 均save
	protected function _save($db, $table, $pair){
		foreach($pair as $k=>$v){
			if(isset($v['c_f']) && $v['c_f'] == 1){
				if(isset($v['id']) && $v['id']){
					$res = $this->db->query("SELECT codec_stream_id, cycle_id FROM cycle_detail WHERE id=".$v['id']);
					while($row = $res->fetch()){
						$sql = "SELECT id FROM cycle_detail WHERE cycle_id=".$row['cycle_id'].
							" AND codec_stream_id=".$row['codec_stream_id']." AND id!=".$pair[$k]['id'];
						$d_res = $this->db->query($sql);
						while($d = $d_res->fetch()){		
							$data = $v;			
							foreach($data as $key=>$val){
								if(($key != 'result_type_id')){
									 if($key != 'tester_id')
										unset($data[$key]);
								}
							}
							$data['id'] = $d['id'];
							$pair[] = $data;
							unset($data);
						}
					}
				}
			}
		}
print_r($pair);
        foreach($pair as $i => $e){
//print_r($e);
			$affectedID = $this->_saveOne($db, $table, $e);
        }
        return $affectedID;
    }
	
	protected function _saveOne($db, $table, $pair){
		// if(!empty($pair['id'])){
			// $date = date('Y-m-d H:i:s'); 
			// $author = $this->currentUserName;
			// $res = $this->db->query("SELECT comment, issue_comment FROM cycle_detail WHERE id=".$pair['id']);
			// $detail = $res->fetch();
			// if($pair['comment'] != $detail['comment'])
				// $pair['comment'] = $author.":".$date."--".$pair['comment'];
			// if($pair['issue_comment'] != $detail['issue_comment'])
				// $pair['issue_comment'] = $author.":".$date."--".$pair['issue_comment'];
		// }
		$affectID = parent::_saveOne($db, 'cycle_detail', $pair);
		return $affectID;
	}
	
	
    protected function getMoreInfoForRow($row){
		$row['codec_stream_result'] = 'does not exsit';
		if(isset($row['c_f']) && $row['c_f'] == 1){
			//看如果说能够用group cat的话，如果说是有fail的话，就将result_type置空
			//如果说全pass的话，就填原值，我觉得这个比较合理一些
			$d_res = $this->db->query("SELECT GROUP_CONCAT(DISTINCT result_type_id) AS result_type_id".
				" FROM cycle_detail WHERE codec_stream_id=".$row['codec_stream_id'].
				" AND cycle_id=".$row['cycle_id']." GROUP BY codec_stream_id, test_env_id");
			if($d = $d_res->fetch()){
				if(strlen($d['result_type_id']) == 1){
					$row['codec_stream_result'] = 'All Blank';
					$r_res = $this->db->query("SELECT id, name FROM result_type WHERE id=".$d['result_type_id']);
					if($r = $r_res->fetch())
						$row['codec_stream_result'] = 'All '.$r['name'];
				}
				else{
					$d['result_type_id'] = ','.$d['result_type_id'];
// print_r($d['result_type_id']);
					if(strpos($d['result_type_id'], '0')){
						$row['result_type_id'] = 102;// Testing
						$row['codec_stream_result'] = 'Has Blank';
						if(strpos($d['result_type_id'], '1'))
							$row['codec_stream_result'] = 'Has Blank & Pass';
						if(strpos($d['result_type_id'], '2'))
							$row['codec_stream_result'] = 'Has Blank & Fail';
					}
					else if(strpos($d['result_type_id'], '2')){
						$row['result_type_id'] = 101;// All tested
						$row['codec_stream_result'] = 'Has Fail';
						if(strpos($d['result_type_id'], '1'))
							$row['codec_stream_result'] = 'Has Fail & Pass';
					}
					else{
						$row['result_type_id'] = 101;//All tested
						$row['codec_stream_result'] = 'Other Results';
						if(strpos($d['result_type_id'], '1'))
							$row['codec_stream_result'] = 'Has Pass & Other Results';
						if(strpos($d['result_type_id'], '2'))
							$row['codec_stream_result'] = 'Has Fail & Other Results';
					}
				}
			}
		}
		return $row;
	}
	
	protected function getInformationViewParams($params){
		$db = $this->get('db');
		$table = $this->get('table');
		$view_params = parent::getInformationViewParams($params);
		$view_params['tabs']['view_edit']['dir'] = 'xt/zzvw_cycle_detail';
		return $view_params;
	}
/*	
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
*/	
        //by zxy 20130801
	// public function updateForCycleCases($cycle_id){
		// $res = $this->db->query("SELECT COUNT(*) as cases FROM cycle_detail WHERE cycle_id={$cycle_id}");
		// $info = $res->fetch();
		// $total_cases = $info['cases'];
		
		// $res = $this->db->query("SELECT COUNT(*) as cases FROM cycle_detail WHERE cycle_id={$cycle_id} and result_type_id=".RESULT_TYPE_PASS);
		// $info = $res->fetch();
		// $pass_cases = $info['cases'];
		
		// $pass_rate = 0;
		// $color = 'red';
		// if ($total_cases > 0){
			// $pass_rate = number_format($pass_cases/$total_cases * 100, 2);
			// if ($pass_rate >= 85)
				// $color = 'blue';
			// else if ($pass_rate >= 60)
				// $color = 'gray';
		// }
		// $pass_and_rate = sprintf("<span style='color:$color'>%-6d [%5.2f%%]</span>", $pass_cases, $pass_rate);
		
		// $res = $this->db->query("SELECT COUNT(*) as cases FROM cycle_detail WHERE cycle_id={$cycle_id} and result_type_id=".RESULT_TYPE_FAIL);
		// $info = $res->fetch();
		// $fail_cases = $info['cases'];
		
		// //$this->db->update('cycle', array('total_cases'=>$total_cases, 'pass_and_rate'=>$pass_and_rate, 'pass_cases'=>$pass_cases, 'pass_rate'=>$pass_rate, 'fail_cases'=>$fail_cases), "id = {$cycle_id}");
	// }
	
	public function getButtonFlag(){
		$params = $this->tool->parseParams();
//print_r($params);
		if (!empty($params['hidden'])){
			$hidden = json_decode($params['hidden']);
			foreach($hidden as $k=>$v){
				if($k='cycle_id' && !empty($v)){
					$res = $this->db->query("SELECT * FROM cycle WHERE id=$v");
					$cycle = $res->fetch();
					if (isset($cycle['cycle_status_id'])){ 
						if($cycle['cycle_status_id'] == CYCLE_STATUS_ONGOING){
							//admin && cycle owner
							$isOwner = false;
							$isAdmin = $this->userAdmin->isAdmin($this->currentUser);
							//$isOwner = $this->isOwner('cycle', $params['parent']);//该函数有点问题？？？？
							if(isset($cycle['creater_id']) && $this->currentUser == $cycle['creater_id'])
								$isOwner = true;
							if(!$isOwner && !$isAdmin)
								return false;
						}
						else if($cycle['cycle_status_id'] == CYCLE_STATUS_FROZEN){
							return  false;
						}
					}
				}
			}
		}
		return true;
	}
	
	public function resultInfo(){
		$params = $this->tool->parseParams();
		if ($this->controller->getRequest()->isPost()){
            //添加view
			if (!empty($params['element'])){		
				$sql = "SELECT cycle_id FROM cycle_detail WHERE id=".$params['element'];
				$res = $this->db->query($sql);
				$detail = $res->fetch();
				if($detail){
					$sql = "SELECT name FROM cycle WHERE id=".$detail['cycle_id'];
					$res = $this->db->query($sql);
					$cycle = $res->fetch();
					$res = $this->db->query("SELECT testcase_id, issue_comment, comment FROM cycle_detail WHERE id=".$params['element']);
					$cycle_detai = $res->fetch();
					$res = $this->db->query("SELECT code FROM testcase WHERE id=".$cycle_detai['testcase_id']);
					$code = $res->fetch();			
					$res = $this->db->query("SELECT id, name FROM result_type");
					$results[0] = '';
					while($result = $res->fetch())
						$results[$result['id']] = $result['name'];				
					$res = $this->db->query("SELECT id, name FROM test_env");
					//$envs[0] = '';
					while($env = $res->fetch())
						$envs[$env['id']] = $env['name'];					
					// $format = array('', 'txt', 'excel', 'yml', 'html', 'zip');
					$cols = array(
						array('id'=>'cycle_id', 'name'=>'cycle_id', 'label'=>'Test Cycle', 'editable'=>false, 'DATA_TYPE'=>'text', 'type'=>'text', 'defval'=>$cycle['name']),
						array('id'=>'code', 'name'=>'code', 'label'=>'Test Case', 'editable'=>false, 'DATA_TYPE'=>'text', 'type'=>'text', 'defval'=>$code['code']),
						array('id'=>'test_env_id', 'name'=>'test_env_id', 'label'=>'Test Env', 'editable'=>true, 'DATA_TYPE'=>'int', 'type'=>'select', 'editoptions'=>array('value'=>$envs)), //'editrules'=>array('required'=>true)),
						array('id'=>'result_type_id', 'name'=>'result_type_id', 'label'=>'result', 'editable'=>true, 'DATA_TYPE'=>'int', 'type'=>'select', 'defval'=>$params['result_type_id'], 'editoptions'=>array('value'=>$results), 'editrules'=>array('required'=>true)),
						array('id'=>'comment', 'name'=>'comment', 'label'=>'CR Comment', 'editable'=>true, 'editable'=>false, 'DATA_TYPE'=>'text', 'type'=>'textarea', 'defval'=>$cycle_detai['comment']),
						array('id'=>'new_comment', 'name'=>'new_comment', 'label'=>'New CR Comment', 'editable'=>true, 'DATA_TYPE'=>'text', 'type'=>'textarea'),
						array('id'=>'defect_ids', 'name'=>'defect_ids', 'label'=>'CR', 'editable'=>true, 'DATA_TYPE'=>'text', 'type'=>'text', 'defval'=>$cycle_detai['issue_comment']),
						//array('id'=>'file_format', 'name'=>'file_format', 'label'=>'Log Foramt', 'editable'=>true, 'DATA_TYPE'=>'text', 'type'=>'select', 'editoptions'=>array('value'=>$format)),
						array('id'=>'logfile', 'name'=>'logfile', 'label'=>'Logfile', 'editable'=>true, 'DATA_TYPE'=>'text', 'type'=>'file'),
						array('id'=>'issue_comment', 'name'=>'issue_comment', 'label'=>'Issue Comment', 'editable'=>false, 'DATA_TYPE'=>'text', 'type'=>'textarea', 'defval'=>$cycle_detai['issue_comment']),
						array('id'=>'new_issue_comment', 'name'=>'new_issue_comment', 'label'=>'New Issue Comment', 'editable'=>true, 'DATA_TYPE'=>'text', 'type'=>'textarea'),
						array('id'=>'submit_a_cr', 'name'=>'submit_a_cr', 'label'=>'Submit A CR', 'editable'=>true, 'DATA_TYPE'=>'int', 'type'=>'checkbox', 'editoptions'=>array('value'=>array(1=>'submit'))),
					);
					$this->renderView('new_element.phtml', array('cols'=>$cols, 'id'=>$params['element']));
				}
			}	                                    
		}
		else{
			
		}
	}
	
	public function crInfo(){
		$params = $this->tool->parseParams();
		if ($this->controller->getRequest()->isPost()){                                    
		}
		else{
			//添加view
			if (!empty($params['element'])){				
				$cols = array(
					array('name'=>'cq_password', 'label'=>'CQ Password', 'editable'=>true, 'DATA_TYPE'=>'text', 'type'=>'password'),
					array('name'=>'cr_headline', 'label'=>'CR Headline', 'editable'=>true, 'DATA_TYPE'=>'text', 'type'=>'text'),
					array('name'=>'cr_description', 'label'=>'CR Description', 'editable'=>true, 'DATA_TYPE'=>'text','type'=>'textarea')
				);
				$this->renderView('cr_info.phtml', array('cols'=>$cols));
			}	
		}
	}
	
	public function saveOneResult(){
		$date = date('Y-m-d H:i:s');
		$params = $this->tool->parseParams();
		$params['element'] = json_decode($params['element']);//检查$params是否非空
		if ($this->controller->getRequest()->isPost()){
			$data = array();
			if(!empty($params['test_env_id']))
				$data['test_env_id'] = $params['test_env_id'];
			if(!empty($params['result_type_id']))
				$data['result_type_id'] = $params['result_type_id'];
			if(!empty($params['defect_ids']))
				$data['defect_ids'] = $params['defect_ids'];
			$data['finish_time'] = date("Y-m-d H:i:s");
			// if(!empty($params['comment']))
				// $data['comment'] = $params['comment'];
			$element = $this->caclIDs();
			$res = $this->db->query("SELECT id FROM cycle_detail WHERE id in (".implode(',', $element).")");
			while($row = $res->fetch()){
				$this->db->update('cycle_detail', $data, "id=".$row['id']);	
				if(!empty($params['result_type_id']))
					$this->updatelastresult($row['id'], $params['result_type_id']);
			}
			if(!empty($params['new_comment'])){
				$this->addfeildnew($params['element'][0], $params['new_comment'], 'comment');
			}
			if(!empty($params['new_issue_comment'])){
				$this->addfeildnew($params['element'][0], $params['new_issue_comment'], 'issue_comment');
			}
			//logfile，存放在php端的logfile文件夹中
			//$filename = '';
			
			//发送至CQ的处理，waiting
			/*if(!empty($params['submit_a_cr'])){
					
				}
			}*/
			$datas_res = $this->db->query("SELECT id, comment, result_type_id, issue_comment, test_env_id, defect_ids, finish_time FROM cycle_detail WHERE id=".$params['element'][0]);
			$datas = $datas_res->fetch();
			if(empty($datas['comment']))
				$datas['comment'] = 'null';
			if(empty($datas['issue_comment']))
				$datas['issue_comment'] = 'null';
			if(empty($datas['defect_ids']))
				$datas['defect_ids'] = 'null';
			$datas['codec_stream_result'] = 'All Blank';
			$res = $this->db->query("SELECT id, name FROM result_type WHERE id=".$datas['result_type_id']);
			if($result = $res->fetch())
				$datas['codec_stream_result'] = 'All '.$result['name'];
			return (json_encode($datas));
		}
		else{
				
		}
	}
	
	private function addfeildnew($id, $data, $feild){
		$author = $this->currentUserName;
		$res = $this->db->query("SELECT ".$feild." FROM cycle_detail WHERE id=".$id);
		$info = $res->fetch();
		if(empty($info[$feild])){
			$sql = "UPDATE cycle_detail SET ".$feild."='".$author.
			":".date('Y-m-d H:i:s')."--".mysql_real_escape_string($data)."' WHERE id=".$id;
		}
		else{
			$sql = "UPDATE cycle_detail SET ".$feild."=concat('".$author.
			":".date('Y-m-d H:i:s')."--','".mysql_real_escape_string($data)."', ".$feild.", '\\r\\n') WHERE id=".$id;
		}
		$res = $this->db->query($sql);
	}
	
	public function getGridOptions(){
		$col = '';
		$key = '';
		$isOwner = false; 
		$isTester = false; 
		$isAdmin = false;	
		$params = $this->tool->parseParams();
		parent::getGridOptions();
		$colModel = $this->options['gridOptions']['colModel'];
		if (!empty($params['parent'])){
			$res = $this->db->query("SELECT * FROM cycle WHERE id=".$params['parent']);
			$cycle = $res->fetch();
			if($cycle['cycle_status_id'] == CYCLE_STATUS_ONGOING){
				$isAdmin = $this->userAdmin->isAdmin($this->currentUser);
				if(isset($cycle['creater_id']) && $this->currentUser == $cycle['creater_id'])
					$isOwner = true;
				if(isset($cycle['tester_ids'])){
					$testers = explode(',', $cycle['tester_ids']);
					foreach($testers as $tester){
						if($this->currentUser == $tester)
							$isTester = true;
					}
				}
			}
		}
		if(!($isOwner || $isAdmin || $isTester))
			$this->options['gridOptions']['inlineEdit'] = false;
		foreach($colModel as $k=>&$m){	
			$key = $k;
			if($isOwner || $isAdmin || $isTester){
				if ($m['name'] == 'build_result_id'){
					if($m['formatter'] == 'select'){
						$colModel[$k]['formatter'] = 'bResultLink';
					}
				}
				if($isOwner || $isAdmin){
					if ($m['name'] == 'tester_id'){
						if($m['formatter'] == 'select'){
							$colModel[$k]['formatter'] = 'testorLink';
						}
					}
				}
			}
			if ($m['name'] == 'result_type_id'){
				$m['searchoptions']['value'][100] = '--Blank--';
				$m['formatoptions']['value'][100] = '--Blank--';
				$col = $colModel[$k];
				if($isOwner || $isAdmin || $isTester)
					$m['formatter'] = 'resultLink';
			}
			if ($m['name'] == 'testcase_priority_id'){
				$priority = $colModel[$k];
			}
			if ($m['name'] == 'codec_stream_priority'){
				$m = $priority;
				$m['name'] = 'codec_stream_priority';	
				$m['label'] = 'S-Priority';
				$m['index'] = $k;
				$m['COLUMN_NAME'] = 'codec_stream_priority';
				$m['query'] = false;
				$m['COLUMN_POSITION'] = $k;
				$m['editable'] = false;
				$m['width'] = 30;
				$m['hidden'] = true;
				$m['view'] = false;
				$m['query'] = true;
				$m['cols'] = 6;
				$m['queryoptions']['querytype'] = 'checkbox';
				$m['queryoptions']['advanced'] = 'true';
			}
			if ($m['name'] == 'build_result_id'){
				$m = $col;
				$m['name'] = 'build_result_id';	
				$m['label'] = 'B-Res';
				$m['index'] = 'build_result_id';
				$m['COLUMN_NAME'] = 'build_result_id';
				$m['query'] = false;
				$m['COLUMN_POSITION'] = $key + 1;
				$m['editable'] = false;
				$m['width'] = 30;
				$m['hidden'] = true;
			}
		}
		$this->options['gridOptions']['colModel'] = $colModel;
        return $this->options;
    }
	
	protected function calcSql($params, $doLimit = false){
		//blank时，只显示未填结果的部分
		$paramsValues = $this->tool->parseParams();
		if(isset($paramsValues['result_type_id']) && ($paramsValues['result_type_id'] == 100)){
			foreach($params['searchConditions'] as $k=>&$v){
				if(isset($v['field']) && ($v['field'] == 'result_type_id'))
					$params['searchConditions'][$k]['value'] = 0;
			}
		}
		if(isset($paramsValues['tester_id']) && ($paramsValues['tester_id'] == 100)){
			foreach($params['searchConditions'] as $k=>&$v){
				if(isset($v['field']) && ($v['field'] == 'tester_id'))
					$params['searchConditions'][$k]['value'] = 0;
			}
		}
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
	
	public function add_del_env(){
		$params = $this->tool->parseParams();
// print_r($params);
		if ($this->controller->getRequest()->isPost()){
			if (!empty($params['test_env_id'])){
				$params['element'] = json_decode($params['element']);
				$params['c_f'] = json_decode($params['c_f']);
				$params['test_env_id'] = json_decode($params['test_env_id']);
				$element = '';
				$cycle_id = '';
				$element = $this->caclIDs();
				if(!$params['isDel']){
					$res = $this->db->query("SELECT * FROM cycle_detail WHERE id in (".implode(',', $element).")");
					while($row = $res->fetch()){//if就可以了
						if(empty($cycle_id))
							$cycle_id = $row['cycle_id'];
						$this->set_feild($row, $params['test_env_id'], $cycle_id, 'test_env_id');
					}	
				}
				else{
					foreach($params['test_env_id'] as $env){
					//最好给一个警告或者选择
						$this->db->delete('cycle_detail', "id in (".implode(',', $element).") AND test_env_id = ".$env);
					}
				}
			}                                    
		}
		else{	
		}
	}
	
	private function set_feild($data, $fvals, $cycle_id, $feild){
print_r($fvals);
		if(!empty($cycle_id)){//可以去掉
			if($feild == 'test_env_id')
				$cond = 'codec_stream_id';
			else if($feild == 'codec_stream_id'){
				$cond = 'test_env_id';
			}
			$condition = " testcase_id={$data['testcase_id']} AND testcase_ver_id={$data['testcase_ver_id']}";
			if($data[$cond])
				$condition .= " AND ".$cond."={$data[$cond]}";
			$condition .= " AND cycle_id={$cycle_id}";
			$d_res = $this->db->query("SELECT ".$feild." FROM cycle_detail WHERE".$condition);
			while($row = $d_res->fetch()){
				foreach($fvals as $k=>$val){
					if($val == $row[$feild])
						unset($fvals[$k]);
				}
			}
			$d_res = $this->db->query("SELECT id, ".$feild." FROM cycle_detail WHERE".$condition);
			//查询此条件下该记录的test_env_id是否为空，如果为空，update
			while($row = $d_res->fetch()){
				if(empty($row[$feild])){
					foreach($fvals as $k=>$val){
						if(!empty($val)){
							$this->db->update('cycle_detail', array($feild=>$val), "id=".$row['id']);
							unset($fvals[$k]);
						}
					}
				}
			}
			if(!empty($fvals)){
				foreach($fvals as $val){
					//insert剩下env的记录
					if($val != $data[$feild]){
						$data = array('cycle_id'=>$cycle_id,'testcase_id'=>$data['testcase_id'], 
							'testcase_ver_id'=>$data['testcase_ver_id'], 'result_type_id'=>0, $cond=>$data[$cond], $feild=>$val, 'finish_time'=>0);
						$this->db->insert('cycle_detail', $data);
					}
				}
			}
		}
	}
	
	public function add_del_stream_actions(){
		$params = $this->tool->parseParams();
		$cycle = '';
		$params['element'] = json_decode($params['element']);//检查$params是否非空
		$params['actions'] = json_decode($params['actions']);
		$actions = implode(",", $params['actions']);
		$params['c_f'] = json_decode($params['c_f']);
		$stream = array();
		$id = array();
		$res = $this->db->query("SELECT test_env_id, prj_id FROM cycle WHERE id=".$params['cycle_id']);
		$cycle = $res->fetch();
		foreach($params['element'] as $k=>$v){
			if(!empty($v)){
				if($params['c_f'][$k]==1){
					//是虚行，找到codec_stream_id， 找到对应id
					$res = $this->db->query("SELECT codec_stream_id, test_env_id FROM cycle_detail WHERE id=".$v);
					$info = $res->fetch();
					if($params['isDel'] == 1){
						$sql = "SELECT id FROM cycle_detail WHERE cycle_id=".$params['cycle_id'].
							" AND codec_stream_id=".$info['codec_stream_id'];
						if(!empty($info['test_env_id']))
							$sql .= " AND test_env_id=".$info['test_env_id'];
						$sql .= " AND testcase_id in ($actions)";	
						$res = $this->db->query($sql);
						while($data = $res->fetch()){
							$id[] = $data['id'];
						}	
					}
					else if($params['isDel'] == 0){
						foreach($params['actions'] as $case){
							$res = $this->db->query("SELECT testcase_ver_id FROM prj_testcase_ver WHERE testcase_id=".$case." AND prj_id=".$cycle['prj_id']." AND edit_status_id in (".EDIT_STATUS_PUBLISHED." ,".EDIT_STATUS_GOLDEN.")");
							$ver = $res->fetch();
							$d_sql = "SELECT id, testcase_ver_id FROM cycle_detail WHERE cycle_id=".$params['cycle_id'].
								" AND testcase_id=".$case.
								" AND codec_stream_id=".$info['codec_stream_id'].
								" AND test_env_id=".$info['test_env_id'];
							$res = $this->db->query($d_sql);
							if($d_info = $res->fetch()){//查看是否已有记录,如果有,更新到最新的ver
								if($d_info['testcase_ver_id'] == $ver['testcase_ver_id'])
									continue;
								$data = array('cycle_id'=>$params['cycle_id'], 'testcase_ver_id'=>$ver['testcase_ver_id']);
								$this->db->update('cycle_detail', $data, 'id='.$d_info['id']);
							}
							else{
								$data = array('cycle_id'=>$params['cycle_id'], 'testcase_ver_id'=>$ver['testcase_ver_id'], 'testcase_id'=>$case,
									'result_type_id'=>0, 'test_env_id'=>$info['test_env_id'], 'codec_stream_id'=>$info['codec_stream_id'], 'finish_time'=>0);
								$this->db->insert('cycle_detail', $data);
							}

						}
					}
				}
			}
		}
		if($params['isDel'] == 1){
			if(!empty($id)){
				$id = implode(",", $id);
				$this->db->delete('cycle_detail_step', "cycle_detail_id in (".$id.")");
				$this->db->delete('cycle_detail', "id in (".$id.") AND cycle_id = {$params['cycle_id']}");
			}
			print_r('success');
		}
	}
	
	protected function getViewEditButtons($params){
		// check if current user is the owner or admin
		$cycle = '';
		$btns = '';
		if(!empty($params['element'])){
			$res = $this->db->query("SELECT * FROM cycle_detail WHERE id =".$params['element']);
			if($cycle_detail = $res->fetch()){
				$res = $this->db->query("SELECT * FROM cycle WHERE id=".$cycle_detail['cycle_id']);
				$cycle = $res->fetch();
			}
		}
		
		$style = 'position:relative;float:right';
		$display = $style;
		$hide = $style.';display:none';	
		if (isset($cycle['cycle_status_id']) && $cycle['cycle_status_id'] == CYCLE_STATUS_ONGOING){
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
			if($isOwner || $isAdmin || $isTester){
				$btns = parent::getViewEditButtons($params);
				unset($btns['cloneit']);
			}
		}
		return $btns;
	}
	
	/*public function case_res1(){
		$params = $this->tool->parseParams();
		if ($this->controller->getRequest()->isPost()){
            if(!empty($params['element']) && !empty($params['resource_id'])){
				$cycle_id = '';
				if(is_array($params['element']))
					$parmas['element'] = implode(",", $parmas['element']);
				//if(is_array($params['resource_id']))
					//$resource_ids = implode(",", $parmas['resource_id']);
					
				$res = $this->db->query("SELECT * FROM cycle_detail WHERE id in (".$parmas['element'].")");
				while($info = $res->fetch()){
					if(empty($cycle_id))
						$cycle_id = $info['cycle_id'];
					$this->db->update('cycle_detail', array('resource_id'=>$params['resource_id'][0]), "id = ".$info['id']);
					unset($params['resource_id'][0]);
					foreach($params['resource_id'] as $resouce){
						$affectID = $this->db->insert('cycle_detail', array('cycle_id'=>$cycle_id,'testcase_id'=>$info['testcase_id'], 'testcase_ver_id'=>$info['testcase_ver_id'], 'resource_id'=>$resource));
					}
				}
			}
		}
		else{
		}
	}*/
	public function case_res(){
		$params = $this->tool->parseParams();
		if ($this->controller->getRequest()->isPost()){
            if(!empty($params['element']) && !empty($params['resource_id'])){
				$params['element'] = json_decode($params['element']);
				$params['resource_id'] = json_decode($params['resource_id']);
				$params['c_f'] = json_decode($params['c_f']);
				$element = '';
				$cycle_id = '';
				$element = $this->caclIDs();
				$res = $this->db->query("SELECT * FROM cycle_detail WHERE id in (".implode(',', $element).")");
				while($row = $res->fetch()){//if就可以了
					if(empty($cycle_id))
						$cycle_id = $row['cycle_id'];
					foreach($params['resource_id'] as $resource)
						$this->set_feild($row, $params['resource_id'], $cycle_id, 'codec_stream_id');
				}
			}
		}
		else{
		}
	}
	
	public function getModule(){
		$params = $this->tool->parseParams();
		$sql = "SELECT DISTINCT testcase_module.id as id, testcase_module.name as name FROM cycle_detail LEFT JOIN testcase ON cycle_detail.testcase_id=testcase.id LEFT JOIN testcase_module ON testcase.testcase_module_id=testcase_module.id";
		$where = "1";
		if(!empty($params['value']) && $params['value']){
			$where = "cycle_detail.cycle_id=".$params['value'];
		}
		$where .= " AND name is not null";
		$sql .= " WHERE $where ORDER BY name ASC";
		$res = $this->db->query($sql);
		return json_encode($res->fetchAll());
	}
	
	public function getPriority(){
		$params = $this->tool->parseParams();
		$sql = "SELECT DISTINCT testcase_priority.id as id, testcase_priority.name as name FROM cycle_detail LEFT JOIN testcase_ver ON cycle_detail.testcase_ver_id=testcase_ver.id LEFT JOIN testcase_priority ON testcase_ver.testcase_priority_id=testcase_priority.id";
		$where = "1";
		if(!empty($params['value']) && $params['value']){
			$where = "cycle_detail.cycle_id=".$params['value'];
		}
		$where .= " AND name is not null";
		$sql .= " WHERE $where ORDER BY name ASC";
		$res = $this->db->query($sql);
		return json_encode($res->fetchAll());
	}
	
	public function getAutoLevel(){
		$params = $this->tool->parseParams();
		$sql = "SELECT DISTINCT auto_level.id as id, auto_level.name as name FROM cycle_detail LEFT JOIN testcase_ver ON cycle_detail.testcase_ver_id=testcase_ver.id LEFT JOIN auto_level ON testcase_ver.auto_level_id=auto_level.id";
		$where = "1";
		if(!empty($params['value']) && $params['value']){
			$where = "cycle_detail.cycle_id=".$params['value'];
		}
		$where .= " AND name is not null";
		$sql .= " WHERE $where ORDER BY name ASC";
		$res = $this->db->query($sql);
		return json_encode($res->fetchAll());
	}
	public function getCycleStreamActions(){
		$params = $this->tool->parseParams();
		if ($this->controller->getRequest()->isPost()){                              
		}
		else{
			$cols = array();
			$cart_data = new stdClass;
			$cart_data->filters = '{"groupOp":"AND","rules":[{"field":"testcase_type_id","op":"eq","data":2}, {"field":"isactive","op":"eq","data":1}, {"field":"testcase_module_id","op":"eq","data":9}]}';
			$cols[] = array('id'=>'testcase_id', 'name'=>'testcase_id', 'label'=>'Actions', 'editable'=>true, 'DATA_TYPE'=>'text', 'editoptions'=>array('value'=>array()), 'type'=>'cart', 'cart_db'=>'xt', 'cart_table'=>'testcase', 'cart_data'=>json_encode($cart_data), 'editrules'=>array('required'=>true));
			$res = $this->db->query("SELECT id, name FROM test_env");
			$env = array();
			$env[0] = '';
			while($info = $res->fetch()){
				$env[$info['id']] = $info['name'];
			}
			$cols[] = array('id'=>'test_env_id', 'name'=>'test_env_id', 'label'=>'Test Env', 'editable'=>true, 'DATA_TYPE'=>'text', 'editoptions'=>array('value'=>$env), 'type'=>'select', 'editrules'=>array('required'=>true));
			$this->renderView('new_element.phtml',  array('cols'=>$cols), '/jqgrid');
		}
	}
	// public function getCodecStream(){
		// $params = $this->tool->parseParams();
		// $sql = "SELECT DISTINCT codec_stream.id as id, codec_stream.name as name FROM cycle_detail LEFT JOIN codec_stream ON cycle_detail.codec_stream_id=codec_stream.id";
		// $where = "1";
		// if(!empty($params['value']) && $params['value']){
			// $where = "cycle_detail.cycle_id=".$params['value'];
		// }
		// $where .= " AND name is not null";
		// $sql .= " WHERE $where ORDER BY name ASC";
		// $res = $this->db->query($sql);
		// return json_encode($res->fetchAll());
	// }
	
	public function getTester(){
		$params = $this->tool->parseParams();
		$where = "1";
		if($params['condition']){
			$where = "id=".$params['condition'];
		}
		$sql = "SELECT tester_ids FROM cycle WHERE ".$where;
		$res = $this->db->query($sql);
		$cycle = $res->fetch();
		$tester = explode(",", $cycle['tester_ids']);
		foreach($tester as $key=>$val){
			if(empty($val)){
				unset($tester[$key]);
			}
		}
		$cycle['tester_ids'] = implode(",", $tester);
		$sql = "SELECT id, nickname as name FROM users WHERE id in (".$cycle['tester_ids'].")";
		$res = $this->userAdmin->db->query($sql);
		$this->renderView('select_item.phtml', array('type'=>"tester", 'items'=>$res->fetchAll()), '/jqgrid');
	}
	
	public function getStreamTag(){
		$params = $this->tool->parseParams();
		$sql = "SELECT `id`, `name`, `element_id` FROM `tag`";
		$where = "`table`='xt.codec_stream'";
		$where .= " AND name is not null";
		$sql .= " WHERE $where ORDER BY name ASC";
		$res = $this->db->query($sql);
		$data = $res->fetchAll();
		foreach($data as $k=>$v){
			$i = 0;
			$res = $this->db->query("SELECT id FROM cycle_detail WHERE codec_stream_id in(".$v['element_id'].") AND cycle_id=".$params['value']);
			unset($data[$k]['element_id']);
			while($info = $res->fetch())
				$i++;
			if($i == 0)
				unset($data[$k]);
		}
		return json_encode($data);
	}
	
	public function getUpload(){
		$params = $this->tool->parseParams();
		$sql = "SELECT * FROM os";
		$where = "1";
		$where .= " AND name is not null";
		$sql .= " WHERE $where ORDER BY name ASC";
		$res = $this->db->query($sql);
		return json_encode($res->fetchAll());
	}
	
	public function cycle_cases(){
		$params = $this->tool->parseParams();
		if ($this->controller->getRequest()->isPost()){
                                          
		}
		else{
			$data = '';
			$data['prj_id'] = '';
			if(isset($params['element'])){
				$res = $this->db->query("SELECT prj_id FROM cycle WHERE id=".$params['element']);
				$data = $res->fetch();
			}
			$cols = array(
				array('name'=>'code', 'label'=>'Testcase', 'query'=>'true',  'editable'=>true, 'DATA_TYPE'=>'text', 'type'=>'text'),
				//array('name'=>'id', 'label'=>'Test Cycle', 'query=>true', 'editable'=>false, 'DATA_TYPE'=>'text', 'type'=>'select'),
				array('name'=>'prj_id', 'label'=>'Prj', 'query'=>'true',  'editable'=>false, 'DATA_TYPE'=>'int', 'type'=>'select', 'queryoptions'=>array('value'=>$data['prj_id']), 'searchoptions'=>array('value'=>'')),
				array('name'=>'testcase_type_id', 'label'=>'Cycle Type', 'query'=>'true', 'editable'=>true, 'DATA_TYPE'=>'int', 'type'=>'select', 'searchoptions'=>array('value'=>'')), //'editrules'=>array('required'=>true)),
				//array('name'=>'myname', 'label'=>'result', 'query'=>'true',  'editable'=>true, 'DATA_TYPE'=>'int', 'type'=>'text'),
				array('name'=>'compiler_id', 'label'=>'Compiler', 'query'=>'true',  'editable'=>true, 'DATA_TYPE'=>'int', 'type'=>'select', 'searchoptions'=>array('value'=>'')),
				array('name'=>'build_target_id', 'label'=>'Build Target', 'query'=>'true',  'editable'=>true, 'DATA_TYPE'=>'int', 'type'=>'select', 'searchoptions'=>array('value'=>'')),
				array('name'=>'cycle_id', 'label'=>'Cycle', 'query'=>'true',  'editable'=>true, 'DATA_TYPE'=>'int', 'type'=>'select', 'searchoptions'=>array('value'=>'')),
				array('name'=>'testcase_module_id', 'label'=>'Module', 'query'=>'true',  'editable'=>true, 'DATA_TYPE'=>'int', 'type'=>'select', 'searchoptions'=>array('value'=>'')),
				array('name'=>'testcase_testpoint_id', 'label'=>'Testpoint', 'query'=>'true',  'editable'=>false, 'DATA_TYPE'=>'int', 'type'=>'select', 'searchoptions'=>array('value'=>'')),
				array('name'=>'auto_level_id', 'label'=>'Auto Level', 'query'=>'true',  'editable'=>true, 'DATA_TYPE'=>'int', 'type'=>'select', 'searchoptions'=>array('value'=>'')),
				array('name'=>'result_type_id', 'label'=>'Result', 'query'=>'true',  'editable'=>true, 'DATA_TYPE'=>'int', 'type'=>'select', 'searchoptions'=>array('value'=>'')),
				array('name'=>'tester_id', 'label'=>'Tester', 'query'=>'true',  'editable'=>true, 'DATA_TYPE'=>'int', 'type'=>'select', 'searchoptions'=>array('value'=>'')),
				array('name'=>'defect_ids', 'label'=>'CR', 'query'=>'true',  'editable'=>true, 'DATA_TYPE'=>'text','type'=>'text', 'searchoptions'=>array('value'=>'')),
				array('name'=>'testcase_priority_id', 'label'=>'Priority', 'query'=>'true',  'editable'=>true, 'DATA_TYPE'=>'int', 'type'=>'checkbox', 'cols'=>6, 'queryoptions'=>array('value'=>'1,2,3'), 'searchoptions'=>array('value'=>'')),
				array('name'=>'codec_stream_priority', 'label'=>'Stream Priority', 'query'=>'true',  'editable'=>true, 'DATA_TYPE'=>'int', 'type'=>'checkbox', 'cols'=>6, 'searchoptions'=>array('value'=>''), 'queryoptions'=>array('advanced'=>true)),
				array('name'=>'codec_stream_name', 'label'=>'Stream', 'query'=>'true',  'editable'=>true, 'DATA_TYPE'=>'text', 'type'=>'text', 'queryoptions'=>array('advanced'=>true)),
				
			);
			foreach($cols as $row=>$search){
				if($search['name'] != 'defect_ids' && $search['name'] != 'code' && $search['name'] != 'stream_priority'){
					if (preg_match('/^(.+)_(ids?)$/i', $search['name'], $matches))
						$data = $this->getdata($matches[1]);
				}
				
				foreach($search as $key=>$val){
					if(!empty($data) && ($key == 'searchoptions')){
						$cols[$row]['searchoptions']['value'] = $data;
						unset($data);
					}
				}
				if($search['name'] == 'codec_stream_priority'){
					$res = $this->db->query("SELECT id, name FROM testcase_priority");
					while($info = $res->fetch())
						$priority[$info['id']] = $info['name'];
					$cols[$row]['searchoptions']['value'] = $priority;
				}
				if(!empty($search['query'])){
					if (!empty($search['queryoptions']['advanced']))
						$advanced[] = $cols[$row];
					else
						$query[] = $cols[$row];
				}
			}
			$this->renderView('other_cycle_case.phtml', array('query'=>$query, 'container'=>$params['container'], 'db'=>'xt', 'table'=>'zzvw_cycle_detail', 'buttonFlag'=>false, 'advanced'=>$advanced));	      
		}
	}
	private function getdata($table){
		if($table == 'tester'){
			$data = $this->userAdmin->getUserList(true);
		}else{
			$data['0'] = '';
			$sql = "SELECT id, name FROM $table";
			if($table == "cycle")
				$sql .= " WHERE cycle_status_id = 1";
			$res = $this->db->query($sql);
			while($info = $res->fetch()){
				$data[$info['id']] = $info['name'];
			}
		}
		return $data;
	}
	public function build_result(){
		$params = $this->tool->parseParams();
		if ($this->controller->getRequest()->isPost()){
            $res = $this->db->query("SELECT id, name FROM result_type");
			$build_result[0] = '';
		    while($info = $res->fetch()){
				$build_result[$info['id']] = $info['name'];
			}
			$cols = array(
				array('name'=>'build_result_id', 'label'=>'Build Result',  'editable'=>true, 'DATA_TYPE'=>'int', 'type'=>'select', 'deval'=>$params['build_result_id'], 'editoptions'=>array('value'=>$build_result)),
				);
			$this->renderView('new_element.phtml', array('cols'=>$cols), '/jqgrid');	                              
		}
		else{      
		}
	}
	
	public function set_build_result(){
		$params = $this->tool->parseParams();
		$params['element'] = json_decode($params['element']);
		$params['c_f'] = json_decode($params['c_f']);
// print_r($params['element']);
// print_r($params['c_f']);
		$element = '';
		$element = $this->caclIDs();
		$res = $this->db->query("SELECT id FROM cycle_detail WHERE id in (".implode(',', $element).")");
		while($row = $res->fetch())
			$this->db->update('cycle_detail', array('build_result_id'=>$params['build_result_id']), 'id='.$row['id']);
		if(count($params['element']) == 1){
			$res = $this->db->query("SELECT id, build_result_id FROM cycle_detail WHERE id=".$params['element'][0]);
			$data = $res->fetch();
			return json_encode($data);
		}
	}
	
	public function script(){
		$params = $this->tool->parseParams();
		$ret = '';
		$params['element'] = json_decode($params['element']);
		$params['c_f'] = json_decode($params['c_f']);
		$element = '';
// print_r($params['element']);
// print_r($params['c_f']);
		$element = $this->caclIDs();
        $rename = "cycle_detail".implode("_", $params['element']);//会导致name过长，怎么解决
		$rename .= '_'.(($params['script_type'] == 1) ? 'Auto' : 'AutoMan');
		$realFileName = SCRIPT_ROOT.'/'.$rename.'_'.rand();
		$download = array("rename"=>$rename, "filename"=>$realFileName, "remove"=>1);
		$sql = "SELECT * FROM zzvw_cycle_detail WHERE id in (".implode(",", $element).") AND auto_level_id=".$params['script_type'];
		$result = $this->db->query($sql);
		$str = '';
		while ($row = $result->fetch()){
			if(!empty($row["command"]))
				$str .= $row["testcase_id"] . " " . $row["command"] . "\n";
		}
		if ($str != ''){
			$handle = fopen($realFileName, 'wb');
			if ($handle){
				if (fwrite($handle, $str)){
					fclose($handle);
					$ret = json_encode($download);
				}
			}
		}
        return $ret;
	}
	
	public function getlatestresult(){
		$params = $this->tool->parseParams();
		if ($this->controller->getRequest()->isPost()){	
			$res = $this->db->query("SELECT code FROM testcase WHERE id=".$params['case_id']);
			$str = '';
			if($info = $res->fetch()){
				$str .= '<table style="width:600px" class="table">';
				$str .= '<tr class="tabletitle"><td colspan="6">'.$info['code'].' ( 5 Latest Test Result(s) ) :</td></tr>';
				$str .= '<tr class="tablecaption"><td style="width:5%">Result</td>'.
					'<td style="width:10%">Env</td><td style="width:10%">Codec Stream</td>'.
					'<td style="width:10%">CRID</td><td style="width:20%">CR Comment</td>'.
					'<td style="width:35%">Cycle</td>';
				$sql = 'SELECT result_type.name as result_type, defect_ids, comment, cycle.name as cycle, test_env.name as test_env, codec_stream.name as codec_stream FROM cycle_detail detail'.
				' LEFT JOIN result_type ON detail.result_type_id=result_type.id LEFT JOIN cycle ON detail.cycle_id=cycle.id'. 
				' LEFT JOIN test_env ON detail.test_env_id=test_env.id LEFT JOIN codec_stream ON detail.codec_stream_id=codec_stream.id'. 
				' WHERE detail.testcase_id='.$params['case_id'].' AND detail.id NOT LIKE '.$params['case_id'].
				' ORDER BY detail.finish_time DESC limit 0, 5';
				$res = $this->db->query($sql);
				$currentRow = 0;
				while($row = $res->fetch()){
					if($currentRow % 2)
						$class = 'odd';
					else
						$class = 'even';
					if(empty($row['defect_ids']))
						$row['defect_ids'] = 'null';
					if(empty($row['comment']))
						$row['comment'] = 'null';
					if(empty($row['cycle']))
						$row['cycle'] = 'null';
					if(empty($row['test_env']))
						$row['test_env'] = 'null';
					if(empty($row['codec_stream']))
						$row['codec_stream'] = 'null';
					if(!empty($row['result_type'])){
						$str .= '<tr class="'.$class.'">';
						$str .= '<td>'.$row['result_type'].'</td>';
						$str .= '<td>'.$row['test_env'].'</td>';
						$str .= '<td>'.$row['codec_stream'].'</td>';
						$str .= '<td>'.$row['defect_ids'].'</td>';
						$str .= '<td>'.$row['comment'].'</td>';
						$str .= '<td>'.$row['cycle'].'</td>';
						$str .= '</tr>';
					}
					$currentRow ++;
				}
				$str .= '</table>';
			}
			
			echo $str;
		}
		else{      
		}
	}
	
	public function getstreamaction(){
		$params = $this->tool->parseParams();
		if ($this->controller->getRequest()->isPost()){	
			$res = $this->db->query("SELECT detail.cycle_id as cycle_id, detail.codec_stream_id as codec_stream_id, codec_stream.name as codec_stream FROM cycle_detail detail".
				" LEFT JOIN codec_stream ON detail.codec_stream_id=codec_stream.id WHERE detail.id=".$params['element']);
			$str = '';
			if($info = $res->fetch()){
				if(!empty($info['codec_stream_id'])){
					$str .= '<table style="width:800px" class="table">';
					$str .= '<tr class="tabletitle"><td colspan="6">'.$info['codec_stream'].' ( Result(s) In The Cycle ) :</td></tr>';
					$str .= '<tr class="tablecaption"><td style="width:20%">Prj</td><td style="width:10%">Result</td><td style="width:15%">CRID</td>'.
						'<td style="width:40%">CR Comment</td><td style="width:15%">Release</td>';
					$sql = 'SELECT result_type.name as result_type, cycle.name as cycle, prj.name as prj, rel.name as rel, codec_stream.name as codec_stream,'.
						' detail.comment as comment, detail.defect_ids as CRID FROM testcase_last_result lastresult'.
						' LEFT JOIN result_type ON lastresult.result_type_id=result_type.id LEFT JOIN cycle_detail detail ON lastresult.cycle_detail_id=detail.id'.
						' LEFT JOIN prj ON lastresult.prj_id=prj.id LEFT JOIN codec_stream ON lastresult.codec_stream_id=codec_stream.id'.
						' LEFT JOIN rel ON lastresult.rel_id= rel.id LEFT JOIN cycle ON detail.cycle_id=cycle.id'.
						' WHERE lastresult.testcase_id in ('.$params["case_id"].') AND lastresult.codec_stream_id='.$info['codec_stream_id'].
						' ORDER BY lastresult.tested DESC limit 0, 5';
					$res = $this->db->query($sql);
					$currentRow = 0;
					$i = 0;
					while($row = $res->fetch()){
						if($currentRow % 2)
							$class = 'odd';
						else
							$class = 'even';
						if(empty($row['CRID']))
							$row['CRID'] = 'null';
						if(empty($row['comment']))
							$row['comment'] = 'null';
						if(empty($row['result_type']))
							$row['result_type'] = 'null';
						if(empty($row['rel']))
							$row['rel'] = 'null';
						if(empty($row['prj']))
							$row['prj'] = 'null';
						if(!empty($row['result_type'])){
							$i++;
							$str .= '<tr class="'.$class.'">';
							$str .= '<td>'.$row['prj'].'</td>';
							$str .= '<td>'.$row['result_type'].'</td>';
							$str .= '<td>'.$row['CRID'].'</td>';
							$str .= '<td>'.$row['comment'].'</td>';
							$str .= '<td>'.$row['rel'].'</td>';
							//$str .= '<td>'.$row['cycle'].'</td>';
							$str .= '</tr>';
						}
						$currentRow ++;
					}
					$str .= '</table>';
				}
				else{
					$str .= '<table style="width:800px" class="table">';
					$str .= '<tr class="tabletitle"><td colspan="5">'.$info['codec_stream_id'].' ( Result(s) In The Cycle ) :</td></tr>';
					$str .= '<tr class="tablecaption"><td style="width:15%">Trick Mode</td><td style="width:10%">Env</td>'.
							'<td style="width:5%">Result</td><td style="width:10%">CRID</td>'.
							'<td style="width:30%">CR Comment</td></table>';//<td style="width:30%">Cycle</td>';
				}
			}
			echo $str;
		}
		else{      
		}
	}
	
	public function getcrossresult(){
		$params = $this->tool->parseParams();
		if ($this->controller->getRequest()->isPost()){	
			$res = $this->db->query("SELECT code FROM testcase WHERE id=".$params['case_id']);
			$str = '';
			if($info = $res->fetch()){
				$str .= '<table style="width:800px" class="table">';
				$str .= '<tr class="tabletitle"><td colspan="7">'.$info['code'].' ( Cross Project Test Result(s) ) :</td></tr>';
				$str .= '<tr class="tablecaption"><td style="width:5%">Result</td><td style="width:15%">Project</td>'.
					'<td style="width:15%">Release</td><td style="width:15%">Codec Stream</td><td style="width:25%">Cycle</td>'.
					'<td style="width:10%">CRID</td><td style="width:15%">CR Comment</td>';
				$sql = 'SELECT result_type.name as result_type, cycle.name as cycle, prj.name as prj, rel.name as rel, codec_stream.name as codec_stream,'.
				' cycle_detail.comment as comment, cycle_detail.defect_ids as CRID FROM testcase_last_result lastresult'.
				' LEFT JOIN result_type ON lastresult.result_type_id=result_type.id LEFT JOIN cycle_detail ON cycle_detail_id=cycle_detail.id LEFT JOIN prj ON lastresult.prj_id=prj.id'. 
				' LEFT JOIN codec_stream ON lastresult.codec_stream_id=codec_stream.id'.
				' LEFT JOIN rel ON lastresult.rel_id= rel.id LEFT JOIN cycle ON cycle_detail.cycle_id=cycle.id WHERE lastresult.testcase_id in ('.$params['case_id'].
				') ORDER BY lastresult.tested DESC limit 0, 5';
				$res = $this->db->query($sql);
				$currentRow = 0;
				while($row = $res->fetch()){
					if($currentRow % 2)
						$class = 'odd';
					else
						$class = 'even';
					if(empty($row['prj']))
						$row['prj'] = 'null';
					if(empty($row['rel']))
						$row['rel'] = 'null';
					if(empty($row['cycle']))
						$row['cycle'] = 'null';
					if(empty($row['codec_stream']))
						$row['codec_stream'] = 'null';
					if(empty($row['comment']))
						$row['comment'] = 'null';
					if(empty($row['CRID']))
						$row['CRID'] = 'null';
					// if(!empty($row['result_type'])){
						$str .= '<tr class="'.$class.'">';
						$str .= '<td>'.$row['result_type'].'</td>';
						$str .= '<td>'.$row['prj'].'</td>';
						$str .= '<td>'.$row['rel'].'</td>';
						$str .= '<td>'.$row['codec_stream'].'</td>';
						$str .= '<td>'.$row['cycle'].'</td>';
						$str .= '<td>'.$row['comment'].'</td>';
						$str .= '<td>'.$row['CRID'].'</td>';
						$str .= '</tr>';
					// }
					$currentRow ++;
				}
				$str .= '</table>';
			}
			
			echo $str;
		}
		else{      
		}
	}
	
	public function getlogfile(){
		$params = $this->tool->parseParams();
		if ($this->controller->getRequest()->isPost()){	
			$res = $this->db->query("SELECT testcase.code as code, cycle.name as cycle, detail.cycle_id as cycle_id FROM cycle_detail detail".
			" LEFT JOIN testcase ON testcase.id=detail.testcase_id".
			" LEFT JOIN cycle ON cycle.id=detail.cycle_id".
			" WHERE detail.id=".$params['element']);
			$info = $res->fetch();
			$path = LOG_ROOT."/".$info['cycle']."_".$info['cycle_id']."/".$info['code']."_".$params['element'];
			$str = '';
			$content = array();
			if(is_dir($path)){
				if($dir = opendir($path)){
					$str .= "<div id='logfiles_{$params['element']}' style='width:800px;font-size:12px'><ul>";
					$i = 1;
					while(($file = readdir($dir)) != false){
						if ($file != "." && $file != "..") {
							$filename = $path."/".$file;
							if ($handle = fopen($filename, 'rb')){
								if($ct = fread($handle, filesize($filename))){
									$str .= "<li><a href='#logfiles_{$params['element']} #aaa{$i}'>{$file}</a></li>";
									$content['aaa'.$i] = $ct;
									$i++;
								}
							}	
						}
					}
					$str .= '</ul>';
				}
			}
			if(empty($content))
				$str .= "<div id='nolog' style='width:750px;height:100px; overflow-y:scroll; border:1px solid;font-size:16px'><fieldset>No logfile here !</fieldset></div>";
			else{
				foreach($content as $key=>$val){
					$str .= "<div id='{$key}' style='width:750px;height:100px; overflow-y:scroll; border:1px solid;font-size:15px'><fieldset><pre>{$val}</pre></fieldset></div>";
				}
			}
			$str .= '</div>';
			return $str;
		}
		else{
		}
	}
	
	public function uploadlogfile(){
		$params = $this->tool->parseParams();
		if ($this->controller->getRequest()->isPost()){	
			$res = $this->db->query("SELECT cycle.id as cycle_id, cycle.name as cycle, testcase.code as code FROM cycle_detail detail".
			" LEFT JOIN cycle ON detail.cycle_id=cycle.id LEFT JOIN testcase ON detail.testcase_id=testcase.id WHERE detail.id=".$params['id']);
			$info = $res->fetch();
			$logFile = $params['cellName'];//选择文件的那个input的id号
			$strLogFilePath = LOG_ROOT;
			if (isset($_FILES[$logFile])){
				$path = LOG_ROOT."/".$info['cycle'].'_'.$info['cycle_id']."/".$info['code'].'_'.$params['id']."/".str_replace(' ', '', basename($_FILES[$logFile]['name']));
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
					if (PHP_OS == "Linux")//"LINUX"
						system('chmod 777 '. $path);
					return "upload successully";//怎么给出提示
				}
			}
		}
		else{
		}
	}
	
	public function getList(){ // 有很大的优化空间，尤其是两次查询，第一次仅仅得到总记录数，第二次加入limit条件继续查，有没有可能改成只查一次？但似乎第二次查询的时间很短
        $this->config();
        $ret = array();
        $params = $this->tool->parseParams('getList');	
        $rownum = $params['limit']['rows'];
		if ($rownum == 0)
			$rownum = 'ALL';
        $cookie = array('type'=>'rowNum', 'name'=>$this->get('db').'_'.$this->get('table'), 'content'=>json_encode(array('rowNum'=>$rownum)));
        $this->userAdmin->saveCookie($cookie);	
		
		$paramsValues = $this->tool->parseParams();
		if(isset($paramsValues['tag_id'])){
			if(!empty($paramsValues['tag_id'])){
				$res = $this->db->query("SELECT element_id FROM tag WHERE id=".$paramsValues['tag_id']);
				$codec_stream_list = $res->fetch();
			}
		}       	
		$sqls = $this->calcSql($params, false);
		$mainFields = $sqls['main']['fields'];
		$sqls['main']['fields'] = "`{$this->get('table')}`.`id`";
		$origin_sql = $sqls['where'];
		//codec_stream != 0
		$str1 = " AND (codec_stream_id != 0) GROUP BY codec_stream_id, test_env_id";
		if(isset($codec_stream_list)){
			if(!empty($codec_stream_list))
				$str1 = " AND (codec_stream_id in (".$codec_stream_list['element_id'].")) GROUP BY codec_stream_id, test_env_id";
		}
		//codec_stream == 0
		$str2 = " AND (codec_stream_id = 0)";
		$sqls['where'] .= $str1;
        $sql = $this->getSql($sqls, false);//true);
		$sqls['main']['fields'] = $mainFields;
		$sqls['where'] = $origin_sql;
		$res = $this->db->query($sql);	
		// num of codec_stream != 0
		$ret['records'] = $res->rowCount();
		$res->closeCursor();
		

		//没有codec_stream_id时
		$sqls['where'] .= $str2;
		$sql = $this->getSql($sqls, false);//true);
		$sqls['where'] = $origin_sql;
		$res = $this->db->query($sql);	
		// num of codec_stream == 0, 叠加
		$ret['records'] += $res->rowCount();
		
		$res->closeCursor();
        $ret['page'] = $params['page'];
        if ($params['limit']['rows'] > 0)
            $ret['pages'] = ceil($ret['records'] / $params['limit']['rows']);
		else
			$ret['pages'] = 1;		
		
		$sqls['limit'] = $this->tool->getLimitSql($params['limit']);
		$sqls['where'] .= $str1;
		$sql = $this->getSql($sqls);
		$sqls['where'] = $origin_sql;
		$res = $this->db->query($sql);
        $rows = array();
		$sqlKeys = $this->tool->getSqlKeys();
        while($row = $res->fetch()){
			$row['c_f'] = 1;
			if (!empty($sqlKeys))
				$row = $this->hilightKeys($row, $sqlKeys);
			$row = $this->getMoreInfoForRow($row);
			if(isset($row['code'])){
				$row['code'] = '';
			}
            $rows[] = $row;
        }
		$res->closeCursor();
		
		//没有codec_stream_id时
		$sqls['where'] .= $str2;
		$sql = $this->getSql($sqls);
		$res = $this->db->query($sql);
		$sqlKeys = $this->tool->getSqlKeys();
		while($row = $res->fetch()){
			$row['c_f'] = 0;
			if (!empty($sqlKeys))
				$row = $this->hilightKeys($row, $sqlKeys);
			$row = $this->getMoreInfoForRow($row);
			$rows[] = $row;
		}
		
		$res->closeCursor();
        $ret['rows'] = $rows;
        $ret['sql'] = $sql;
        return $ret;
    }

	public function genPlayList(){
		$params = $this->tool->parseParams();
		if($this->controller->getRequest()->isPost()){
			//传入c_f
			$playlist = new cycle_playlist($this->get('db'));
			if($params['flag'] != 2)//1,3
				$download = $playlist->genCmdFileByDetailid($params);
			if($params['flag'] == 2)
				$download = $playlist->genCmd2File($params);
			return $download;
		}
		else{
		}
	}
}
