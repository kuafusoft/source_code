<?php
require_once('table_desc.php');
//员工管理
class qygl_hb_yg extends table_desc{
	protected function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		
        $this->options['list'] = array(
            'id'=>array('editable'=>false, 'hidden'=>true),
            'hb_id'=>array('label'=>'名称', 'editable'=>false, 'formatter'=>'text'),
			'work_type_id'=>array('label'=>'工种'),
			'dept_id'=>array('label'=>'部门'),
			'position_id'=>array('label'=>'职位'),
			'salary_fl_id'=>array('label'=>'工资类型'),
			'base_salary'=>array('label'=>'基本工资', 'post'=>array('value'=>'元')),
            'ticheng_ratio'=>array('label'=>'提成比例'),
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
