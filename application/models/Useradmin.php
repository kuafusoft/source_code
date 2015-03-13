<?php
defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/..'));
require_once('Zend/Auth.php');
require_once('Zend/Config/Xml.php');
require_once(APPLICATION_PATH.'/../library/const_def.php');
require_once(APPLICATION_PATH.'/../library/dbfactory.php');

// function getNavigationMenus($roles){
    // $menus = array();
    // $menuType = array('user', 'navi');
    // if(empty($roles))
        // $roles = array('guest');
    // foreach($roles as $role){
        // $config = new Zend_Config_Xml(APPLICATION_PATH . '/configs/role_menu.xml', $role);  
        // $menu = $config->toArray();
        // foreach($menu as $header =>$item){
            // if (!isset($item['display']) || $item['display'] == false || $item['display'] == 'false'){
                // continue;
            // }
            // foreach($item as $name=>$v){
                // if (!is_array($v) || (isset($v['display']) && ($v['display'] == true || $v['display'] == 'true')))
                    // $menus[$header][$name] = $v;
            // }
        // }
    // }
    // return $menus;
// }
    

class Application_Model_Useradmin{
    var $db;
    var $userInfo = null;
    var $options = array();
	var $request = null;
    public function __construct($controller, array $options = null){
		$this->db = dbFactory::get('useradmin');
        $this->userInfo = $this->getUserInfo();
    } 

    public function getUserInfo(){
        if(is_null($this->userInfo)){
            $auth = Zend_Auth::getInstance();
            $logined = $auth->hasIdentity();
            if ($logined){
                $this->userInfo = $auth->getIdentity();
            }
            else{
                $this->resetUserInfo();
            }
        }
//print_r($this->userInfo);        
        return $this->userInfo;
    }
    
    private function resetUserInfo(){
        $this->userInfo = new stdClass;
        $this->userInfo->id = 0;
        $this->userInfo->nickname = 'Guest';
        $this->userInfo->roles = array('guest');
	$this->userInfo->group_ids = '0';
        $this->userInfo->menus = $this->getNavigationMenus(array('guest'));
		$this->userInfo->indexList = $this->getIndexList(array('guest'));
    }
    
	function getNavigationMenus($roles){
		$menus = array();
		$menuType = array('user', 'navi');
		if(empty($roles))
			$roles = array('guest');
		foreach($roles as $role){
			try{
				$config = new Zend_Config_Xml(APPLICATION_PATH . '/configs/role_menu.xml', $role);  
				$menu = $config->toArray();
				foreach($menu as $header =>$item){
					$header_display = isset($item['display']) ? $item['display'] : 'false';
					if(empty($menus[$header]))
						$menus[$header] = array('display'=>$header_display, 'items'=>array());
					elseif($header_display == 'true')
						$menus[$header]['display'] = $header_display;
					foreach($item as $name=>$v){
						if(is_array($v)){
							$item_display = isset($v['display']) ? $v['display'] : 'false';
							if(empty($menus[$header]['items'][$name])){
								$menus[$header]['items'][$name] = $v;
							}
							elseif($item_display == 'true'){
								$menus[$header]['items'][$name]['display'] = $item_display;
							}
						}
						else{
							$menus[$header]['items'][$name] = $v;
						}
					}
				}
			}catch(Exception $e){
			
			}
		}
// print_r($menus);
		$display_menus = array();
		foreach($menus as $header=>$item){
			if($item['display'] == 'false')
				continue;
// print_r($item['items']);				
			foreach($item['items'] as $name=>$v){
// print_r(">>>>>>>name = $name, ");			
// print_r($v);	
// print_r("<<<<<<<<");		
				if(!is_array($v) || (isset($v['display']) && $v['display'] == 'true')){
					$display_menus[$header][$name] = $v;
				}
			}
		}
// print_r($display_menus);
		return $display_menus;
	}
    
	function getIndexList($roles){
		$menus = array();
		if(empty($roles))
			$roles = array('guest');
		foreach($roles as $role){
// print_r("role = $role\n");		
			try{
				$config = new Zend_Config_Xml(APPLICATION_PATH . '/configs/role_index.xml', $role);  
				$menu = $config->toArray();
// print_r($menu);				
				foreach($menu as $header =>$item){
					if (!isset($item['display']) || $item['display'] == false || $item['display'] == 'false'){
						continue;
					}
					foreach($item as $name=>$v){
						if (!is_array($v) || (isset($v['display']) && ($v['display'] == true || $v['display'] == 'true')))
							$menus[$header][$name] = $v;
					}
				}
			}catch(Exception $e){
			
			}
		}
// print_r("last Menus = ");		
// print_r($menus);		
		return $menus;
	}
	
