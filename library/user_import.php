<?php
// 导入User
require_once('xt_common.php');

class user_import extends xt_common{
	private $source;
	private $target;
		
	public function __construct($source_dsn, $target_dsn){
		$this->source = $this->getDb($source_dsn);
		$this->target = $this->getDb($target_dsn);
	}
	
	public function import(){
		// import roles
		$res = $this->source->query("SELECT * FROM tms_sa_role");
		while($row = $res->fetch()){
			$this->target->insert('role', $row);
		}
		// import groups 
		$res = $this->source->query("SELECT * FROM tms_sa_group");
		while($row = $res->fetch()){
			$this->target->insert('groups', $row);
		}
		// import users
		$res = $this->source->query("SELECT * FROM tms_sa_users");
		while($row = $res->fetch()){
			unset($row['firstname']);
			unset($row['lastname']);
			$row['nickname'] = $row['name'];
			unset($row['name']);
			$row['status_id'] = $row['isactive'];
			unset($row['isactive']);
			$this->target->insert('users', $row);
		}
		// import user-role, user-group
		$res = $this->source->query("SELECT * FROM tms_sa_user_role");
		while($row = $res->fetch()){
			$this->target->insert('role_user', array('id'=>$row['id'], 'role_id'=>$row['roleid'], 'user_id'=>$row['userid']));
		}
		$res = $this->source->query("SELECT * FROM tms_sa_user_group");
		while($row = $res->fetch()){
			$this->target->insert('groups_users', array('id'=>$row['id'], 'groups_id'=>$row['groupid'], 'users_id'=>$row['userid']));
		}
	}
	
};


?>