<?php
require_once('table_desc.php');
require_once('const_def_qygl.php');

class qygl_unit extends table_desc{
	protected function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
// print_r($params);		
        $this->options['list'] = array(
            'id'=>array('editable'=>false, 'hidden'=>true),
			'unit_fl_id'=>array('label'=>'分类'),
            'name'=>array('label'=>'名称'),
			'fen_zi'=>array('label'=>'分子'),
			'fen_mu'=>array('label'=>'分母'),
			'is_standard'=>array('label'=>'是否标准单位', 'search'=>false)
        );
	}
	
	public function getMoreInfoForRow($row){
		$row['is_standard'] = '';
		$res = $this->tool->query("SELECT * FROM unit_fl WHERE id={$row['unit_fl_id']}");
		if($t = $res->fetch()){
			if($t['unit_id'] == $row['id']){
				$row['is_standard'] = '是';
			}
		}
		return $row;
	}
}
