<?php
require_once('dbfactory.php');
require_once('toolfactory.php');
require_once('useradminfactory.php');

class table_desc{
	protected $db = null;
	protected $configed = array();
	protected $params = array();
	protected $options = array();
	protected $tool = null;
	protected $colModelMap = array();
	protected $userAdmin = null;
	protected $userInfo = null;
	protected $colModels = array();
	protected $action = null;
	protected $actionName = '';
	
	protected $limited = array(); //是否已经进行limit计算
	protected $limit = array();	//实际的限制条件
	
	//以下的各种表间关系可能组合出现，所以不适合用继承来实现
	protected $standardLinkTabled = false;
	protected $linkTables = array();	//各种表间关系，如对多对关系（通过link表互相关联), 多属性（如一个人有多个通讯方式 ）
	
	protected $fillOptionConditions = array();	// 对于传入的params，分析如何过滤可选项
	protected $blankItems = array();	// 哪些字段需要==BLANK==选项
	protected $allFields = array();		//哪些字段需要得到全部信息
	
	public function __construct($db, $table, $params = array()){
		// $this->tool->p_t("Before table_desc Init");
		$this->init($db, $table, $params);
		// $this->tool->p_t("After table_desc Init");
	}
	
	public function get($name){
		return isset($this->options[$name]) ? $this->options[$name] : null;
	}
	
	protected function init($db, $table, $params = array()){
// print_r($params);	
		$this->options['db'] = $db;
		$this->options['table'] = $this->options['real_table'] = $table;
		$this->tool = toolFactory::get(array('tool'=>'db', 'db'=>$this->options['db'], 'table'=>$this->options['table']));
		$this->tool->setDb($this->options['db']);
// $this->tool->p_t("Before $table table_desc init");
		$this->db = dbFactory::get($db, $realDbName);
		$this->options['real_db'] = $realDbName;
		$this->userAdmin = useradminFactory::get();
		$this->userInfo = $this->userAdmin->getUserInfo();
// $this->tool->p_t("Step 1");
// print_r($this->userInfo);		
		
		// $this->params = $params;
		// if(!empty($this->params['self_action'])){
			// $this->action = $this->params['self_action'];
			// $this->actionName = $this->action->getActionName();
			// unset($this->params['self_action']);
		// }
// $this->tool->p_t("Step 2");
		$this->setParams($params);
		
        // if (!empty($params['filters'])){
            // $json_filters = json_decode($params['filters'], true);
            // if(is_array($json_filters)){
				// $gopr = strtolower($json_filters['groupOp']);
				// $rules = $json_filters['rules'];
				// $this->params['searchConditions'] = $this->tool->generateFilterConditions($rules);
				// foreach($this->params['searchConditions'] as $k=>$cond){
					// $this->params['condMap'][$cond['field']] = $cond;
				// }
			// }
		// }
// // $this->tool->p_t("Step 3");
		// $this->handleFillOptionCondition();
		$this->setSubGrid();
// print_r($this->params);		
// $this->tool->p_t("After $table table_desc init");
	}
	
	public function setParams($params){
		$this->params = $params;
// print_r($this->params);		
		if(!empty($this->params['self_action'])){
			$this->action = $this->params['self_action'];
			$this->actionName = $this->action->getActionName();
			unset($this->params['self_action']);
		}
        if (!empty($params['filters'])){
            $json_filters = json_decode($params['filters'], true);
            if(is_array($json_filters)){
				$gopr = strtolower($json_filters['groupOp']);
				$rules = $json_filters['rules'];
				$this->params['searchConditions'] = $this->tool->generateFilterConditions($rules);
				foreach($this->params['searchConditions'] as $k=>$cond){
					$this->params['condMap'][$cond['field']] = $cond;
				}
			}
		}
		$this->handleFillOptionCondition();
// print_r($this->actionName);		
	}
	
	protected function setSubGrid(){
		$this->_setSubGrid();
		if(!empty($this->options['gridOptions']['subGrid'])){
			if(!empty($this->params['condMap'])){
				foreach($this->params['condMap'] as $condMap)
					$this->options['subGrid']['additional'][$condMap['field']] = $condMap['value'];
			}
// print_r($this->params);			
// print_r($this->params['condMap']);			
// print_r($this->options['subGrid']);			
		}
	}

	protected function _setSubGrid(){
	}
	
