<?php
require_once('action_jqgrid.php');
require_once(APPLICATION_PATH."/jqgrid/qygl/hb_tool.php");
require_once("const_def_qygl.php");
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
defined('YW_FL_YIKU') || define('YW_FL_YIKU', 11); //移库
defined('YW_FL_JIESHOUDINGDAN') || define('YW_FL_JIESHOUDINGDAN', 12); //接收订单
defined('YW_FL_CHUKU') || define('YW_FL_CHUKU', 13); //出库
defined('YW_FL_YUNCHU') || define('YW_FL_YUNCHU', 14); //运出
defined('YW_FL_PANKU') || define('YW_FL_PANKU', 15); //盘库
defined('YW_FL_TX') || define('YW_FL_TX', 16); //贴息
*/
class qygl_hb_action_get_helper_by_yw_fl extends action_jqgrid{
	protected function handlePost(){
		$tool_hb = new hb_tool($this->tool);
		$ret = array();
		switch($this->params['value']){
			case YW_FL_CG:
			case YW_FL_ZJOUT:
			case YW_FL_TUIHUO:
				$ret = $tool_hb->getSTGYS();
// print_r($ret);
				break;
			case YW_FL_JIESHOUDINGDAN:
			case YW_FL_ZJIN:
			case YW_FL_JIESHOUTUIHUO:
				$ret = $tool_hb->getKH();
				break;
			case YW_FL_YUNCHU:
			case YW_FL_YUNRU:
				$ret = $tool_hb->getZXR();
				break;
			case YW_FL_ZHUANGZAI:
			case YW_FL_XIEZAI:
				$ret = $tool_hb->getZXR();
				break;
			case YW_FL_SCDJ:
			case YW_FL_PANKU:
			case YW_FL_RUKU:
			case YW_FL_CHUKU:
			case YW_FL_YIKU:
			case YW_FL_ZHUANZHANG:
				$ret = $tool_hb->getYG(0, false);
				break;
			case YW_FL_TX: //贴息
			case YW_FL_JIERU:	//借入款项
			case YW_FL_HUANKUAN:	//还款
			case YW_FL_JIECHU:	//借出款项
				$ret = $tool_hb->getJRXGR();
				break;
			
		}
		return $ret;
	}
}
