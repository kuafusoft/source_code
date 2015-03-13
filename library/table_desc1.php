<?php
require_once('dbfactory.php');
require_once('toolfactory.php');
require_once('useradminfactory.php');

class table_desc{
	protected $db = null;
	protected $configed = false;
	protected $params = array();
	protected $options = array();
	protected $tool = null;
	protected $tool_name = 'db';
	protected $colModelMap = array();
	protected $userAdmin = null;
	protected $userInfo = null;
	protected $caption = '';
	protected $listTags = true;
	
	protected $parent_table = '';		//包容关系？
	protected $parent_field = '';
	
	//以下的各种表间关系可能组合出现，所以不适合用继承来实现
	protected $standardLinkTabled = false;
	protected $linkTables = array();	//对多对关系
	
	// for colmodle's editoptions: array(field=>array('conditions'=>array(), 'blankItem'=>true/false, 'allfields'=>true/false, 'sql'=>'select * '));
	protected $selectOptions = array();
	
	protected $fillOptionConditions = array();	// 对于传入的params，分析如何过滤可选项
	protected $blankItems = array();	// 哪些字段需要==BLANK==选项
	protected $allFields = array();		//哪些字段需要得到全部信息
	
	public function __construct($db, $table, $params = array()){
		$this->init($db, $table, $params);
	}
	
	public function get($name){
		return isset($this->options[$name]) ? $this->options[$name] : null;
	}
	
	public function getParentInfo(){
		return array('table'=>$this->parent_table, 'field'=>$this->parent_field);
	}
	
	protected function init($db, $table, $params = array()){
		$this->params = $params;
		$this->options['db'] = $db;
		$this->options['table'] = $this->options['real_table'] = $table;
		$this->db = dbFactory::get($db, $realDbName);
		// $this->options['db'] = $realDbName;
		$this->userAdmin = useradminFactory::get();
		// $this->userAdmin = new Application_Model_Useradmin(null);
		$this->userInfo = $this->userAdmin->getUserInfo();
// print_r($this->userInfo);		
		$this->tool = toolFactory::get(array('tool'=>$this->tool_name, 'db'=>$this->options['db'], 'table'=>$this->options['table']));
		$this->tool->setDb($this->options['db']);
		
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
	}

	public function accessMatrix(){
		$matrix = array('guest'=>array('all'=>false)); // 默认guest不允许任何操作
		$access_file = APPLICATION_PATH."/jqgrid/{$this->options['db']}/{$this->options['table']}/access.xml";
		if(file_exists($access_file)){
			$access = new Zend_Config_Xml($access_file);
			$access = $access->toArray();
			$matrix = array_merge($matrix, $access);
// print_r($matrix);
		}
		return $matrix;
	}
	
	protected function standardLinkTable(){
		if(!$this->standardLinkTabled){
			$this->standardLinkTabled = true;
			if(!empty($this->linkTables)){
				foreach($this->linkTables as $linkTable=>$linkInfo){
					if(is_int($linkTable)){
						$tmp = $linkTable;
						$linkTable = $linkInfo;
						$linkInfo = array();
						unset($this->linkTables[$tmp]);
					}
					$linkDb = $this->get('db');
					if(empty($linkInfo['link_table'])){
						//linkTable可能的格式是db.table
						$tmp = explode(".", $linkTable);
						if(count($tmp) == 2){
							$linkTable = $tmp[1];
							$linkDb = $tmp[0];
						}
						$tables = array($this->get('table'), $linkTable);
						sort($tables);
						$linkInfo['link_table'] = implode('_', $tables);
					}
					if(empty($linkInfo['link_db']))
						$linkInfo['link_db'] = $linkDb;
					if(empty($linkInfo['self_link_field']))
						$linkInfo['self_link_field'] = $this->get('table').'_id';
					if(empty($linkInfo['link_field']))
						$linkInfo['link_field'] = $linkTable.'_id';
					if(empty($linkInfo['refer_table']))
						$linkInfo['refer_table'] = $linkTable;
					$this->linkTables[$linkTable] = $linkInfo;
				}
			}
		}
// print_r($this->linkTables);
	}
	
	public function getLinkTables(){
		$this->standardLinkTable();
		return $this->linkTables;
	}
	
	public function getCaption(){
		if (empty($this->options['caption']))
			$this->options['caption'] = isset($this->options['gridOptions']['label']) ? $this->options['gridOptions']['label'] : ucfirst($this->get('table'));
		return $this->options['caption'];
	}
	