	public function getRoles($userId, $acl){
		$roles = array();
		$sql = "SELECT role.name, GROUP_CONCAT(CONCAT(acl.resource, ':', acl.action)) AS permits ".
			" FROM role LEFT JOIN role_user ON role.id=role_user.role_id ".
			" LEFT JOIN acl ON role.id=acl.role_id".
			" WHERE role_user.user_id=$userId".
			" GROUP BY role.id";
		$res = $this->db->query($sql);
		while($row = $res->fetch()){
			$roles[] = $row['name'];
			// $acl->addRole(new Zend_Acl_Role($role['name']));
			// $permits = explode(',', $row['permits']);
			// $resources = array();
			// foreach($permits as $permit){
				// $resource = explode(':', $permit);
				// if (count($resource) > 1)
					// $resources[$resource[0]][] = strtolower($resource[1]);
			// }
			// foreach($resources as $resource=>$actions){
				// $acl->add(new Zend_Acl_Resource(strtolower($resource)));
				// $acl->allow($row['name'], strtolower($resource), array_unique($actions));
			// }
		}
		return $roles;
	}
	
	public function getConfigInfo($db_name, $user_id = 0){
		$configDb = '';
		if(empty($user_id)){
			$userInfo = $this->getUserInfo();
			$user_id = $userInfo->id;
		}
		if(!empty($user_id)){
			$res = $this->db->query("SELECT company_config.* FROM company_config left join company on company.id=company_config.company_id ".
				" left join users on users.company_id=company.id WHERE users.id=$user_id AND company_config.db_name='$db_name'");
			if($row = $res->fetch())
				$configDb = $row['config'];
		}
		return $configDb;
	}
	
    public function login($userName, $password){
        $adapter = new Zend_Auth_Adapter_DbTable(
            $this->db,
            'useradmin.users',
            'username',
            'password',
            '? AND status_id=1'
//            'MD5(?) AND status_id=1'
            );
        $adapter->setIdentity($userName);
        $adapter->setCredential($password);
        $auth = Zend_Auth::getInstance();
//        $auth->setStorage(new Zend_Auth_Storage_Session('hengshan'));
        $result = $auth->authenticate($adapter);
        if ($result->isValid()) {
            $this->userInfo = $adapter->getResultRowObject();
            $userId = $this->userInfo->id;
//print_r($this->userInfo);			
            // get RoleIds
            $sql = "SELECT role.name, GROUP_CONCAT(CONCAT(acl.resource, ':', acl.action)) AS permits ".
                " FROM role LEFT JOIN role_user ON role.id=role_user.role_id ".
                " LEFT JOIN acl ON role.id=acl.role_id".
                " WHERE role_user.user_id=$userId".
                " GROUP BY role.id";
//print_r($sql);				
            $res = $this->db->query($sql);
            $rows = $res->fetchAll();
            $acl = new Zend_Acl();
            $roles = array('normal');
            foreach($rows as $tmp){
                $roles[] = $tmp['name'];
                $acl->addRole(new Zend_Acl_Role($tmp['name']));
                $permits = explode(',', $tmp['permits']);
                $resources = array();
                foreach($permits as $permit){
                    $resource = explode(':', $permit);
                    if (count($resource) > 1)
                        $resources[$resource[0]][] = strtolower($resource[1]);
                }
                foreach($resources as $resource=>$actions){
                    $acl->add(new Zend_Acl_Resource(strtolower($resource)));
                    $acl->allow($tmp['name'], strtolower($resource), array_unique($actions));
                }
            }
			$res = $this->db->query("SELECT GROUP_CONCAT(distinct groups_id) as group_ids FROM groups_users WHERE users_id=$userId");
			$row = $res->fetch();
			$group_ids = $row['group_ids'];
            $this->userInfo->roles = $roles;
			$this->userInfo->group_ids = $group_ids;
			unset($this->userInfo->password);
			unset($this->userInfo->password_salt);
            $this->userInfo->acl = serialize($acl);
            $this->userInfo->menus = $this->getNavigationMenus($this->userInfo->roles);
			$this->userInfo->indexList = $this->getIndexList($this->userInfo->roles);
//print_r($row);            
        }
        else{
            $this->resetUserInfo();
        }
//print_r($this->userInfo);
        $storage = $auth->getStorage();
        $storage->write($this->userInfo);
        return $result;
    }
    
