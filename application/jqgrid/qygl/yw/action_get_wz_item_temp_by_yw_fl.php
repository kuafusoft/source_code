<?php 
require_once('action_jqgrid.php');
require_once('const_def_qygl.php');
require_once(APPLICATION_PATH."/jqgrid/qygl/yw_tool.php");
require_once(APPLICATION_PATH."/jqgrid/qygl/hb_tool.php");
require_once(APPLICATION_PATH."/jqgrid/qygl/wz_tool.php");
/*
defined('YW_FL_CG') || define('YW_FL_CG', 1); //下采购单
defined('YW_FL_YUNRU') || define('YW_FL_YUNRU', 2); //运入
defined('YW_FL_XIEZAI') || define('YW_FL_XIEZAI', 3); //卸载
defined('YW_FL_ZHUANGZAI') || define('YW_FL_ZHUANGZAI', 17); //装载
defined('YW_FL_RUKU') || define('YW_FL_RUKU', 4); //入库
defined('YW_FL_SCDJ') || define('YW_FL_SCDJ', 5);//生产登记
defined('YW_FL_ZJOUT') || define('YW_FL_ZJOUT', 6); //资金转出
defined('YW_FL_ZJIN') || define('YW_FL_ZJIN', 7); //资金转入
defined('YW_FL_ZHUANZHANG') || define('YW_FL_ZHUANZHANG', 8); //转账
defined('YW_FL_TUIHUO') || define('YW_FL_TUIHUO', 9); //退货
defined('YW_FL_JIESHOUTUIHUO') || define('YW_FL_JIESHOUTUIHUO', 10); //接收退货
defined('YW_FL_YIKU') || define('YW_FL_YIHU', 11); //移库
defined('YW_FL_JIESHOUDINGDAN') || define('YW_FL_JIESHOUDINGDAN', 12); //接收订单
defined('YW_FL_CHUKU') || define('YW_FL_CHUKU', 13); //出库
defined('YW_FL_YUNCHU') || define('YW_FL_YUNCHU', 14); //运出
defined('YW_FL_PANKU') || define('YW_FL_PANKU', 15); //盘库
defined('YW_FL_TX') || define('YW_FL_TX', 16); //贴息
*/
class qygl_yw_action_get_wz_item_temp_by_yw_fl extends action_jqgrid{
	protected function singleYWHTML($yw_fl_id){
		$html = '';
		switch($yw_fl_id){
			case YW_FL_YUNRU:
			case YW_FL_YUNCHU:
			case YW_FL_CG:
			case YW_FL_JIESHOUDINGDAN:
			default:
				$html = $this->genView('single_yw.phtml', array('tool'=>$this->tool, 'yw_fl_id'=>$yw_fl_id));
				break;
		}
		return $html;
	}
	
	protected function handlePost(){
		$yw_tool = new yw_tool($this->tool);
		$ret = array('html'=>'');
		if(!empty($this->params['value'])){
			$ret['html'] = $yw_tool->getWzItemTemplate($this->params['value'], $this->params['id']);
		}
		return json_encode($ret);
	}
}

?>