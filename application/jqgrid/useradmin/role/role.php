<?php
require_once('table_desc.php');

class useradmin_role extends table_desc{
	protected function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		$this->options['list'] = array(
            'id'=>array('editable'=>false),
			'name'=>array('unique'=>true),
			'description',
			'users_ids'=>array('label'=>'User', 'search'=>true, 'editable'=>true, 'editrules'=>array('required'=>true)),
        );
		$this->options['edit'] = array('name', 'description', 'users_ids'); 
		$this->linkTables = array('m2m'=>array('users'=>array('link_table'=>'role_user', 'self_link_field'=>'role_id', 'link_field'=>'user_id')));
	}
	
    // public function getMoreInfoForRow($row){
		// $res = $this->db->query("SELECT group_concat(DISTINCT user_id) as users_ids FROM role_user WHERE role_id=".$row['id']);
		// $prj = $res->fetch();
		// $row['users_ids'] = $prj['users_ids'];
		// return $row;
	// }

	// public function calcSqlComponents($params, $doLimit = true){
		// $users_ids = 0;
		// foreach($params['searchConditions'] as $k=>$c){
			// if(isset($c['field']) && $c['field'] == 'users_ids'){
				// $users_ids = $c['value'];
				// unset($params['searchConditions'][$k]);
				// continue;
			// }
		// }
		// $sqls = parent::calcSqlComponents($params, $doLimit);
		// if (!empty($users_ids)){
			// $sqls['main']['from'] .= ' LEFT JOIN role_user ON role.id=role_user.role_id';
			// $sqls['where'] .= " AND role_user.user_id=$users_ids";
		// }
		// return $sqls;
	// }
}
?>