	protected function standardLinkTable(){
		if(!empty($this->linkTables) && !$this->standardLinkTabled){
			$this->standardLinkTabled = true;
			foreach($this->linkTables as $rel=>$relData){
				if(is_int($rel)){ // set it as m2m
					$rel = 'm2m';
				}
// print_r($relData);			
				if(!is_array($relData)){
					$relData = array('table'=>$relData);
				}
				foreach($relData as $key=>$linkInfo){//rel: one2one, one2m, m2m, ver, history, treeview
					if(is_string($linkInfo)){ // should be the table name
						$linkInfo = array('table'=>$linkInfo);
					}
					elseif(empty($linkInfo['table']) && !is_int($key)){
						$linkInfo['table'] = $key;
					}
					$tmp = explode(".", $linkInfo['table']);
					if(count($tmp) == 2){
						$linkInfo['db'] = $tmp[0];
						$linkInfo['table'] = $tmp[1];
					}
					if(empty($linkInfo['db']))
						$linkInfo['db'] = $this->get('db');
					if(empty($linkInfo['self_link_field']))
						$linkInfo['self_link_field'] = $this->get('table').'_id';
					if(empty($linkInfo['link_db']))
						$linkInfo['link_db'] = $this->get('db');
					if($rel == 'm2m' || $rel == 'node_ver_m2m'){
						if(empty($linkInfo['link_table'])){
							$tables = array($this->get('table'));
							$tables[] = $linkInfo['table'];
							sort($tables);
							$linkInfo['link_table'] = implode('_', $tables);
						}
						if(empty($linkInfo['link_field']))
							$linkInfo['link_field'] = $linkInfo['table'].'_id';
						if($rel == 'node_ver_m2m'){
							$node_table = "";
							if(substr($this->get('table'), -4) == '_ver')
								$node_table = substr($this->get('table'), 0, -4);
							if(empty($linkInfo['node_field'])){
								if(!empty($node_table))
									$linkInfo['node_field'] = $node_table.'_id';
							}
							if(empty($linkInfo['link_node_field'])){
								if(!empty($node_table))
									$linkInfo['link_node_field'] = $node_table.'_id';
							}
						}
					}
					elseif($rel == 'one2m'){
						if(empty($linkInfo['link_table']))
							$linkInfo['link_table'] = $linkInfo['table'];
					}
					elseif($rel == 'ver'){
						if(empty($linkInfo['ver_field']))
							$linkInfo['ver_field'] = 'ver';
					}
					elseif($rel == 'treeview'){
						if(empty($linkInfo['tree_table'])){
							preg_match('/^(.*)_node$/', $linkInfo['table'], $matches);
							if(!empty($matches))
								$node = $matches[1];
							else	
								$node = $linkInfo['table'];
							$linkInfo['tree_table'] = $node.'_tree';
						}
					}
					$this->options['linkTables'][$rel]["{$linkInfo['db']}.{$linkInfo['table']}"] = $linkInfo;
				}
			}
// print_r("db ={$this->options['db']}, table = {$this->options['table']}\n");		
// print_r($this->options['linkTables']);
		}
	}

	public function getLinkTables(){
		$this->standardLinkTable();
		return empty($this->options['linkTables']) ? array() : $this->options['linkTables'];
	}
	
	public function getCaption(){
		if (empty($this->options['caption']))
			$this->options['caption'] = isset($this->options['gridOptions']['label']) ? $this->options['gridOptions']['label'] : ucfirst($this->get('table'));
		return $this->options['caption'];
	}
	
	public function getOptions($trimed = true, $params = array()){
// $this->tool->p_t("Before table_desc getOptions");
// print_r($params);
// print_r($this->options);	
		$this->config($trimed, $params);
// print_r($this->options);
// $this->tool->p_t("Before table_desc getOptions");
		return $this->options;
	}

	//可以根据当前的Action进行不同的配置
	protected function config($trimed = true, $params){
		if (!empty($this->configed[$this->actionName]))
			return;
			
// print_r($this->actionName);			
		switch($this->actionName){
			case 'index':
			case 'getGridOptions':
				break;
		}
		$this->options['contextMenuItems'] = $this->contextMenu();
		$this->standardLinkTable();
		if (empty($this->options['label']))
			$this->options['label'] = ucfirst($this->options['table']);
		if (empty($this->options['list']))
			$this->options['list'] = '*';
		$this->options['list'] = $this->standardColumns($this->options['list']);
        $displayField = $this->getDisplayField();
		
		$this->options['list'] = $this->getListFields($params, $displayField);
		$this->options['edit'] = $this->getEditFields($params, $displayField);
		$this->options['add'] = $this->getAddFields($params, $displayField);
// print_r($this->options);		
		$this->options['queryValue'] = array();
		if (!empty($this->options['query'])){
			$this->options['query']['normal'][] = '__interTag';
			$this->getQueryFields($params, $displayField);
		}
		elseif (!empty($this->options['listTags'])){
			$this->options['tags'] = $this->fetch_tags();
		}			
		foreach($this->options['list'] as $field=>$model)
			$this->options['gridOptions']['colModel'][] = $model;
		// $this->options['colModel'] = $this->colModels;
		
		if ($trimed)	// 调整Column的前后次序
			$this->options['gridOptions']['colModel'] = $this->trimColModel($this->options['gridOptions']['colModel']);
		$this->options['gridOptions']['colModelMap'] = $this->colModelMap;
		$this->options['buttons'] = $this->getButtons();

		//权限检查，裁剪Button
		if(!empty($this->action)){
			$this->options['buttons'] = $this->action->trimButtons($this->options['buttons']);
			if(!empty($this->options['query']['buttons'])){
				$this->options['query']['buttons'] = $this->action->trimButtons($this->options['query']['buttons']);
			}
		}

		$this->configed[$this->actionName] = true;
	}
			