	public function getOptions($trimed = true, $params = array()){
//print_r($params);	
		$this->config($trimed, $params);
		return $this->options;
	}

	protected function getTool(){
		return $this->tool;
	}
	
	protected function config($trimed = true, $params){
		if ($this->configed)
			return;
		$this->getTool();
		$this->standardLinkTable();
		if (empty($this->options['label']))
			$this->options['label'] = ucfirst($this->options['table']);
		if (empty($this->options['list']))
			$this->options['list'] = '*';
		$this->options['gridOptions']['colModel'] = $this->getColModel($params);
// print_r($this->options['list']);		
		$this->options['edit'] = $this->getEditFields();
//		$this->options['add'] = $this->getAddFields();
		$this->options['queryValue'] = array();
		if (!empty($this->options['query'])){
			$this->options['query']['normal'][] = '__interTag';
			$this->getQueryFields();
		}
		elseif ($this->listTags && $this->tool->tableExist('tag', $this->options['db'])){
			$this->options['tags'] = $this->fetch_tags();
		}			
		$this->options['buttons'] = $this->getButtons();
		$this->options['contextMenuItems'] = $this->contextMenu();
		if ($trimed)	// 调整Column的前后次序
			$this->options['gridOptions']['colModel'] = $this->trimColModel($this->options['gridOptions']['colModel']);
		$this->options['gridOptions']['colModelMap'] = $this->colModelMap;
		$this->configed = true;
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
        return $buttons;
	}
			
	public function fetch_tags(){
		$userList = $this->userAdmin->getUserList();
		$userList[0] = 'Unknown';
		$tags = array();
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
		return $tags;
	}
	
	protected function getListFields(){
		if (empty($this->options['list']))
			$this->options['list'] = '*';
		$this->options['list'] = $this->modelFields($this->options['list']);
		return $this->options['list'];
	}
	
	protected function getQueryFields(){
		$query = array();
		foreach($this->options['query'] as $k=>$v){ // options['query'] = array('buttons'=>$buttons, 'normal'=>$normal, 'advanced'=>$advanced)
			if ($k != 'buttons' && $k != 'cols'){
				$this->options['query'][$k] = $this->modelFields($v);
				foreach($this->options['query'][$k] as $field=>&$prop){
					$prop['editrules']['required'] = false;
					$prop['unique'] = false;
				}
			}
		}
		return $this->options['query'];
	}
	
	protected function modelFields($fields){
		$list = array();
		if (is_string($fields))
			$fields = explode(',', $fields);
		foreach($fields as $field=>$prop){
			if (is_int($field)){
				$field = $prop;
				$prop = array();
			}
			if (isset($this->colModelMap[$field])){
				$column = $this->tool->array_extends($this->options['gridOptions']['colModel'][$this->colModelMap[$field]], $prop);
			}
			else{
				$column = $this->singleColModel($field, $prop);
			}
			//如果params['searchConditions']里有关于该Column的限制，应加入
			if(!empty($this->params['condMap'])){
				if(!empty($this->params['condMap'][$field])){
					$column['defval'] = $column['editoptions']['defaultValue'] = $this->options['queryValue'][$field] = $this->params['condMap'][$field]['value'];
					$column['force_readonly'] = true;
				}
			}
			$list[$field] = $column;
		}
		return $list;
	}
	
	protected function getEditFields(){
//print_r($this->options['edit']);	
		if (empty($this->options['edit']))
			$this->options['edit'] = array_keys($this->options['list']);
		$this->options['edit'] = $this->modelFields($this->options['edit']);
		return $this->options['edit'];
	}
	
