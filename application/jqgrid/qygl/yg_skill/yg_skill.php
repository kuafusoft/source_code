<?php
require_once('table_desc.php');
//员工管理
class qygl_yg_skill extends table_desc{
	protected function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		
        $this->options['list'] = array(
            'id'=>array('editable'=>false, 'hidden'=>true),
            'yg_id'=>array('label'=>'员工', 'editable'=>false, 'formatter'=>'text'),
			'skill_id'=>array('label'=>'技能'),
			'skill_grade_id'=>array('label'=>'技能水平'),
			'note'=>array('label'=>'备注', 'edittype'=>'text'),
        );
		// $this->options['navOptions']['refresh'] = false;
	}
	
	// public function accessMatrix(){
		// $access_matrix = array(
			// 'all'=>array('index'=>true, 'list'=>true, 'export'=>true),
			// 'admin'=>array('all'=>true, ),
		// );
		
		// $access_matrix['row_owner'] = $access_matrix['assistant_admin'] = $access_matrix['admin'];
		
		// return $access_matrix;
	// }
}