    protected function contextMenu(){
		return array();
        // $menu = array();
        // $menu['information'] = 'information';
        // $menu['export'] = 'export';
        // return $menu;
    }
	
	protected function getButtons(){
		$buttons = array();
		$buttons['add'] = array();
		if (count($this->options['gridOptions']['colModel']) > 5)
			$buttons['columns'] = array(
				'caption'=>'Columns',
                'buttonimg'=>'',
                'title'=>'Show/Hide Columns',
            );
		$buttons['subscribe'] = array('caption'=>'Subscribe', 'buttonimg'=>'', 'title'=>'Subscribe the selected records');
        $buttons['export'] = array(
			'caption'=>'Export',
			'buttonimg'=>'',
			'title'=>'Export for selected records',
		);
		// if(isset($this->options['tags'])){
		if($this->tool->tableExist('tag', $this->options['db'])){
			$buttons['tag'] = array(
				'caption'=>'Tag',
				'buttonimg'=>'',
				'title'=>'Create a Tag for selected records',
			);
			$buttons['removeFromTag'] = array(
				'caption'=>'Remove From Tag',
				'buttonimg'=>'',
				'title'=>'Remove selected records From This Tag',
			);
		}
		// check the special fields
		foreach($this->options['gridOptions']['colModel'] as $model){
			switch($model['name']){
				case 'used_by_id':
					$buttons['lend'] = array('caption'=>'Lend To', 'buttonimg'=>'', 'title'=>'Lend the selected items');
					break;
				case 'owner_id':
					$buttons['change_owner'] = array('caption'=>'Change Owner', 'buttonimg'=>'', 'title'=>'Change the owner for the selected items');
					break;
				case 'isactive':
					$buttons['activate'] = array('caption'=>'Activate', 'buttonimg'=>'', 'title'=>'Activate the selected items');
					$buttons['inactivate'] = array('caption'=>'Inactivate', 'buttonimg'=>'', 'title'=>'Inactivate the selected items');
					break;
			}
		}
		//检查表名是否*_ver或*_history，如果是，则默认添加ver_diff
		if(stripos($this->get('real_table'), '_ver') !== false){//} || stripos($this->get('real_table'), '_history') !== false){
			$buttons['ver_diff'] = array('caption'=>'Diff the versions');
		}
		if(stripos($this->get('real_table'), '_history') !== false){
			$buttons['his_diff'] = array('caption'=>'Diff the history');
		}		
		
        return $buttons;
	}
			
	public function fetch_tags(){
		$tags = array();
		if($this->tool->tableExist('tag', $this->options['db'])){
			$userList = $this->userAdmin->getUserList();
			$userList[0] = 'Unknown';
			$base_sql = "SELECT id, name, creater_id FROM tag ".
				" WHERE `db_table`='{$this->options['db']}.{$this->options['table']}'";
			if(!empty($this->userInfo->id)){
				$sql = $base_sql . " and creater_id={$this->userInfo->id} ORDER BY name ASC";
				$res = $this->tool->query($sql);
				while($row = $res->fetch()){
		// print_r($row);		
					$row['name'] = $row['name'].' (--By '.$userList[$row['creater_id']].')';
					$tags[] = $row;
				}
			}
			$sql = $base_sql . " and `public`=1";
			if (!empty($this->userInfo->id))
				$sql .= " AND creater_id!={$this->userInfo->id}";
			$sql .= " ORDER BY name ASC";
			$res = $this->tool->query($sql);
			while($row = $res->fetch()){
	//print_r($row);		
				$row['name'] = $row['name'].' (--By '.$userList[$row['creater_id']].')';
				$tags[] = $row;
			}
		}
		return $tags;
	}
	
	//确定自己的最大范围，返回FALSE表示没有限制
	public function getLimit($field = '', $params = array()){
		if(empty($this->limited[$field])){
			$this->limited[$field] = true;
			$this->limit[$field] = $this->_getLimit($params);
		}
		return $this->limit[$field];
	}
	
	protected function _getLimit($params){
		return false;
	}
	
	protected function getFieldModel($field){
		$model = array();
		if (isset($this->colModelMap[$field])){
			$model = $this->colModels[$this->colModelMap[$field]];
		}
		return $model;
	}
	
	protected function getListFields($params){
		$this->options['list'] = $this->modelFields($this->options['list'], $params);
		return $this->options['list'];
	}
	
	protected function getQueryFields($params){
		$query = array();
		foreach($this->options['query'] as $k=>$v){ // options['query'] = array('buttons'=>$buttons, 'normal'=>$normal, 'advanced'=>$advanced)
			if ($k != 'buttons' && $k != 'cols'){
				$this->options['query'][$k] = $this->modelFields($v, $params);
				foreach($this->options['query'][$k] as $field=>&$prop){
					$prop['editrules']['required'] = false;
					$prop['unique'] = false;
				}
			}
		}
		return $this->options['query'];
	}
	
