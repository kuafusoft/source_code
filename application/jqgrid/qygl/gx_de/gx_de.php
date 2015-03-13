<?php
require_once('table_desc.php');
require_once('const_def_qygl.php');
require_once(APPLICATION_PATH."/jqgrid/qygl/wz_tool.php");

//工序定额管理
class qygl_gx_de extends table_desc{
	protected function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		
        $this->options['list'] = array(
            'id'=>array('editable'=>false, 'hidden'=>true),
			'gx_id'=>array('label'=>'工序', 'editrules'=>array('required'=>true)),
			'wz_id'=>array('label'=>'产品', 'editrules'=>array('required'=>true)),
			'rengong'=>array('label'=>'人工', 'post'=>'工作班'),
			'changdi_area'=>array('label'=>'场地', 'post'=>'平方米'),
			'gx_de_input'=>array('label'=>'输入', 'formatter'=>'multi_row_edit','legend'=>'输入', 'data_source_db'=>'qygl', 'data_source_table'=>'gx_de_input'),
			'gx_de_output'=>array('label'=>'输出', 'formatter'=>'multi_row_edit','legend'=>'输出', 'data_source_db'=>'qygl', 'data_source_table'=>'gx_de_output'),
        );
		
		$this->options['edit'] = array(
			'gx_id', 'wz_id', 
			'rengong', 'changdi_area',
			'gx_de_input',
			'gx_de_output',
			);
	}
	
	public function getMoreInfoForRow($row){
		// $res = $this->tool->query("select * from gx WHERE id={$row['gx_id']}");
		// $t = $res->fetch();
		// $row['gx_fl_id'] = $t['gx_fl_id'];
		
		$sql = "SELECT * from gx_de_input where gx_de_id={$row['id']}";
		$res = $this->tool->query($sql);
		$row['gx_de_input'] = $res->fetchAll();
		
		$sql = "SELECT * from gx_de_output where gx_de_id={$row['id']}";
		$res = $this->tool->query($sql);
		$row['gx_de_output'] = $res->fetchAll();
		return $row;
	}
	
	protected function handleFillOptionCondition(){
		$gx = array();
		$res = $this->tool->query("SELECT gx.id from gx where gx.gx_fl_id!=".GX_FL_FSCX); //排除非生产性工序
		while($row = $res->fetch())
			$gx[] = $row['id'];
		
		$this->fillOptionConditions['gx_id'] = array(array('field'=>'id', 'op'=>'IN', 'value'=>$gx));
		$this->fillOptionConditions['wz_id'] = array(array('field'=>'wz_fl_id', 'op'=>'IN', 'value'=>array(WZ_FL_CHANPIN)));
		
		$this->allFields['gx_id'] = $this->allFields['wz_id'] = true;
	}
	
	public function fillOptions(&$columnDef, $db, $table){
		parent::fillOptions($columnDef, $db, $table);
		
		switch($columnDef['name']){
			case 'wz_id':
// print_r($columnDef['editoptions']['value']);
				foreach($columnDef['editoptions']['value'] as $k=>&$item){
					if($k == 0)
						continue;
					$this->getMoreInfoForOption($item);
				}
				foreach($columnDef['addoptions']['value'] as $k=>&$item){
					if($k == 0)
						continue;
					$this->getMoreInfoForOption($item);
				}
				break;
			default:
				break;
		}
	}
	
	private function getMoreInfoForOption(&$item){
		$res = $this->tool->query("SELECT name as unit from unit where id={$item['unit_id']}");
		$t = $res->fetch();
		$item['unit'] = $t['unit'];
		if(!empty($item['zuhe'])){ //是组合型产品
			$zuhe = array();
			$res = $this->tool->query("SELECT * FROM wz_cp_zuhe WHERE wz_id={$item['id']}");
			while($row = $res->fetch()){
				$zuhe[] = array('input_wz_id'=>$row['input_wz_id'], 'amount' => $row['amount']);
			}
			if(!empty($zuhe))
				$item['zuhe'] = json_encode($zuhe);
		}
	}
}
