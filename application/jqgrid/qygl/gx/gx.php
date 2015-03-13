<?php
require_once('table_desc.php');
require_once('const_def_qygl.php');
//工序管理
class qygl_gx extends table_desc{
	protected function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		
        $this->options['list'] = array(
            'id'=>array('editable'=>false, 'hidden'=>true),
			'gx_fl_id'=>array('label'=>'工序类型'),
			'name'=>array('label'=>'工序名称'),
			'pre_gx_ids'=>array('label'=>'前置工序', 'data_source_table'=>'gx'),
			'wz_id'=>array('label'=>'产出品材料'),
			'defect_id'=>array('label'=>'主输入的缺陷'),
			'has_shell'=>array('label'=>'外壳', 'formatter'=>'select', 
				'edittype'=>'select', 'editoptions'=>array('value'=>array(1=>'有外壳', 2=>'无外壳')),
				'editrules'=>array('required'=>true)),
			'need_mj'=>array('label'=>'需要模具', 'formatter'=>'select', 
				'edittype'=>'select', 'editoptions'=>array('value'=>array(1=>'需要', 2=>'不需要')),
				'editrules'=>array('required'=>true)),
			'work_type_id'=>array('label'=>'工种'),
			'gx_input'=>array('label'=>'输入', 'formatter'=>'multi_row_edit','legend'=>'输入(不包括主产品)', 'data_source_db'=>'qygl', 'data_source_table'=>'gx_input'),
			'gx_output'=>array('label'=>'输出', 'formatter'=>'multi_row_edit','legend'=>'输出（不包括主产品）', 'data_source_db'=>'qygl', 'data_source_table'=>'gx_output'),
			'note'=>array('label'=>'备注')
        );
		
		$this->parent_table = 'gx_fl';
		$this->parent_field = 'gx_fl_id';
	}
	
	public function getMoreInfoForRow($row){
		$sql = "SELECT * from gx_input where gx_id={$row['id']}";
		$res = $this->tool->query($sql);
		$row['gx_input'] = $res->fetchAll();
		
		$sql = "SELECT * from gx_output where gx_id={$row['id']}";
		$res = $this->tool->query($sql);
		$row['gx_output'] = $res->fetchAll();
		return $row;
	}	
	
	protected function handleFillOptionCondition(){
		$this->fillOptionConditions['wz_id'] = array(array('field'=>'wz_fl_id', 'op'=>'IN', 'value'=>array(WZ_FL_YUANLIAO)));
		
		$this->allFields['wz_id'] = true;
	}
}