	protected function modelFields($fields, $params){
		$list = array();
		if (is_string($fields))
			$fields = explode(',', $fields);
		foreach($fields as $field=>$prop){
			if (is_int($field)){
				$field = $prop;
				$prop = array();
			}
			if (isset($this->colModelMap[$field])){
				$column = $this->tool->array_extends($this->colModels[$this->colModelMap[$field]], $prop);
			}
			else{
				$column = $this->singleColModel($field, $prop, $params);
				$current = count($this->colModelMap);
				$this->colModels[$current] = $column;
				$this->colModelMap[$field] = $current;
			}
			//如果params['searchConditions']里有关于该Column的限制，应加入
			if(!empty($this->params['condMap'])){
// print_r($this->params['condMap']);			
				if(!empty($this->params['condMap'][$field])){
					$column['defval'] = $column['editoptions']['defaultValue'] = $this->options['queryValue'][$field] = $this->params['condMap'][$field]['value'];
					$column['force_readonly'] = true;
				}
			}
			$list[$field] = $column;
		}
		return $list;
	}
	
	protected function getEditFields($params){
//print_r($this->options['edit']);	
		if (empty($this->options['edit']))
			$this->options['edit'] = array_keys($this->options['list']);
		$this->options['edit'] = $this->modelFields($this->options['edit'], $params);
		unset($this->options['edit']['id']);
		
		return $this->options['edit'];
	}
	
	protected function getAddFields($params){
		if (empty($this->options['add'])){
			if(!empty($this->options['edit']))
				$this->options['add'] = $this->options['edit'];
			else
				$this->options['add'] = array_keys($this->options['list']);
		}
		$this->options['add'] = $this->modelFields($this->options['add'], $params);
		unset($this->options['add']['id']);
		return $this->options['add'];
	}
	
    public function standardColumns($columns){
        if (is_string($columns))
            $columns = explode(',', $columns);
		$existFields = array();
		$star = false;
        foreach($columns as $key=>$existField){
            if (is_int($key))
				$key = $existField;
			if ($key !== '*')
				$existFields[] = $key;
			else
				$star = true;
        }
		$descFields = $this->tool->describe($this->options['table'], $this->options['db']);

        $allColumns = array();
        $descs = array();
		$default = array('LENGTH'=>20, 'DATA_TYPE'=>'text', 'sortable'=>false);
        foreach($columns as $key=>$column){
			if (is_int($key)){
				$key = $column;
				$column = array();//'COLUMN_NAME'=>$key, 'name'=>$key);
			}
			
			if ($key != '*'){
				if (isset($descFields[$key]))
					$allColumns[$key] = array_merge($descFields[$key], $column);
				else{
					$allColumns[$key] = array_merge($default, $column);
				}
			}
			else{
				foreach($descFields as $i=>$f){
					if (!in_array($i, $existFields)){
						$allColumns[$i] = array_merge($f, $column);
					}
				}
			}
        }
        return $allColumns;
    }
    
	protected function handleFillOptionCondition(){
		// if(!empty($this->params['searchConditions'])){
			// $searchConditions = $this->params['searchConditions'];
			// foreach($searchConditions as $condition){
				// switch($condition['field']){
					// case 'testcase_id':
						// $this->where_testcase_id = $condition;
						// break;
				// }
			// }
		// }
	}
	
	public function trimColModel($colModel){
		$trimed = array();
		$container = isset($this->params['container']) ? $this->params['container'] : 'mainContent';
        $params = array('user_id'=>$this->userInfo->id, 'name'=>$this->options['db'].'_'.$this->options['table']);
		$cookie = json_decode($this->userAdmin->getCookie($params));
		if (!empty($cookie->rowNum))
			$rowNum = json_decode($cookie->rowNum);
        if (isset($rowNum->rowNum))
            $this->options['gridOptions']['rowNum'] = $rowNum->rowNum;
		if (isset($cookie->display))
			$display = json_decode($cookie->display);
		else
			$display = new stdClass();
        $maxOrder = count((array)$display);
//print_r($display);		
		$notMatch = true;
        if ($maxOrder == count($colModel)){ // cookie exists
			$notMatch = false;
            $tmp = array();
            foreach($colModel as $key=>$columnDef){
                if (isset($display->$columnDef['name'])){
                    $columnDef['hidden'] = $display->$columnDef['name']->hidden;
                    $columnDef['width'] = $display->$columnDef['name']->width;
					$order = $display->$columnDef['name']->order;
					$tmp[$order] = $columnDef;
//                    $columnDef['order'] = $display->$columnDef['name']->order;
                }
                else{
					$notMatch = true;
					break;
//                    $columnDef['order'] = $maxOrder ++;
                }
//                $tmp[$columnDef['order']] = $columnDef;
            }
            // now reset the colmodels
//print_r($maxOrder);
//print_r($tmp);            
			if (!$notMatch){
				for($i = 0; $i < $maxOrder; $i ++){
					if (isset($tmp[$i]))
						$trimed[$i] = $tmp[$i];
					else{
						$notMatch = true;
						break;
					}
				}
			}
        }
		if ($notMatch)
            $trimed = $colModel;	
		return $trimed;
	}
	
