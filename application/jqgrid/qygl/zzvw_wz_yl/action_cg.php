<?php
require_once(APPLICATION_PATH.'/jqgrid/qygl/action_yw.php');

class qygl_zzvw_wz_yl_action_cg extends action_yw{
	protected function getViewParams($params){
		$view_params = parent::getViewParams($params);
		//获取供应商信息
		$gys = $this->getGYS($params['id']); //供应商
		$zjzh = $this->getZJZH();	//资金账号
		$cyr = $this->getCYR();		//承运人
		$zxr = $this->getZXR();		//装卸人
		$jbr = $this->getJBR();		//经办人
		$zl = $this->getZL();
		$ck = $this->getCK();
		$view_params['fh_fl'] = $this->getFH_FL(); //发货方式
		$view_params['gys'] = $gys;
		$view_params['zjzh'] = $zjzh;
		$view_params['cyr'] = $cyr;
		$view_params['zxr'] = $zxr;
		$view_params['jbr'] = $jbr;
		$view_params['zl'] = $zl;
		$view_params['ck'] = $ck;
		
		$wz = $this->getWZ($params['id']);
		
		$view_params['wz'] = $wz;
		
		$view_params['from'] = 'wz';
		
		$view_params['view_file'] = 'wz_cg.phtml';
		$view_params['view_file_dir'] = '/jqgrid/qygl';
// print_r($view_params);		
		return $view_params;
	}
	
	protected function handlePost(){
		$yw_dingdan_id = $yw_yunru_id = $yw_xiezai_id = 0;
		$params = $this->params;
// print_r($params);		
		list($yw_id, $yw_dingdan_id) = $this->cg($params['cg']); //下采购单
		
		if(!empty($params['cg_zf']['pay'])){
			$params['cg_zf']['hb_id'] = $params['cg']['hb_id'];
			$params['cg_zf']['related_yw_id'] = $yw_id;
			$params['cg_zf']['zj_cause_id'] = ZJ_CAUSE_CG;
			$this->zf($params['cg_zf']);	// 支付采购款
		}

		if(!empty($params['has_yunshu'])){
// print_r($params['yunshu'])		;
			$params['yunshu']['yw_dingdan_id'] = $yw_dingdan_id;
			list($yw_id, $yw_yunru_id) = $this->yunru($params['yunshu']); // 运输
			if(!empty($params['yunshu_zf']['pay'])){
				$params['yunshu_zf']['hb_id'] = $params['yunshu']['hb_id'];
				$params['yunshu_zf']['related_yw_id'] = $yw_id;
				$params['yunshu_zf']['zj_cause_id'] = ZJ_CAUSE_YUNSHU;
				$this->zf($params['yunshu_zf']); //付运输款
			}
		}
		if(!empty($params['has_zhuangxie'])){
			$params['zhuangxie']['yw_dingdan_id'] = $yw_dingdan_id;
			$params['zhuangxie']['yw_yunru_id'] = $yw_yunru_id;
			list($yw_id, $yw_xiezai_id) = $this->xiezai($params['zhuangxie']); // 卸载
			if(!empty($params['zhuangxie_zf']['pay'])){
				$params['zhuangxie_zf']['hb_id'] = $params['yunshu']['hb_id'];
				$params['zhuangxie_zf']['related_yw_id'] = $yw_id;
				$params['zhuangxie_zf']['zj_cause_id'] = ZJ_CAUSE_ZHUANGXIE;
				$this->zf($params['zhuangxie_zf']); //付装卸款
			}
		}
		if(!empty($params['has_ruku'])){
			$params['ruku']['wz_id'] = $params['cg']['wz_id'];
			$params['ruku']['hb_id'] = $params['cg']['hb_id'];
			$params['ruku']['gx_id'] = GX_FL_CG;
			// $params['ruku']['zl_id'] = $params['ruku']
			$params['ruku']['yw_dingdan_id'] = $yw_dingdan_id;
			$params['ruku']['yw_yunru_id'] = $yw_yunru_id;
			$params['ruku']['yw_xiezai_id'] = $yw_xiezai_id;
			$this->ruku($params['ruku']); // 卸载
		}
	}
}

?>
