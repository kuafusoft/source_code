<?php
require_once('table_desc.php');

class useradmin_users extends table_desc{
	protected function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		$this->options['list'] = array(
            'id'=>array('editable'=>false),
			'username'=>array('unique'=>true),
			'nickname',
			'email',
			'role_ids'=>array('label'=>'Role', 'search'=>true, 'editable'=>true, 'editrules'=>array('required'=>true)),
			'groups_ids'=>array('label'=>'Group', 'search'=>true, 'editable'=>true, 'editrules'=>array('required'=>true)),
			'company_id',
			'status_id'
        );
		$this->options['edit'] = array('company_id', 'username', 'nickname', 'email', 'role_ids', 'groups_ids', 'company_id'); 
		$this->linkTables = array('m2m'=>array('groups', 'role'=>array('link_table'=>'role_user', 'self_link_field'=>'user_id')));
	}
	
	public function accessMatrix(){
		// $access_matrix = parent::accessMatrix();
		$access_matrix['all']['all'] = false;
		$access_matrix['admin']['all'] = true;
		return $access_matrix;
	}
	
    // public function getMoreInfoForRow($row){
		// $res = $this->db->query("SELECT group_concat(DISTINCT role_id) as role_ids FROM role_user WHERE user_id=".$row['id']);
		// $prj = $res->fetch();
		// $row['role_ids'] = $prj['role_ids'];

		// $res = $this->db->query("SELECT group_concat(DISTINCT groups_id) as groups_ids FROM groups_users WHERE users_id=".$row['id']);
		// $prj = $res->fetch();
		// $row['groups_ids'] = $prj['groups_ids'];
		// return $row;
	// }

	// public function calcSqlComponents($params, $doLimit = true){
		// $role_ids = 0;
		// $group_ids = 0;
// //print_r($params['searchConditions']);		
		// foreach($params['searchConditions'] as $k=>$c){
// //print_r($k);
// //print_r($c);		
// //			if ($k == 'and')
// //				continue;
// //print_r($c);				
// //print_r("field = >>>".$c['field']."<<<");
			// if(isset($c['field']) && $c['field'] == 'role_ids'){
// //print_r($c);			
				// $role_ids = $c['value'];
				// unset($params['searchConditions'][$k]);
				// continue;
			// }
			// if(isset($c['field']) && $c['field'] == 'groups_ids'){
// //print_r($c);			
				// $groups_ids = $c['value'];
				// unset($params['searchConditions'][$k]);
				// continue;
			// }
		// }
// //print_r("k = $k, role_ids = $role_ids, groups_ids = $groups_ids");				
		// $sqls = parent::calcSqlComponents($params, $doLimit);
		// if (!empty($role_ids)){
			// $sqls['main']['from'] .= ' LEFT JOIN role_user ON users.id=role_user.user_id';
			// $sqls['where'] .= " AND role_user.role_id=$role_ids";
		// }
		// if (!empty($groups_ids)){
			// $sqls['main']['from'] .= ' LEFT JOIN groups_users ON users.id=groups_users.users_id';
			// $sqls['where'] .= " AND groups_users.groups_id=$groups_ids";
		// }
		
// //print_r($sqls);		
		// return $sqls;
	// }
	
	protected function getButtons(){
        $buttons = array(
            'resetpassword'=>array('caption'=>'Reset Password',
                'buttonimg'=>'',
                'title'=>'Reset Password to 123'),
			'lock'=>array('caption'=>'Lock Users'),
			'unlock'=>array('caption'=>'unLock Users'),
        );
        $buttons = array_merge($buttons, parent::getButtons());
		unset($buttons['tag']);
		unset($buttons['subscribe']);
		return $buttons;
	}
}
?>