	public function getDisplayField(){
		if (empty($this->options['displayField'])){
			$desc = $this->tool->describe($this->get('table'), $this->get('db'));
			$this->options['displayField'] = $this->tool->getDisplayField($desc);
		}
		return $this->options['displayField'];
	}
	
	public function getRowRole($table_name = '', $id = 0){
		$roles = array();
		$row = $this->getRowForRole($table_name = '', $id = 0);
		if(!empty($row)){
			$matrix = $this->getRowRoleMatrix($row);
			foreach($matrix as $field=>$role){
				if(isset($row[$field]) && $row[$field] == $this->userInfo->id){
					$roles[] = $role;
				}
			}
		}
		return $roles;
	}
	
	protected function getRowForRole($table_name = '', $id = 0){
		$data = array();
		if(!empty($this->params['id'])){
			$strID = $this->params['id'];
			if(is_array($this->params['id']))
				$strID = implode(',', $this->params['id']);
			$res = $this->tool->query("SELECT * FROM {$this->options['table']} WHERE id IN ($strID)");
			$data = $res->fetch();
		}
		return $data;
	}
	
	protected function getRowRoleMatrix($row){
		$matrix = array(
			'creater_id'=>'row_owner',
			'owner_id'=>'row_owner',
			'updater_id'=>'row_owner',
			'assistant_owner_id'=>'row_assistant_owner',
			'tester_id'=>'row_tester',
		);
		return $matrix;
	}
	
	public function roleAndStatus($table_name = '', $id = 0, $userId = 0, $fields = array()){
		$row = array();
		if (empty($table_name))
			$table_name = $this->get('table');
		if(!empty($id)){
			$res = $this->tool->query("SELECT * FROM $table_name WHERE id=$id");
			$row = $res->fetch();
		}

		$role = 'guest';
		if (empty($userId))
			$userId = $this->userInfo->id;
		if (!empty($userId)){
			$role = 'normal';
			if ($this->userAdmin->isAdmin($userId))
				$role = 'admin';
			else{
				if(isset($row)){
					if(isset($row['creater_id']) && $row['creater_id'] == $userId)
						$role = 'owner';
					else if(isset($row['assistant_owner_id']) && $row['assistant_owner_id'] == $userId)
						$role = 'owner';
					else if(isset($row['owner_id']) && $row['owner_id'] == $userId)
						$role = 'owner';
					else if(isset($row['tester_ids'])){
						$testers = explode(',', $row['tester_ids']);
						if (in_array($userId, $testers))
							$role = 'tester';
					}
					else if(isset($row['tester_id']) && $row['tester_id'] == $userId)
						$role ='tester';
				}
			}
		}
		$ret = array('role'=>$role);
		foreach($fields as $k=>$f){
			if (isset($row[$f]))
				$ret[$k] = $row[$f];
		}
		return $ret;
	}

