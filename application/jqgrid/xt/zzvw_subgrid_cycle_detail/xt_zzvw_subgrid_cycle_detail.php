<?php

require_once(str_replace("\\", "/", APPLICATION_PATH.'/jqgrid/xt/zzvw_cycle_detail/xt_zzvw_cycle_detail.php'));
require_once('kf_editstatus.php');

class xt_zzvw_subgrid_cycle_detail extends xt_zzvw_cycle_detail{
    public function init($controller, array $options = null){
        parent::init($controller, $options);
        $this->options['db'] = 'xt';
        $this->options['table'] = 'zzvw_subgrid_cycle_detail';
        $this->options['columns'] = array(
			'id',
			'c_f'=>array('label'=>'c_f', 'hidden'=>true, 'excluded'=>true, 'edittype'=>'text', 'editable'=>true, 'hidedlg'=>true),
			'testcase_id'=>array('editable'=>false, 'formatter'=>'text', 'hidden'=>true),
			'code'=>array('label'=>'Testcase', 'editable'=>false, 'query'=>true, 'hidedlg'=>true, 'unique'=>false),
			'codec_stream_id'=>array('label'=>'Codec Stream', 'editable'=>false, 'hidden'=>true, 'hidedlg'=>true),
			'summary'=>array('label'=>'Summary', 'editable'=>false),
			'testcase_module_id'=>array('label'=>'Module','query'=>true, 'editable'=>false, 'hidden'=>true),
			'testcase_testpoint_id'=>array('label'=>'Testpoint','query'=>true, 'editable'=>false, 'hidden'=>true),
			'testcase_priority_id'=>array('label'=>'Priority', 'query'=>true, 'editable'=>false, 'hidden'=>true),
			'ver'=>array('label'=>'Ver', 'editable'=>false),
			'test_env_id'=>array('label'=>'Test Env'),
			'result_type_id'=>array('label'=>'Result', 'query'=>true), 
			'tester_id'=>array('label'=>'Testor', 'query'=>true),
			'auto_level_id'=>array('label'=>'Auto Level', 'query'=>true, 'editable'=>false),
			'cycle_id'=>array('hidden'=>true, 'editable'=>false),
			//'*'=>array('hidden'=>true, 'editable'=>false, 'view'=>false),
			'duration_minutes'=>array('hidden'=>true),
			'finish_time'=>array('label'=>'Finish Time', 'editable'=>false),
			//'steps'=>array('label'=>'Steps', 'view'=>true, 'hidden'=>true),
			'precondition'=>array('label'=>'Precondition', 'view'=>true, 'hidden'=>true, 'editable'=>false),
			'steps'=>array('label'=>'Steps', 'view'=>true, 'hidden'=>true),
			'deadline'=>array('hidden'=>true, 'required'=>false),
			'defect_ids'=>array('label'=>'CRID', 'query'=>true, 'formatter'=>'text', 'required'=>false),
			'comment'=>array('label'=>'CR Comment', 'editable'=>false),
			'issue_comment'=>array('hidden'=>true, 'editable'=>false),
			//'objective',
			'finish_time'=>array('label'=>'Finish Time', 'editable'=>false),
		);
//        $options['gridOptions']['subGrid'] = true;	
		$this->options['gridOptions']['label'] = 'cycle cases';
		$this->options['gridOptions']['subGrid'] = false;
        $this->options['ver'] = '1.0';
    } 
	public function getButtons(){
		$buttons = parent::getButtons();
		if(isset($buttons['add_del_stream_actions']))
			unset($buttons['add_del_stream_actions']);
		if(isset($buttons['add_del_env']))
			unset($buttons['add_del_env']);
		if(isset($buttons['script']))
			unset($buttons['script']);
		if(isset($buttons['playlist']))
			unset($buttons['playlist']);
		return $buttons;
	}

	public function getList(){ // 有很大的优化空间，尤其是两次查询，第一次仅仅得到总记录数，第二次加入limit条件继续查，有没有可能改成只查一次？但似乎第二次查询的时间很短
        $this->config();
        $ret = array();
        $params = $this->tool->parseParams('getList');		
		$flag = 0;//subgrid flag
		$detail = '';//subgrid detail element
		foreach($params['searchConditions']['and'] as $k=>$v){
			if(isset($v['field']) && ($v['field'] == 'id')){
				$res = $this->db->query("SELECT id, cycle_id, codec_stream_id, test_env_id FROM cycle_detail WHERE id=".$v['value']);
				if($detail = $res->fetch()){
					$flag = 1;//subgrid, 前端判断是否有codec_stream_id
					unset($params['searchConditions']['and']);
				}
			}
		}
        $rownum = $params['limit']['rows'];
		if ($rownum == 0)
			$rownum = 'ALL';
        $cookie = array('type'=>'rowNum', 'name'=>$this->get('db').'_'.$this->get('table'), 'content'=>json_encode(array('rowNum'=>$rownum)));
        $this->userAdmin->saveCookie($cookie);   	
		$sqls = $this->calcSql($params, false);
		$mainFields = $sqls['main']['fields'];
		$sqls['main']['fields'] = "`{$this->get('table')}`.`id`";
		$origin_sql = $sqls['where'];
		//falg=0时，group by codec_stream_id; flag=1时，显示codec_stream_id下的trickmode cases
		$sqls['where'] .= " AND (cycle_id=".$detail['cycle_id'].")".
			" AND (codec_stream_id=".$detail['codec_stream_id'].")".
			" AND (test_env_id=".$detail['test_env_id'].")";
        $sql = $this->getSql($sqls, false);//true);
		$sqls['main']['fields'] = $mainFields;
		$sqls['where'] = $origin_sql;
		$res = $this->db->query($sql);	
		// num of codec_stream != 0
		$ret['records'] = $res->rowCount();		
		$res->closeCursor();
        $ret['page'] = $params['page'];
        if ($params['limit']['rows'] > 0)
            $ret['pages'] = ceil($ret['records'] / $params['limit']['rows']);
		else
			$ret['pages'] = 1;		
		
		$sqls['limit'] = $this->tool->getLimitSql($params['limit']);
		$sqls['where'] .= " AND (cycle_id=".$detail['cycle_id'].")".
			" AND (codec_stream_id=".$detail['codec_stream_id'].")".
			" AND (test_env_id=".$detail['test_env_id'].")";	
		$sql = $this->getSql($sqls);
		$res = $this->db->query($sql);
        $rows = array();
		$sqlKeys = $this->tool->getSqlKeys();
        while($row = $res->fetch()){
			$row['c_f'] = 2;
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


}
