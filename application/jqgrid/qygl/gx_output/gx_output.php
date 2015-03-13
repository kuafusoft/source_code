<?php
require_once('table_desc.php');
require_once('const_def_qygl.php');
//工序管理
class qygl_gx_output extends table_desc{
	protected function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		
        $this->options['list'] = array(
            'id'=>array('editable'=>false, 'hidden'=>true),
			'gx_id'=>array('label'=>'工序'),
			'wz_id'=>array('label'=>'产品'),
			'calc_method_id'=>array('label'=>'计量方法'),
			'amount'=>array('label'=>'数量', 'post'=>'?'),
        );
		
		$this->options['edit'] = array('wz_id', 'calc_method_id', 'amount');
	}

	protected function handleFillOptionCondition(){
		$this->fillOptionConditions['wz_id'] = array(array('field'=>'wz_fl_id', 'op'=>'IN', 'value'=>array(WZ_FL_CHANPIN, WZ_FL_YUANLIAO, WZ_FL_NENGYUAN)));
		$this->allFields['wz_id'] = true;
	}
	
	public function fillOptions(&$columnDef, $db, $table){
		parent::fillOptions($columnDef, $db, $table);
		
		switch($columnDef['name']){
			case 'wz_id':
// print_r($columnDef['editoptions']['value']);
				foreach($columnDef['editoptions']['value'] as $k=>&$item){
					if($k == 0)
						continue;
					$res = $this->tool->query("SELECT name as unit from unit where id={$item['unit_id']}");
					$t = $res->fetch();
					$item['unit'] = $t['unit'];
				}
				foreach($columnDef['addoptions']['value'] as $k=>&$item){
					if($k == 0)
						continue;
					$res = $this->tool->query("SELECT name as unit from unit where id={$item['unit_id']}");
					$t = $res->fetch();
					$item['unit'] = $t['unit'];
				}
				break;
			default:
				break;
		}
	}
}