	protected function singleColModel($key, $column, $params = array()){
		// print_r($this->options);
// print_r("key = $key, prop = ");
// if($key == 'pid')
// print_r($column);
		$displayField = $this->getDisplayField();
		$db = $this->options['db'];
		$table = $this->options['table'];
		if (empty($column['COLUMN_NAME']))
			$column['COLUMN_NAME'] = $key;
		else if(!isset($column['from']))
			$column['from'] = "$db.$table";
		if (empty($column['DATA_TYPE'])){
			$column['DATA_TYPE'] = 'varchar';
			$column['LENGTH'] = 20;
			// $column['editable'] = true;
			$column['IDENTITY'] = false;
		}
		// if(empty($column['type']))
			// $column['type'] = 'text';
		$columnDef = array(
			'hidedlg'=>false, 
			'hidden'=>false, 
			'sortable'=>true, 
			'length'=>isset($column['LENGTH']) ? $column['LENGTH'] : 20,
			'formatter'=>isset($column['formatter']) ? $column['formatter'] : '',               
			'formatoptions'=>array(),
			'formoptions'=>array(), //???
			'label'=>ucwords(isset($column['COLUMN_NAME']) ? str_replace('_', ' ', $column['COLUMN_NAME']) : ''),
			'name' =>$key, //$column['COLUMN_NAME'],
			'index'=>$key, //$column['COLUMN_NAME'],
			'defval'=>isset($column['DEFAULT']) ? $column['DEFAULT'] : '',
			'editable'=>isset($column['IDENTITY']) ? !$column['IDENTITY'] : false,
			'edittype'=>isset($column['edittype']) ? $column['edittype'] : '',
			'editoptions'=>array('defaultValue'=>isset($column['DEFAULT'])?$column['DEFAULT'] : ''),
			'addoptions'=>array('defaultValue'=>isset($column['DEFAULT'])?$column['DEFAULT'] : ''),
			'editrules'=>array('edithidden'=>true, 'required'=>isset($column['NULLABLE']) ? !$column['NULLABLE'] : false),
			'search'=>true,
			'stype'=>isset($column['stype']) ? $column['stype'] : 'text',
			'searchoptions'=>array('searchhidden'=>true),
			'data_source_db'=>isset($column['data_source_db']) ? $column['data_source_db'] : '',
			'data_source_table'=>isset($column['data_source_table']) ? $column['data_source_table'] : '',
			'search_field'=>isset($column['search_field']) ? $column['search_field'] : '',
		);
		$columnDef = $this->tool->array_extends($columnDef, $column);
		
// if($column['COLUMN_NAME'] == 'owner_id'){
// // print_r($columnDef);
// // print_r($column);
// }
		// if(empty($column['TABLE_NAME']))
			// $columnDef['view'] = false;
		switch($column['DATA_TYPE']){
			case 'char':
			case 'varchar':
				if(empty($columnDef['edittype']))
					$columnDef['edittype'] = 'text';
				
				if ($columnDef['edittype'] == 'text' && $column['LENGTH'] > 255){
					$columnDef['edittype'] = 'textarea';
					$columnDef['editoptions']['rows'] = 3;
				}
				break;
			case 'text':
				if(empty($columnDef['edittype'])){
					$columnDef['edittype'] = 'textarea';
					$columnDef['editoptions']['rows'] = 3;
				}
				break;
			case 'int':
			case 'bigint':
			case 'mediumint':
			case 'smallint':
				$columnDef['formatter'] = 'integer';
				$columnDef['sorttype'] = 'int';
				$columnDef['editrules']['int'] = true;
				break;
			case 'tinyint': // BOOL, checkbox, 1:TRUE, 2:FALSE
				$columnDef['edittype'] = $columnDef['stype'] = $columnDef['formatter'] = 'select';
				$columnDef['formatoptions'] = $columnDef['editoptions'] = array('value'=>array(0=>' ', BOOL_TRUE=>'TRUE', BOOL_FALSE=>'FALSE'));
				$columnDef['searchoptions'] = array('value'=>array(0=>' ', BOOL_TRUE=>'TRUE', BOOL_FALSE=>'FALSE'));
				break;
			case 'decimal':
			case 'float':
				$columnDef['formatter'] = 'number';
				$columnDef['editrules']['number'] = true;
				break;
			case 'date':
				$columnDef['width'] = 60;
				$columnDef['editrules']['date'] = true;
				$columnDef['sorttype'] = 'date';
				$columnDef['editoptions']['dataInit'] = $columnDef['addoptions']['dataInit'] = $columnDef['searchoptions']['dataInit'] = 'XT.datePick';
				$columnDef['editoptions']['defval'] = $columnDef['defval'] = date('Y-m-d');
//					$columnDef['searchoptions']['dataInit']['attr']['title'] = 'Select Date';
				break;
				
			case 'timestamp':
				$columnDef['editable'] = false;
			case 'datetime':
				$columnDef['width'] = 100;
				break;
		}
		if(isset($column['COLUMN_NAME'])){
			switch(strtolower($column['COLUMN_NAME'])){
				case 'id':
					$columnDef['width'] = 40;
					$columnDef['editable'] = false;
					$columnDef['key'] = true;
					if ($displayField != 'id')
						$columnDef['hidden'] = true;
					$columnDef['limit'] = $this->getLimit();
					break;
					
				case 'isactive':
					$columnDef['edittype'] = $columnDef['stype'] = $columnDef['formatter'] = 'select';
					$columnDef['editoptions']['defval'] = 'Active'; //$columnDef['searchoptions']['defval'] = 'Active';
					$columnDef['editoptions']['value'] = $columnDef['formatoptions']['value'] = array(1=>'Active', 2=>'Inactive');
					$columnDef['searchoptions']['value'] = array(0=>' ', 1=>'Active', 2=>'Inactive');
					$columnDef['label'] = 'Is Active';
					$columnDef['width'] = 50;
					$columnDef['editable'] = false;
					break;
					
				case 'email':
					$columnDef['editrules']['email'] = true;
					$columnDef['formatter'] = 'email';
					break;
					
				case 'created':
				case 'modified':
				case 'updated':
					$columnDef['hidden'] = true;
					//如果是_history，则不隐藏
					if(stripos($table, '_history') !== false){
						$columnDef['hidden'] = false;
					}
					$columnDef['editable'] = false;
					$columnDef['editrules']['required'] = false;
					break;
					
				case 'gender':
					$columnDef['edittype'] = $columnDef['stype'] = $columnDef['formatter'] = 'select';
					$columnDef['editoptions']['defval'] = 'Male';
					$columnDef['editoptions']['value'] = $columnDef['formatoptions']['value'] = array(0=>' ', 1=>'Male', 2=>'Female');
					$columnDef['searchoptions']['value'] = array(0=>' ', 1=>'Male', 2=>'Female');
					$columnDef['label'] = 'Gender';
					break;
					
				case 'ver':
					$columnDef['label'] = 'Version';
					$columnDef['formatter'] = 'updateViewEditPage';
					$columnDef['editable'] = false;
					break;
					
				case 'file_name':
					$columnDef['formatter'] = 'downloadLink';
					break;
					
				case '__intertag':
					$columnDef['label'] = 'Tag';
					$columnDef['edittype'] = $columnDef['stype'] = 'select';
					$tags = $this->fetch_tags($db, $table);
					$tagOptions = array(0=>'==Select Tag==');
					foreach($tags as $tag)
						$tagOptions[$tag['id']] = $tag['name'];
					$columnDef['editoptions']['value'] = $columnDef['searchoptions']['value'] = $this->tool->array2Str($tagOptions);
					break;
					
				default:
					if(isset($column['formatter']) && ($column['formatter'] == 'multi_row_edit' || $column['formatter'] == 'embed_table')){
						$optionDb = $db;
						$optionTable = $key;
						if(!empty($column['data_source_db']))
							$optionDb = $column['data_source_db'];
						if(!empty($column['data_source_table']))
							$optionTable = $column['data_source_table'];
// if($column['COLUMN_NAME'] == 'hb_contact_method'){
// print_r($column);
// }
						$columhDef['search'] = false;
						$columnDef['data_source_db'] = $optionDb;
						$columnDef['data_source_table'] = $optionTable;
						// subitem table
						$itemParams = isset($column['itemParams']) ? $column['itemParams'] : array('id'=>isset($params['id']) ? $params['id'] : 0);
						if($column['formatter'] == 'multi_row_edit')
							$multiRowEdit = $this->tool->getMultiRowEditTemplate($optionDb, $optionTable, array(), $itemParams, array($table.'_id'));
						else
							$multiRowEdit = $this->tool->embed_table($optionDb, $optionTable, array(), $itemParams, array($table.'_id'));
							
						$columnDef = array_merge($columnDef, $multiRowEdit);
						$columnDef['editable'] = true;
						//
						if(empty($column['from'])){
							$index = $optionDb.'.'.$optionTable;
							if(!empty($this->options['linkTables']['one2m'][$index]))
								$column['from'] = $optionDb.'.'.$optionTable;
						}
						
// print_r($multiRowEdit['temp'])						;
					}
					elseif (preg_match('/^(.+)_(ids?|items)$/i', $column['COLUMN_NAME'], $matches)){
//print_r($matches);					
						$optionDb = $this->get('db');
						$optionTable = $matches[1];
						switch($matches[1]){
							case 'creater':
							case 'creator':
							case 'updater':
								$columnDef['editable'] = false;
							case 'tester':
							case 'assistant_owner':
							case 'testor':
							case 'used_by':
							case 'owner':
							case 'executer':
							case 'controller':
							case 'manager':
								$useradmin = useradminFactory::get();
								$userTable = explode('.', $useradmin->getUserTable());
// print_r($userTable);
								$optionDb = $userTable[0];//'useradmin';
								$optionTable = $userTable[1];//'users';
								$userInfo = $useradmin->getUserInfo();
								$columnDef['defval'] = $columnDef['editoptions']['defaultValue'] = $userInfo->id;
								break;
							case 'groups':
							case 'group':
								$useradmin = useradminFactory::get();
								$userTable = explode('.', $useradmin->getUserTable());
								
								$optionDb = $userTable[0];//'useradmin';
// print_r("optionDb = $optionDb, table = $optionTable\n");								
								// $optionDb = 'useradmin';
								break;
						}
						if(!empty($column['data_source_db']))
							$optionDb = $column['data_source_db'];
						if(!empty($column['data_source_table']))
							$optionTable = $column['data_source_table'];
						$columnDef['data_source_db'] = $optionDb;
						$columnDef['data_source_table'] = $optionTable;
						if(!empty($column['data_source_sql']))$columnDef['data_source_sql'] = $column['data_source_sql'];
						if(!empty($column['data_source_condition']))$columnDef['data_source_condition'] = $column['data_source_condition'];
						$columnDef['data_source_blank_item'] = isset($column['data_source_blank_item']) ? $column['data_source_blank_item'] : false;
						$columnDef['data_source_all_fields'] = isset($column['data_source_all_fields']) ? $column['data_source_all_fields'] : true;
						$columnDef['label'] = ucwords(str_replace('_', ' ', $matches[1]));
						if(empty($column['formatter'])){
							$columnDef['edittype'] = $columnDef['stype'] = 'select';
							$columnDef['formatter'] = 'select';//'select_showlink';
							$columnDef['formatoptions'] = array(
								'baseLinkUrl'=>'/jqgrid/jqgrid/newpage/1/oper/information/db/'.$db.'/table/'.$matches[1],
								'target'=>'blank',
								'db'=>$optionDb,
								'table'=>$optionTable);
						}
						if ($matches[2] == 'ids'){
							if(empty($column['formatter'])){
								$columnDef['formatter'] = 'ids';
								// $columnDef['edittype'] = 'select';
								$columnDef['editoptions']['multiple'] = $columnDef['addoptions']['multiple'] = $columnDef['formatoptions']['multiple'] = 'true';
								$columnDef['editoptions']['size'] = $columnDef['addoptions']['size'] = 5;
							}
						}
						
// print_r("optionDb = $optionDb, optionTable = $optionTable\n");
						// if (empty($column['formatter']) || $column['formatter'] == 'select' || $column['formatter'] == 'ids' || $column['formatter'] == 'select_showlink'){
							// if ($this->tool->tableExist($optionTable, $optionDb)){
								// $columnDef['edittype'] = $columnDef['stype'] = $columnDef['formatter'] = 'select';
								// $columnDef['formatter'] = 'select_showlink';
								// $columnDef['formatoptions'] = array(
									// 'baseLinkUrl'=>'/jqgrid/jqgrid/newpage/1/oper/information/db/'.$db.'/table/'.$matches[1],
									// 'target'=>'blank',
									// 'db'=>$optionDb,
									// 'table'=>$optionTable);
								// $t = tableDescFactory::get($optionDb, $optionTable, array());
								// $columnDef['limit'] = $t->getLimit();
								// if(!isset($this->params['fill']) || $this->params['fill'] == true)
									// $this->tool->fillOptions($columnDef);
								// else
									// $columnDef['notfill'] = true;
// // print_r($columnDef);									
							// }
						// }
						// if ($matches[2] == 'ids'){
							// if(empty($column['formatter']) || $column['formatter'] != 'multi_row_edit'){
								// $columnDef['formatter'] = 'ids';
								// if (!empty($columnDef['edittype']) && $columnDef['edittype'] == 'select'){
									// $columnDef['editoptions']['multiple'] = $columnDef['addoptions']['multiple'] = $columnDef['formatoptions']['multiple'] = 'true';
									// $columnDef['editoptions']['size'] = $columnDef['addoptions']['size'] = 5;
	// //print_r($columnDef);				
								// }
							// }
						// }
						//看看是否存在m2m关系，依次设置from属性
						if(empty($column['from'])){
// print_r($matches);						
							if(!empty($this->options['linkTables']['m2m']["$optionDb.{$matches[1]}"])){
								$link = $this->options['linkTables']['m2m']["$optionDb.{$matches[1]}"];
// print_r($link);								
								$columnDef['from'] = $link['db'].'.'.$link['table'];
								$columnDef['formatter'] = 'ids';
								$columnDef['search'] = true;
								if (!empty($columnDef['edittype']) && $columnDef['edittype'] == 'select'){
									$columnDef['editoptions']['multiple'] = $columnDef['addoptions']['multiple'] = $columnDef['formatoptions']['multiple'] = 'true';
									$columnDef['editoptions']['size'] = $columnDef['addoptions']['size'] = 5;
								}
							}
							elseif(!empty($this->options['linkTables']['node_ver_m2m']["$optionDb.{$matches[1]}"])){
								$link = $this->options['linkTables']['node_ver_m2m']["$optionDb.{$matches[1]}"];
								$columnDef['from'] = $link['db'].'.'.$link['table'];
								$columnDef['formatter'] = 'ids';
								$columnDef['search'] = true;
							}
						}
						if(!empty($column['type']) && $column['type'] == 'cart'){
							$columnDef['cart_db'] = $optionDb;
							$columnDef['cart_table'] = $matches[1];
						}
					}
					break;
			}
			if(!empty($columnDef['data_source_table'])){
				if(empty($columnDef['data_source_db']))
					$columnDef['data_source_db'] = $db;
				if($this->tool->tableExist($columnDef['data_source_table'], $columnDef['data_source_db'])){
					$t = tableDescFactory::get($columnDef['data_source_db'], $columnDef['data_source_table'], array());
					$columnDef['limit'] = $t->getLimit();
// if($column['COLUMN_NAME'] == 'period_id')					
// print_r($columnDef);
					if(!isset($this->params['fill']) || $this->params['fill'] == true)
						$this->tool->fillOptions($columnDef);
					else
						$columnDef['notfill'] = true;
				}
			}
			
			if ($column['COLUMN_NAME'] == $displayField){
				$columnDef['displayField'] = true;
				$columnDef['formatter'] = isset($this->options['linktype']) ? $this->options['linktype'] : 'infoLink_dialog';
				// display field 应该是唯一的，在输入后应进行检查
				if (!isset($columnDef['unique']) && ($displayField == 'code' || $displayField == 'name' || $displayField == 'username'))
					$columnDef['unique'] = true;
			}
		}
		
// if($key == 'pid')
// print_r($columnDef);
		$from = '';
		if(!empty($column['from'])){
			$from = $column['from'];
		}
		elseif(!empty($columnDef['from']))
			$from = $columnDef['from'];
		if(!empty($from)){
			$tmp = explode('.', $from);
			if(count($tmp) == 1){
				$column['from'] = $db.'.'.$tmp[0];
			}
		}
		$columnDef = $this->tool->array_extends($columnDef, $column);
		if (!$columnDef['editable'])
			$columnDef['editrules']['required'] = false;
		if ($columnDef['editrules']['required']){
			$columnDef['classes'] = 'required';
			$columnDef['formoptions']['elmsuffix'] = '(*)';
		}	
// if($key == 'groups_id')
// print_r($columnDef);
		return $columnDef;
	}
		
}

?>