	protected function getAddFields(){
		if (empty($this->options['add']))
			$this->options['add'] = array_keys($this->options['list']);
		$this->options['add'] = $this->modelFields($this->options['add']);
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
		$default = array('LENGTH'=>20, 'DATA_TYPE'=>'text', 'editable'=>false, 'search'=>true, 'sortable'=>false);
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
    
	protected function singleColModel($key, $column, $displayField = '', $params = array()){
		if (empty($column['COLUMN_NAME']))
			$column['COLUMN_NAME'] = $key;
		if (empty($column['DATA_TYPE'])){
			$column['DATA_TYPE'] = 'varchar';
			$column['LENGTH'] = 20;
			// $column['editable'] = true;
			$column['IDENTITY'] = false;
		}
		if(!isset($column['editable'])){
			$column['editable'] = isset($column['IDENTITY']) ? !$column['IDENTITY'] : true;
		}
		$columnDef = array(
			'hidedlg'=>false, 
			'hidden'=>false, 
			'sortable'=>true, 
			'length'=>isset($column['LENGTH']) ? $column['LENGTH'] : 20,
			'formatter'=>'',                 
			'formatoptions'=>array(),
			'formoptions'=>array(), //???
			'label'=>ucwords(isset($column['COLUMN_NAME']) ? $column['COLUMN_NAME'] : ''),
			'name' =>$key, //$column['COLUMN_NAME'],
			'index'=>$key, //$column['COLUMN_NAME'],
			'defval'=>isset($column['DEFAULT']) ? $column['DEFAULT'] : '',
			'editable'=>isset($column['IDENTITY']) ? !$column['IDENTITY'] : false,
			'edittype'=>isset($column['edittype']) ? $column['edittype'] : 'text',
			'editoptions'=>array('defaultValue'=>isset($column['DEFAULT'])?$column['DEFAULT'] : ''),
			'editrules'=>array('edithidden'=>true, 'required'=>isset($column['NULLABLE']) ? !$column['NULLABLE'] : false),
			'search'=>true,
			'stype'=>'text',
			'searchoptions'=>array('searchhidden'=>true),
		);
		// if(empty($column['TABLE_NAME']))
			// $columnDef['view'] = false;
		switch($column['DATA_TYPE']){
			case 'char':
			case 'varchar':
				$columnDef['edittype'] = 'text';
				if ($column['LENGTH'] > 255){
					$columnDef['edittype'] = 'textarea';
					$columnDef['editoptions']['rows'] = 5;
				}
				break;
			case 'text':
				$columnDef['edittype'] = 'textarea';
				$columnDef['editoptions']['rows'] = 5;
				break;
			case 'int':
			case 'bigint':
			case 'mediumint':
			case 'smallint':
				$columnDef['formatter'] = 'integer';
				$columnDef['sorttype'] = 'int';
				$columnDef['editrules']['int'] = true;
				$columnDef['searchoptions']['sopt'] = array('eq','ne','bw','bn','in','ni','ew','en','cn','nc');
				break;
			case 'tinyint': // BOOL, checkbox, 1:TRUE, 2:FALSE
				$columnDef['edittype'] = $columnDef['stype'] = $columnDef['formatter'] = 'select';
				$columnDef['formatoptions'] = $columnDef['editoptions'] = array('value'=>array(BOOL_TRUE=>'TRUE', BOOL_FALSE=>'FALSE'));
				$columnDef['searchoptions'] = array('value'=>array(0=>' ', BOOL_TRUE=>'TRUE', BOOL_FALSE=>'FALSE'));
				break;
			case 'decimal':
			case 'float':
				$columnDef['formatter'] = 'number';
				$columnDef['editrules']['number'] = true;
				$columnDef['searchoptions']['sopt'] = array('eq','ne','bw','bn','in','ni','ew','en','cn','nc');
				break;
			case 'date':
				$columnDef['width'] = 60;
				$columnDef['editrules']['date'] = true;
				$columnDef['sorttype'] = 'date';
				$columnDef['searchoptions']['sopt'] = array('eq','ne','lt','le','gt','ge');
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
					$columnDef['editable'] = false;
					$columnDef['editrules']['required'] = false;
					break;
					
				case 'gender':
					$columnDef['edittype'] = $columnDef['stype'] = $columnDef['formatter'] = 'select';
					$columnDef['editoptions']['defval'] = 'Male'; //$columnDef['searchoptions']['defval'] = 'Active';
					$columnDef['editoptions']['value'] = $columnDef['formatoptions']['value'] = array(1=>'Male', 2=>'Female');
					$columnDef['searchoptions']['value'] = array(0=>' ', 1=>'Male', 2=>'Female');
					$columnDef['label'] = 'Gender';
					break;
					
				case 'file_name':
					$columnDef['formatter'] = 'downloadLink';
					break;
					
				case '__intertag':
					$columnDef['label'] = 'Tag';
					$columnDef['edittype'] = $columnDef['stype'] = 'select';
					$tags = $this->fetch_tags();
					$tagOptions = array(0=>'=Without Tag=');
					foreach($tags as $tag)
						$tagOptions[$tag['id']] = $tag['name'];
					$columnDef['editoptions']['value'] = $columnDef['searchoptions']['value'] = $this->tool->array2Str($tagOptions);
					break;
					
				default:
					if(isset($column['formatter']) && ($column['formatter'] == 'multi_row_edit' || $column['formatter'] == 'embed_table')){
						$optionDb = $this->get('db');
						$optionTable = $key;
						if(!empty($column['data_source_db']))
							$optionDb = $column['data_source_db'];
						if(!empty($column['data_source_table']))
							$optionTable = $column['data_source_table'];
// if($column['COLUMN_NAME'] == 'hb_contact_method'){
// print_r($column);
// }
						// if(!isset($column['search'])){
							$column['search'] = false;
							$columhDef['search'] = false;
						// }
						$columnDef['data_source_db'] = $optionDb;
						$columnDef['data_source_table'] = $optionTable;
						// subitem table
						$itemParams = isset($column['itemParams']) ? $column['itemParams'] : array('id'=>isset($params['id']) ? $params['id'] : 0);
						if($column['formatter'] == 'multi_row_edit')
							$multiRowEdit = $this->tool->getMultiRowEditTemplate($optionDb, $optionTable, array(), $itemParams, array($this->get('table').'_id'));
						else
							$multiRowEdit = $this->tool->embed_table($optionDb, $optionTable, array(), $itemParams, array($this->get('table').'_id'));
							
						$columnDef = array_merge($columnDef, $multiRowEdit);
						$column['editable'] = true;
// print_r($multiRowEdit)						;
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
								$optionDb = 'useradmin';
								$optionTable = 'users';
								$columnDef['defval'] = $this->userInfo->id;
								$columnDef['editoptions']['defaultValue'] = $this->userInfo->id;
								break;
							case 'groups':
								$optionDb = 'useradmin';
								break;
						}
						if(!empty($column['data_source_db']))
							$optionDb = $column['data_source_db'];
						if(!empty($column['data_source_table']))
							$optionTable = $column['data_source_table'];
						$columnDef['data_source_db'] = $optionDb;
						$columnDef['data_source_table'] = $optionTable;
						$columnDef['label'] = ucwords($matches[1]);
// print_r("optionDb = $optionDb, optionTable = $optionTable\n");
						if (empty($column['formatter']) || $column['formatter'] == 'select' || $column['formatter'] == 'ids'){
							if ($this->tool->tableExist($optionTable, $optionDb)){
								$columnDef['edittype'] = $columnDef['stype'] = $columnDef['formatter'] = 'select';
								$columnDef['formatter'] = 'select_showlink';
								$columnDef['formatoptions'] = array(
									'baseLinkUrl'=>'/jqgrid/jqgrid/newpage/1/oper/information/db/'.$this->options['db'].'/table/'.$matches[1],
									'target'=>'blank',
									'db'=>$optionDb,
									'table'=>$optionTable);
								$this->fillOptions($columnDef, $optionDb, $optionTable);
							}
						}
						if ($matches[2] == 'ids'){
							if(empty($column['formatter']) || $column['formatter'] != 'multi_row_edit'){
								$columnDef['formatter'] = 'ids';
								if (!empty($columnDef['edittype']) && $columnDef['edittype'] == 'select'){
									$columnDef['editoptions']['multiple'] = $columnDef['addoptions']['multiple'] = $columnDef['formatoptions']['multiple'] = 'true';
									$columnDef['editoptions']['size'] = $columnDef['addoptions']['size'] = 5;
	//print_r($columnDef);				
								}
							}
						}
					}
					break;
			}
			if ($column['COLUMN_NAME'] == $displayField){
				$columnDef['displayField'] = true;
				$columnDef['formatter'] = isset($this->options['linktype']) ? $this->options['linktype'] : 'infoLink_dialog';
				// display field 应该是唯一的，在输入后应进行检查
				if (!isset($columnDef['unique']) && ($displayField == 'code' || $displayField == 'name' || $displayField == 'username'))
					$columnDef['unique'] = true;
			}
		}
		$columnDef = $this->tool->array_extends($columnDef, $column);
		if (!$columnDef['editable'])
			$columnDef['editrules']['required'] = false;
		if ($columnDef['editrules']['required']){
			$columnDef['classes'] = 'required';
			$columnDef['formoptions']['elmsuffix'] = '(*)';
		}	
		return $columnDef;
	}
	
	public function fillOptions(&$columnDef, $db, $table){
		$conditions = isset($this->fillOptionConditions[$columnDef['name']]) ? $this->fillOptionConditions[$columnDef['name']] : null;
		$blankItem = isset($this->blankItems[$columnDef['name']]) ? $this->blankItems[$columnDef['name']] : false;
		$allFields = isset($this->allFields[$columnDef['name']]) ? $this->allFields[$columnDef['name']] : false;
		$this->tool->fillOptions($db, $table, $columnDef, false, $conditions, $blankItem, $allFields);
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
	
	protected function getFieldValues($field, $table, $condition){
		$ret = array();
		$res = $this->tool->query("SELECT $field FROM $table Where $condition");
		while($row = $res->fetch()){
			if(!empty($row[$field]))
				$ret[] = $row[$field];
		}
		return $ret;
	}
	
	protected function getColModel($params){
		$colModels = array();
		$index = 0;
		$columns = $this->options['list'] = $this->standardColumns($this->options['list']);
        $displayField = $this->getDisplayField();
//print_r($columns);		
        foreach($columns as $key=>$column){
            $colModels[$index] = $this->singleColModel($key, $column, $displayField, $params);
			$this->colModelMap[$key] = $index;
			$index ++;
        }
        return $colModels;
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
			$this->getTool();
			$desc = $this->tool->describe($this->get('table'), $this->get('db'));
			$this->options['displayField'] = $this->tool->getDisplayField($desc);
		}
		return $this->options['displayField'];
	}
	
	protected function getSpecialFilters(){
		$special = array();
		$this->standardLinkTable();
		if(!empty($this->linkTables)){
			foreach($this->linkTables as $linkTable=>$linkInfo){
				$special[] = $linkTable.'_id';
				$special[] = $linkTable.'_ids';
			}
		}
		return $special;
	}
	
	protected function specialSql($special, &$ret){
// print_r($special);
// print_r($this->linkTables);
		if(!empty($special) && !empty($this->linkTables)){
			foreach($this->linkTables as $linkTable=>$linkInfo){
				foreach($special as $c){
					if(in_array($c['field'], array($linkTable.'_id', $linkTable.'_ids'))){
						$v = $c['value'];
						if(is_array($v))
							$v = implode(',', $v);
						$ret['main']['from'] .= " LEFT JOIN {$linkInfo['link_table']} ON {$this->get('table')}.id={$linkInfo['link_table']}.{$linkInfo['self_link_field']}";
						$ret['group'] = $this->get('table').'.id';
						$ret['where'] .= " AND {$linkInfo['link_table']}.{$linkInfo['link_field']} IN ($v)";
					}
				}
			}
		}
// print_r($ret)		;
	}
	
	public function calcSqlComponents($params, $limited = true){
// print_r($params);
		$specialFields = $this->getSpecialFilters();
// print_r($specialFields);
		$special = array();
		foreach($params['searchConditions'] as $k=>&$c){
			if (isset($c['field'])){
				if (in_array($c['field'], $specialFields) && !empty($c['value'])){
					$special[] = $c;
					unset($params['searchConditions'][$k]);
				}
				else{
					if (stripos($c['field'], '.') === false && $c['field'] != '__interTag')
						$c['field'] = $this->get('table').'.'.$c['field'];
				}
			}
		}
// print_r($params);		
		$params['table'] = $this->get('table');
		$components = $this->tool->calcSql($params, $limited);
// print_r($components);		
		$this->specialSql($special, $components);
		return $components;
	}
	
	public function getSql($components){
		$sql = "SELECT {$components['main']['fields']} FROM {$components['main']['from']} WHERE {$components['where']}";
		if (!empty($components['group']))
			$sql .= ' GROUP BY '.$components['group'];
		if (!empty($components['order']))
			$sql .= " ORDER BY {$components['order']}";
			
		if (!empty($components['limit']))
			$sql .= " LIMIT ".$components['limit'];
//print_r($sql);
		return $sql;
	}
	
		// $this->linkTables = array('testcase_type'=>array('link_table'=>'testcase_module_testcase_type', 'self_link_field'=>'testcase_module_id', 'link_field'=>'testcase_type_id'));
	public function getMoreInfoForRow($row){
		if(!empty($this->linkTables)){
			$row = $this->getLinkedTableInfoForRow($row);
		}
		return $row;
	}
	
	protected function getLinkedTableInfoForRow($row){
		// 如果field_list里有linktable_ids,则需要以下操作
// print_r($this->colModelMap);
		$this->standardLinkTable();
		foreach($this->linkTables as $linkTable=>$linkInfo){
// print_r($linkTable.'_ids');
			if(isset($this->colModelMap[$linkTable.'_ids'])){
				$colModel = $this->options['gridOptions']['colModel'][$this->colModelMap[$linkTable.'_ids']];
// print_r($colModel);				
				if($colModel['formatter'] == 'ids'){
					$res = $this->tool->query("SELECT GROUP_CONCAT({$linkInfo['link_field']}) as ids FROM `{$linkInfo['link_table']}` WHERE {$linkInfo['self_link_field']}={$row['id']}");
					$type_res = $res->fetch();
					$row[$linkTable.'_ids'] = $type_res['ids'];
				}
			}
			elseif(isset($this->colModelMap[$linkTable])){
				$colModel = $this->options['gridOptions']['colModel'][$this->colModelMap[$linkTable]];
// print_r($colModel);				
				if($colModel['formatter'] == 'multi_row_edit'){
					$res = $this->tool->query("SELECT * FROM `{$linkInfo['link_table']}` WHERE {$linkInfo['self_link_field']}={$row['id']}");
					$type_res = $res->fetchAll();
					$row[$linkTable] = $type_res;
				}
				else if ($colModel['formatter'] == 'embed_table'){
					$res = $this->tool->query("SELECT * FROM `{$linkInfo['link_table']}` WHERE {$linkInfo['self_link_field']}={$row['id']}");
					$type_res = $res->fetch();
					$row[$linkTable] = $type_res;
				}
			}
		}
// print_r($row);
		return $row;
	}
	
	public function paramsForViewEdit($view_params){
//print_r($view_params);	
		$options = $this->getOptions(true, $view_params);
		$node = array('model'=>$options['edit'], 'view_file'=>'simple_view.phtml', 'view_file_dir'=>'/jqgrid', 'editing'=>empty($view_params['id']));
		$v = array();
		if (!empty($view_params['id'])){
//print_r($view_params);			
			$sqls = $this->calcSqlComponents(array('table'=>$this->get('table'), 'searchConditions'=>array(array('field'=>"`{$this->get('table')}`.`id`", 'op'=>' = ', 'value'=>$view_params['id']))), false);
//print_r($sqls);
			$sql = $this->getSql($sqls);
// print_r($sql);
			$res = $this->tool->query($sql);
			$v = $this->getMoreInfoForRow($res->fetch());
//print_r($v);
		}
		else{// get the default value from the colModel
			foreach($node['model'] as &$each){
				if(isset($each['defval'])){
					if (!empty($view_params['parent']) && $this->parent_field == $each['index']){
						$v[$each['index']] = $view_params['parent'];
					}
					else
						$v[$each['index']] = $each['defval'];
				}
			}
		}
		$node['value'] = $v;
		$ms = 0;
		foreach($node['model'] as $k=>&$m){
			if (!empty($view_params['parent']) && $this->parent_field == $m['index']){
				$m['editable'] = false;
			}
			if ($m['name'] == 'id'){
				unset($node['model'][$k]);
				continue;
			}
			$ms ++;
		}
		if ($ms < 8)
			$cols = 1;
		else
			$cols = 2;
		$node['cols'] = 1;//$cols;
		$node['legend'] = $this->getCaption();
		return $node;
	}
	
	public function getRowRole($table_name = '', $id = 0){
// print_r($this->params)	;
		$userId = $this->userInfo->id;
		$row = array();
		if (empty($table_name))
			$table_name = $this->get('table');
		if(empty($id))
			$id = isset($this->params['id']) ? $this->params['id'] : 0;
		if(is_array($id))
			$id = implode(",", $id);
// print_r($id);
		$row = $this->getRoleRow($table_name, $id);

		$role = '';
		if (!empty($userId) && !empty($row)){
			if(isset($row['creater_id']) && $row['creater_id'] == $userId)
				$role = 'row_owner';
			else if(isset($row['owner_id']) && $row['owner_id'] == $userId)
				$role = 'row_owner';
			else if(isset($row['assistant_owner_id']) && $row['assistant_owner_id'] == $userId)
				$role = 'row_assistant_owner';
			// else if(isset($row['tester_ids'])){
				// $testers = explode(',', $row['tester_ids']);
				// if (in_array($userId, $testers))
					// $role = 'row_tester';
			// }
			else if(isset($row['tester_id']) && $row['tester_id'] == $userId)
				$role ='row_tester';
		}
		$this->params['role'] = $role;
		return $role;
	}
	
	protected function getRoleRow($table_name, $id){
		$row = array();
		if(!empty($id)){
			$res = $this->tool->query("SELECT * FROM $table_name WHERE id IN ($id)");
			$row = $res->fetch();
		}
		return $row;
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
}

?>