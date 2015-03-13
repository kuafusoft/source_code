<?php
require_once('table_desc.php');
require_once('const_def_qygl.php');
//工序管理
class qygl_gx_de_output extends table_desc{
	protected function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		
        $this->options['list'] = array(
            'id'=>array('editable'=>false, 'hidden'=>true),
			'gx_de_id'=>array('label'=>'工序'),
			'wz_id'=>array('label'=>'产品'),
			'yl_id'=>array('label'=>'原料', 'data_source_table'=>'wz'),
			'amount'=>array('label'=>'数量', 'post'=>'?'),
        );
		
		$this->options['edit'] = array('wz_id', 'yl_id', 'amount');
	}
	
	protected function handleFillOptionCondition(){
		$this->fillOptionConditions['yl_id'] = array(array('field'=>'wz_fl_id', 'op'=>'=', 'value'=>WZ_FL_YUANLIAO));
		$this->fillOptionConditions['wz_id'] = array(array('field'=>'wz_fl_id', 'op'=>'IN', 'value'=>array(WZ_FL_CHANPIN, WZ_FL_YUANLIAO)));
		$this->allFields['wz_id'] = $this->allFields['yl_id'] = true;
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