    public function logout(){
        Zend_Auth::getInstance()->clearIdentity();
        $this->resetUserInfo();
    }
    
    public function changePassword($userInfo, $password){
        $set = array('password'=>md5($password));
        $table = 'users';
        $where = $this->db->quoteInto('username = ?', $userInfo->username);
//$this-> array("username"=>$userInfo['username']);
        $this->db->update($table, $set, $where);
        return true; //$result;
    }
	
	public function resetPassword($user_id){
		$this->db->update('users', array('password'=>md5('123456')), "id=$user_id");
		return true;
	}
    
    public function register($form){
        $username = $form->getValue('username');
        $nickname = $form->getValue('nickname');
        $password = md5($form->getValue('password'));
        $email = $form->getValue('email');
        $sql = $this->db->quoteInto("SELECT * FROM users Where username=?", $username);
        $result = $this->db->fetchRow($sql);
        if ($result){
            return false;
        }
        $this->db->insert('users', compact('username', 'nickname', 'password', 'email'));
        $userId = $this->db->lastInsertId();
        $this->db->insert('role_user', array('user_id'=>$userId, 'role_id'=>1)); // normal role
        $this->db->insert('role_user', array('user_id'=>$userId, 'role_id'=>3)); // test role
        $result = $this->login($username, $form->getValue('password'));
        return $result;
    }
	
	public function getProfile(){
		$userInfo = $this->userInfo;
		$res = $this->db->query("SELECT * FROM users where id={$this->userInfo->id}");
		$detail = $res->fetch();
		$res = $this->db->query("SELECT * FROM expe where user_id={$this->userInfo->id}");
		$expe = $res->fetchAll();
		
		// get interests
		$sql = "SELECT interest_user.*, interest_type.name as interest_type ".
			" FROM interest_user left join interest_type on interest_user.interest_type_id=interest_type.id".
			" where interest_user.user_id={$this->userInfo->id}";
		$res = $this->db->query($sql);
		$interest = $res->fetchAll();
		// get memorial day
		$sql = "SELECT memorial_day.*, memorial_day_type.name as memorial_day_type ".
			" FROM memorial_day left join memorial_day_type on memorial_day.memorial_day_type_id=memorial_day_type.id".
			" where memorial_day.user_id={$this->userInfo->id}";
		$res = $this->db->query($sql);
		$memorial_day = $res->fetchAll();
		// get circles
		$res = $this->db->query("SELECT * FROM circle where creater_id={$this->userInfo->id}");
		$circle = $res->fetchAll();
		
		return compact('detail', 'expe', 'memorial_day', 'interest', 'circle');
	}
	
	public function setProfile($profile){

	}
    
    public function setFavorite(){
    
    }
    
