<?php 
require_once('action_jqgrid.php');
require_once('const_def_qygl.php');

class qygl_zzvw_yw_dingdan_action_change_status extends action_jqgrid{
	protected function handlePost(){
		$dingdan_status_id = DINGDAN_STATUS_ZHIXING;
		switch($this->params['status']){
			case 'qx':
				$dingdan_status_id = DINGDAN_STATUS_QUXIAO;
				break;
			case 'jieshu':
				$dingdan_status_id = DINGDAN_STATUS_JIESHU;
				break;
		}
		$this->tool->update('yw_dingdan', array('dingdan_status_id'=>$dingdan_status_id), "id={$this->params['id']}", 'qygl');
		return;
	}
	protected function getViewEditButtons($params){
		$view_buttons = parent::getViewEditButtons($params);
	
		if(!empty($params['id'])){
			$style = 'position:relative;float:left';
			$newBtns = array(
				'gengzong'=>array('label'=>'跟踪', 'style'=>$style),
				'scqk'=>array('label'=>'生产情况', 'style'=>$style),
				'kcqk'=>array('label'=>'库存情况', 'style'=>$style),
				'qx'=>array('label'=>'取消', 'style'=>$style),
				'jieshu'=>array('label'=>'结束', 'style'=>$style),
			);
			$res = $this->tool->query("SELECT * FROM zzvw_yw_dingdan WHERE id={$params['id']}");
			$row = $res->fetch();
			if(empty($row['completed'])){
				$view_buttons['gengzong'] = $newBtns['gengzong'];
				$view_buttons['qx'] = $newBtns['qx'];
				$view_buttons['jieshu'] = $newBtns['jieshu'];
			}
			if($row['yw_fl_id'] == YW_FL_JIESHOUDINGDAN){ //销售
				$view_buttons['scqk'] = $newBtns['scqk']; //查看生产情况
				$view_buttons['kcqk'] = $newBtns['kcqk']; //查看生产情况
			}
		}
		return $view_buttons;
	}
}

?>