    public static function isAllowed($resource, $action){
        $isAllowed = false;
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()){
            $userInfo = $auth->getIdentity();
            $acl = unserialize($userInfo->acl);
            foreach($userInfo->roles as $role){
                if ($acl->isAllowed($role, strtolower($resource), strtolower($action))){
                    $isAllowed = true;
                    break;
                }
            }
        }
        return $isAllowed;
    }
    
    public function getCookie($params){
        $userInfo = $this->getUserInfo();
        $userId = $userInfo->id;
        if (isset($params['user_id']))
            $userId = $params['user_id']; 
        $v = array('user_id'=>$userId, 'name'=>$params['name']);
        $cookie = '';
        $result = $this->db->query("SELECT * FROM user_cookie WHERE user_id=:user_id AND name=:name", $v);
        while ($row = $result->fetch()){
//			print_r($row);
            $cookie[$row['type']] = $row['content'];
		}
        return json_encode($cookie);
    }
    
    public function saveCookie($params){
        $userInfo = $this->getUserInfo();
        $userId = $userInfo->id;
        if (isset($params['user_id']))
            $userId = $params['user_id']; 
        $v = array('user_id'=>$userId, 'type'=>$params['type'], 'name'=>$params['name'], 'content'=>$params['content']);
//print_r($v);
        $this->db->query('DELETE FROM user_cookie WHERE user_id=:userId AND type=:type AND name=:name', array('userId'=>$v['user_id'], 'type'=>$v['type'], 'name'=>$v['name']));
        $this->db->insert('user_cookie', $v);        
    }
    
    public function ajax(){
        
    }
    
    public function getUserTable(){
        return 'useradmin.users';
    }
    
	public function getSubUsers($level, $includeSelf = false){
		$users = array();
		$currentUser = $this->userInfo->id;
		if ($includeSelf)
			$users[] = $currentUser;
//print_r($level);			
		switch($level){
			case 'myself-level':
				$sql = "SELECT ps WHERE user_id=$currentUser";
				break;
			case 1: // Son level Only
			case 'son-level':
				$sql = "SELECT user_id FROM user_report_to WHERE manager_id=$currentUser";
				break;
			case 2: // all sub levels
			case 'sub-levels':
				$res = $this->db->query("SELECT ps WHERE user_id=$currentUser");
				$row = $res->fetch();
				$ps = $row['ps'];
				$sql = "SELECT user_id FROM user_report_to WHERE ps like '$ps%'";
				break;
		}
		$res = $this->db->query($sql);
		while($row = $res->fetch())
			$users[] = $row['user_id'];
		return $users;
	}
	
    public function getUserList($conditions = array()){
//print_r($conditions);	
        $userList = array();
        if (!empty($conditions['blank']))
            $userList[0] = ' ';
		if (!empty($conditions['blank_item']))
            $userList[-1] = '=Blank=';
        $main = "SELECT users.id, users.nickname";
		$from = "users";
		$where = array(" 1 ");
		if (!empty($conditions['id'])){
			if ($conditions['id'][0] == ',')
				$conditions['id'] = substr($conditions['id'], 1, -1);
            $where[] = " users.id in (".$conditions['id'].")";
		}
        if (!empty($conditions['active'])){
            $where[] = " users.status_id=1";
        }
		if (!empty($conditions['role_id'])){
			if (is_array($conditions['role_id']))
				$conditions['role_id'] = implode(',', $conditions['role_id']);
			$from  .= " left join role_user on users.id=role_user.user_id";
			$where[] = " role_user.role_id in ({$conditions['role_id']})";
		}
		if (!empty($conditions['groups_id'])){
			if (is_array($conditions['groups_id']))
				$conditions['groups_id'] = implode(',', $conditions['groups_id']);
			$from  .= " left join groups_users on users.id=groups_users.users_id";
			$where[] = " groups_users.groups_id in ({$conditions['groups_id']})";
		}
		$where = implode(' AND ', $where);
		$sql = "$main FROM $from WHERE $where ORDER BY nickname ASC";
        $res = $this->db->query($sql);
        while($row = $res->fetch()){
            $userList[$row['id']] = $row['nickname'];
        }
        return $userList;
    }
	
	public function getUsers($userIds){
		if (is_array($userIds))
			$userIds = implode(',', $userIds);
		if (!empty($userIds)){
			$sql = "SELECT * FROM users WHERE id in ($userIds)";
			$res = $this->db->query($sql);
			return $res->fetchAll();
		}
		return array();
	}
    
    public function getReviewerList($object){
    
        return $this->getUserList(array("role_id"=>4));
    }
    
	public function getGroups($object){
		$ret = array();
		$res = $this->db->query("SELECT id, name FROM groups");
		$ret[0] = '';
		while($row = $res->fetch())
			$ret[$row['id']] = $row['name'];
		return $ret;
	}
	
	public function isReviewer($userId, $db, $table, $element_id){
		$res = $this->db->query("SELECT * FROM user_task WHERE task_type=:task and db=:db and `table`=:table and element_id=:element_id and user_id=:user_id and finished=0",
			array('task'=>'review', 'db'=>$db, 'table'=>$table, 'element_id'=>$element_id, 'user_id'=>$userId));
		if($row = $res->fetch())
			return $row['id'];
		return false;
	}
	
	public function isAdmin($userId){
		$res = $this->db->query("SELECT role_user.role_id, role.name as name ".
			" FROM role_user LEFT JOIN role on role_user.role_id = role.id ".
			" WHERE role_user.user_id={$userId} and name='admin'");
		return $res->rowCount();

		$res = $this->db->query("SELECT role_id FROM role_user WHERE user_id=".$userId);
		while($row = $res->fetch()){
			$role[] = $row['role_id'];
		}
		$roles = implode(',', $role);
		$res = $this->db->query("SELECT name FROM role WHERE id in (".$roles.")");
		while($row = $res->fetch()){
			if($row['name'] == 'admin')
				return true;
		}
		return false;
	}
	
    public function inform($userId, $subject, $body){
        if (is_string($userId))
            $userId = explode(',', $userId);
        else if (is_int($userId))
            $userId = array($userId);
        if (is_array($userId))
            $where = "id IN (".implode(',', $userId).")";
        else
            $where = "id=$userId";
        $where .= " AND status_id=1";
//print_r($where);
//		return;
        $result = $this->db->query("SELECT email FROM users WHERE $where");
        $emails = array();
        while($row = $result->fetch()){
            $emails[] = $row['email'];
        }
        if (!empty($emails)){
            $to = implode(',', $emails);
            $header = 'From:'.$this->userInfo->nickname.'(xiaotianadmin@umbrella.ap.freescale.net)';
    
            mail($to, $subject, $body, $header);
        }
        // send message
        foreach($userId as $user){
            $msg = array('user_id'=>$user, 'from'=>$this->userInfo->id, 'subject'=>$subject, 'message'=>$body);
            $this->db->insert('user_message', $msg);
        }        
    }
    
	public function calcUsers($user_ids, $users_groups_ids){
        if (is_int($user_ids))
            $user_ids = array($user_ids);
        else if (is_string($user_ids))
            $user_ids = explode(',', $user_ids);
		// handle group_ids
		if (!empty($users_groups_ids)){
			$res = $this->db->query("SELECT group_concat(users_id) as user_ids FROM groups_users WHERE groups_id IN (".implode(',', $users_groups_ids).")");
			$row = $res->fetch();
			$guser_ids = explode(',', $row['user_ids']);
			$user_ids = array_unique(array_merge($user_ids, $guser_ids));
		}
		return $user_ids;
	}
	
	public function addTask($params){
//print_r($params);
		$user_ids = $params['user_ids'];
		if (is_int($user_ids))
			$user_ids = array($user_ids);
		else if (is_string($user_ids))
			$user_ids = explode(',', $user_ids);
		$assigner_id = $this->userInfo->id;
		if (!empty($params['assigner_id']))
			$assigner_id = $params['assigner_id'];
		$res = $this->db->query("SELECT username, nickname, email FROM users WHERE id=$assigner_id");
		$assigner = $res->fetch();
		if (!isset($params['task_priority_id']))
			$params['task_priority_id'] = TASK_PRIORITY_MIDDLE;
		if (!isset($params['deadline']))
			$params['deadline'] = date('Y-m-d', time() + 3600 * 24 * 5);//mktime(0,0,0,date("m"),date("d")+5,date("Y"))); // 5 days
		//检查同一个Task是否已经存在，Task的标志为url
		$task = array('task_type_id'=>$params['task_type_id'], 'description'=>$params['description'], 'url'=>$params['url'], 
			'task_priority_id'=>$params['task_priority_id'], 'deadline'=>$params['deadline'], 'progress'=>0, 
			'controller_id'=>$params['controller_id'], 'task_result_id'=>0, 'isactive'=>ISACTIVE_ACTIVE, 'creater_id'=>$this->userInfo->id);
		$res = $this->db->query("SELECT id FROM task where url=:url", array('url'=>$params['url']));
		if ($row = $res->fetch()){
			// $this->db->update('task', $task, "id={$row['id']}");
			$task_id = $row['id'];
		}
		else{
			$this->db->insert('task', $task);
			$task_id = $this->db->lastInsertId();
		}
print_r($user_ids);
		$user_taskId = array();
        foreach($user_ids as $id){
            // check if the task has been assigned to the user and not completed
            $v = array('task_id'=>$task_id, 'user_id'=>$id);
            $result = $this->db->query("SELECT * FROM user_task WHERE user_id=:user_id AND task_id=:task_id", $v);
			if($row = $result->fetch()){
print_r($row);			
				$this->db->update('user_task', array('task_result_id'=>TASK_RESULT_NORESULT), 'id='.$row['id']);
				$user_taskId[$id] = $row['id'];
			}
			else{
                $v['assigner_id'] = $assigner_id;
				$v['assign_type_id'] = ASSIGN_TYPE_ASSIGN;
				$v['task_result_id'] = TASK_RESULT_NORESULT;
				$v['comment'] = 'Assigned by '.$assigner['nickname'].'['.$assigner['email'].'] at '.date('Y-m-d H:i:s');
                $this->db->insert('user_task', $v);
				$user_taskId[$id] = $this->db->lastInsertId();
            }
        }
		return $user_taskId;
	}

    public function finishTask($user_taskId, $comment = '', $result_type_id = TASK_RESULT_SUCCESS){
		$this->db->query("UPDATE user_task SET task_result_id=$result_type_id, comment=CONCAT(".$this->db->quote($comment).", \"\n\", comment) where id=$user_taskId");
//        $this->db->update('user_task', array('task_result_id'=>$result_type_id, 'comment'=>$comment), "id=$taskId");
		// 更新Task
		$res = $this->db->query("SELECT * FROM user_task WHERE id=$user_taskId");
		$row = $res->fetch();
		$taskId = $row['task_id'];

		$res = $this->db->query("SELECT task.*, task_type.name as task_type, task_priority.name as task_priority, task_result.name as task_result".
			" FROM task left join task_type on task.task_type_id=task_type.id ".
			" left join task_priority on task.task_priority_id=task_priority.id ".
			" left join task_result on task.task_result_id=task_result.id".
			" WHERE task.id=$taskId");
		$task = $res->fetch();
		
		$res = $this->db->query("SELECT COUNT(*) AS cc FROM user_task WHERE task_id=$taskId");
		$row = $res->fetch();
		$total = $row['cc'];
		$res = $this->db->query("SELECT COUNT(*) AS cc FROM user_task WHERE task_id=$taskId and task_result_id>0");
		$row = $res->fetch();
		$finished = $row['cc'];
		$this->db->update('task', array('progress'=>$finished / $total * 100), 'id='.$taskId);
		// 通知task_controller
		if (!empty($task['controller_id'])){
			$subject = " The task id=$taskId has been changed";
			if ($finished == $total)
				$subject .= " and finished";
			$subject .= ", please check it";
			$body = " The task information is\n<BR>".
				" id: $taskId\n<BR>".
				" type:{$task['task_type']}\n<BR>".
				" description: ".$task['description']."\n<BR>".
				" url:{$task['url']}\n<BR>".
				" priority:{$task['task_priority']}\n<BR>".
				" deadline:{$task['deadline']}\n<BR>".
				" result:{$task['task_result']}\n<BR>";
//print_r($task);				
			$this->inform($task['controller_id'], $subject, $body);
		}
    }
    
	public function modifyTask($taskId, $task_result_id, $comment = ''){
		$task = array('task_result_id'=>$task_result_id, 'comment'=>$comment, 'progress'=>100);
		$this->db->update('task', $task, "id=$taskId");
	}
	
	public function getUserTask($user_taskId){
		$res = $this->db->query("SELECT task.*, user_task.task_result_id as user_task_result_id FROM user_task left join task on user_task.task_id=task.id where user_task.id=$user_taskId");
		$row = $res->fetch();
		return $row;
	}
	
    public function getTasks($user_id, $unfinished = true, $limit = 0){
		$sql = "SELECT user_task.id, user_task.task_id, task.url, task.description, task.task_priority_id, task_priority.name as task_priority, task.progress, ".
			" task.action_type_id, task.deadline, task.task_type_id, task_type.name as task_type, task.task_result_id, task_result.name as my_task_result ".
			" FROM user_task left join task on user_task.task_id=task.id ".
			" left join task_type on task.task_type_id=task_type.id ".
			" left join task_priority on task.task_priority_id=task_priority.id".
			" left join task_result on user_task.task_result_id=task_result.id".
			// " left join task_result task_result2 on task.task_result_id=task_result_2.id".
			" WHERE user_task.user_id=$user_id ";
		if ($unfinished)
			$sql .= " AND user_task.task_result_id=0 AND task.task_result_id=0 AND task.progress!=100";
		else
			$sql .= " AND (user_task.task_result_id>0 OR task.task_result_id>0)";
		
		$sql .= " ORDER BY task.task_result_id ASC, task.progress ASC, deadline ASC, task_priority_id ASC";
		if ($limit > 0)
			$sql .= " LIMIT 0, $limit";
        $result = $this->db->query($sql);
        $tasks = $result->fetchAll();
// print_r($tasks);		
        return $tasks;
    }
    
    public function getMyControlledTasks($user_id, $unfinished = true){
		$sql = "SELECT task.*, task_priority.name as task_priority, task_type.name as task_type, task_result.name as task_result ".
			" FROM task left join task_type on task.task_type_id=task_type.id ".
			" left join task_priority on task.task_priority_id=task_priority.id".
			" left join task_result on task.task_result_id=task_result.id".
			" WHERE task.controller_id=$user_id ";
		if ($unfinished)
			$sql .= " AND task.task_result_id=0";
		$sql .= " ORDER BY task.task_result_id ASC, task.progress ASC, deadline ASC, task_priority_id ASC";
        $result = $this->db->query($sql);
        $tasks = $result->fetchAll();
        return $tasks;
    }
    
	//分类：编辑中的，Review中的，
	public function getCases($user_id){
		$xt_db = dbFactory::get('xt', $realDbName);
		$notPublished = EDIT_STATUS_EDITING.','.EDIT_STATUS_REVIEW_WAITING.','.EDIT_STATUS_REVIEWING.','.EDIT_STATUS_REVIEWED;
		$sql = "SELECT testcase_ver.testcase_id, testcase.code, testcase.summary, group_concat(distinct testcase_ver.id) as ver_ids, group_concat(distinct edit_status.name) as edit_status".
			" FROM $realDbName.testcase_ver testcase_ver LEFT JOIN $realDbName.testcase testcase on testcase_ver.testcase_id=testcase.id ".
			" left join $realDbName.edit_status edit_status on testcase_ver.edit_status_id=edit_status.id".
			" WHERE (testcase_ver.owner_id=$user_id OR testcase_ver.updater_id=$user_id) AND testcase_ver.edit_status_id IN ($notPublished)".
			" GROUP BY testcase_ver.testcase_id".
			" order by testcase_ver.edit_status_id ASC, testcase.code ASC";
// print_r($sql);			
		$res = $xt_db->query($sql);
		return $res->fetchAll();
	}
	
	public function getCycles($user_id){
		$cycles = array();
		$xt_db = dbFactory::get('xt', $realDbName);
		
		$sql = "SELECT distinct cycle.id, cycle.name FROM $realDbName.cycle left join $realDbName.cycle_detail on cycle.id=cycle_detail.cycle_id".
			" left join $realDbName.prj prj on cycle.prj_id=prj.id".
			" where cycle.cycle_status_id=".CYCLE_STATUS_ONGOING.
			" AND cycle_detail.tester_id=$user_id".// OR cycle.creater_id=$user_id)".
			" AND cycle.isactive=".ISACTIVE_ACTIVE.
			" AND prj.isactive=".ISACTIVE_ACTIVE.
			" AND prj.prj_status_id=".PRJ_STATUS_ONGOING.
			" order by cycle.created DESC";
// print_r($sql);			
		$res = $xt_db->query($sql);
		while($row = $res->fetch()){
			$total = 0;
			$stat = array();
			$sql = "SELECT cycle_detail.result_type_id, result_type.name as result_type, count(*) as cc ".
				" FROM $realDbName.cycle_detail left join $realDbName.result_type on cycle_detail.result_type_id=result_type.id ".
				" where cycle_id={$row['id']} AND tester_id=$user_id GROUP BY result_type_id ORDER BY result_type ASC";
			$tmp = $xt_db->query($sql);
			while($ttt = $tmp->fetch()){
// print_r($ttt)			;
				if($ttt['result_type_id'] == 0)
					$ttt['result_type'] = 'Unfinished';
				$total += $ttt['cc'];
				$stat[$ttt['result_type']] = $ttt['cc'];
//				$row[$ttt['result_type']] = $ttt['cc'];
			}
			foreach($stat as $result_type=>$cc){
				$row[$result_type] = $cc.'/'.$total;
			}
			// $sql = "SELECT cycle_detail.result_type_id, result_type.name as result_type, count(*) as cc ".
				// " FROM $realDbName.cycle_detail left join $realDbName.result_type on cycle_detail.result_type_id=result_type.id ".
				// " where cycle_id={$row['id']} GROUP BY result_type_id ORDER BY result_type ASC";
			// $tmp = $xt_db->query($sql);
			// while($ttt = $tmp->fetch()){
// // print_r($ttt)			;
				// if($ttt['result_type_id'] == 0)
					// $ttt['result_type'] = 'Unfinished';
				// if (empty($row[$ttt['result_type']]))
					// $row[$ttt['result_type']] = 0;
				// $row[$ttt['result_type']] = $row[$ttt['result_type']].'/'.$ttt['cc'];
			// }
			$cycles[] = $row;
		}
// print_r($cycles)		;
		return $cycles; //$res->fetchAll();
	}
	
    public function getSubscribes($user_id){
        $opers = array();
        $result = $this->db->query("select * from user_subscribe WHERE user_id=$user_id");
        return $result->fetchAll();
    }
    
    public function getMessages($user_id){
        $result = $this->db->query("SELECT msg.id, msg.from, user.nickname as name, msg.subject, msg.message, msg.handled FROM user_message msg LEFT JOIN users user ON msg.from=user.id WHERE msg.user_id=$user_id AND msg.handled!=2 ORDER BY id DESC");
        $messages = $result->fetchAll();
        return $messages;
    }

    public function getMessage($id){
        $result = $this->db->query("SELECT msg.id, msg.from, user.nickname as name, msg.subject, msg.message, msg.handled FROM user_message msg LEFT JOIN users user ON msg.from=user.id WHERE msg.id=$id");
        $message = $result->fetch();
        $this->db->update('user_message', array('handled'=>1), "id=$id");
        return $message;
    }
    
    public function removeMessage($id){
        $this->db->update('user_message', array('handled'=>2), "id=$id");
        return $id;
    }
	
    public function replyMsg($msg){
        $tos = $msg['user_id'];
        if (is_string($tos))
            $tos = explode(',', $tos);
//print_r($tos);            
        foreach($tos as $to){
            $sql = $this->db->quoteInto("SELECT * FROM users WHERE nickname=?", $to);
            $result = $this->db->query($sql);
            $row = $result->fetch();
            if (!empty($row['id'])){
                $msg['user_id'] = $row['id'];
                $this->db->insert('user_message', $msg);
            }
        }
    }
    
    public function subscribe($user_id, $object, $items){
		foreach($items as $object_id=>$description){
            $this->unsubscribe($user_id, $object, $object_id);
            $oper = 'all';
            $this->db->insert('user_subscribe', compact('user_id', 'object', 'object_id', 'description', 'oper'));
        }
    }
    
    public function unsubscribe($user_id, $object, $object_id, $opers = array()){
        if (empty($opers)){
            $this->db->query("DELETE FROM user_subscribe WHERE user_id = :user_id AND object = :object AND object_id = :object_id", compact('user_id', 'object', 'object_id'));
        }
        else{
            if (is_string($opers))
                $opers = explode(',', $opers);
            foreach($opers as $oper){
                $this->db->query("DELETE FROM user_subscribe WHERE user_id = :user_id AND object = :object AND object_id = :object_id AND oper = :oper", compact('user_id', 'object', 'object_id', 'oper'));
            }
        }
    }
    
    public function unscribe($id){
        $this->db->delete('user_subscribe', "id=$id");
    }
    
    public function hasSubscribed($user_id, $object, $object_id){
        $opers = array();
        $p = compact('user_id', 'object', 'object_id');
        $result = $this->db->query("select oper from user_subscribe WHERE user_id=:user_id AND object=:object AND object_id=:object_id", compact('user_id', 'object', 'object_id'));
        while($row = $result->fetch())
            $opers[] = $row['oper'];
//print_r($opers);            
        return $opers;
    }
    
    public function processSubscribe($object, $object_id, $oper){
		$db_table = explode('.', $object);
		$db = substr($db_table[0], 1, -1);
		$table = substr($db_table[1], 1, -1);
        if (is_numeric($object_id))
            $object_id = array($object_id);
		else if (is_string($object_id))
			$object_id = explode(',', $object_id);
        if (is_array($object_id)){
            foreach($object_id as $each){
                $subscribers = $this->whoSubscribe($object, $each, $oper);
				//看是否存在OWNER_ID or CREATER_ID
				$res = $this->db->query("SELECT * FROM $db.$table where id=$each");
				$row = $res->fetch();
				if(!empty($row['owner_id']))
					$subscribers[] = array('user_id'=>$row['owner_id'], 'description'=>$db.'.'.$table);
				elseif(!empty($row['creater_id']))
					$subscribers[] = array('user_id'=>$row['creater_id'], 'description'=>$db.'.'.$table);
				
				$link = "/jqgrid/jqgrid/newpage/1/oper/information/db/$db/table/$table/element/$each/parent/0";
				foreach($subscribers as $subscriber){
                    $subject = $subscriber['description']." (id:$each) has been {$oper}ed at ".date('Y-m-d H:i:s');
                    $body = " Please check it.<BR />\n".
						"<a href='$link' target='_blank'>Detail Information</a>";
                    $this->inform($subscriber['user_id'], $subject, $body);
                }
            }
        }
    }
    
    public function whoSubscribe($object, $object_id, $oper){
        $subscriber = array();
        $result = $this->db->query("SELECT user_id, description FROM user_subscribe WHERE object=:object AND object_id=:object_id AND (oper=:oper OR oper='all')", compact('object', 'object_id', 'oper'));
        while($row = $result->fetch())
            $subscriber[] = $row;
        return $subscriber;
    }
	
	public function grantAuthority(){
		
	}
}

