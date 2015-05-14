<?php
require_once('tool_kf.php');
require_once('dbfactory.php');

class tool_db extends tool_kf{
	protected $describes = array();
	protected $keys = array(); //存放生成Sql时的关键字搜索，主用用于界面高亮
	protected $db = null;
	protected $dbName = '';
	protected $inTransaction = false;

	public function setDb($db){
		$this->dbName = $db;
		$this->db = $this->getDb($db);
	}
	
	public function getDb($dbName = '', &$realDb = ''){
		$dbName = $this->getDbName($dbName);
		return dbFactory::get($dbName, $realDb);
	}
    
	private function getDbName($dbName){
		$ret = empty($dbName) ? (empty($this->dbName) ? 'xt' : $this->dbName) : $dbName;
		return $ret;
	}
	
	public function query($sql, $params = array(), $db_name = ''){
		$dbAdapter = $this->getDb($db_name, $realDbName);
		$res = $dbAdapter->query($sql, $params);
		return $res;
	}
	
	public function freeRes(&$res){
		if(is_object($res)){
			$res->closeCursor();
		}
		unset($res);
	}
	
	function getDB_Table($table, $db){
		$a = explode('.', $table);
		if(count($a) == 2){
			$table = $a[1];
			$db = $a[0];
		}
		return array($table, $db);
	}
	
	function update($table_name, $row, $conditions = '', $db_name = ''){
		list($table_name, $db_name) = $this->getDB_Table($table_name, $db_name);
		$dbAdapter = $this->getDb($db_name, $realDbName);
		if(empty($conditions))
			$conditions = " 1 ";
		if(!empty($row['id']))
			$conditions = "id=".$row['id']." AND (".$conditions.")";
		$dbAdapter->update($realDbName.'.'.$table_name, $row, $conditions);
	}
	
	function insert($table_name, $row, $db_name = ''){
		list($table_name, $db_name) = $this->getDB_Table($table_name, $db_name);
		$dbAdapter = $this->getDb($db_name, $realDbName);
		$dbAdapter->insert($realDbName.'.'.$table_name, $row);
		return $dbAdapter->lastInsertId();
	}
	
	function insertRows($table_name, $rows, $db_name = ''){
		$count = 0;
		$countOnce = 200;
		list($table_name, $db_name) = $this->getDB_Table($table_name, $db_name);
		$dbAdapter = $this->getDb($db_name, $realDbName);
		$keys = implode(',', array_keys($rows[0]));
// print_r($keys);		
		$sql = '';
		$values = array();
		foreach($rows as $valuepair){
			foreach($valuepair as $key=>$value){
				if (is_null($value))
					$valuepair[$key] = 'NULL';
				else
					$valuepair[$key] = $dbAdapter->quote($value);
			}
			$values[] = "(".implode(',', $valuepair).")";
			$count ++;
			if($count == $countOnce){
				if (!empty($values)){
					$sql = "INSERT INTO $table_name ($keys) VALUES ".implode(',', $values);
		// print_r($sql);
					$dbAdapter->query($sql);
				}
				$count = 0;
				$values = array();
			}
		}
		if (!empty($values)){
			$sql = "INSERT INTO $table_name ($keys) VALUES ".implode(',', $values);
// print_r($sql);
			$dbAdapter->query($sql);
		}
		return $dbAdapter->lastInsertId();
	}
	
	function delete($table, $conditions, $db = ''){
		list($table, $db) = $this->getDB_Table($table, $db);
		$dbAdapter = $this->getDb($db, $realDbName);
		$dbAdapter->delete($realDbName.".".$table, $conditions);
	}
	
	public function beginTransaction(){
		if(!empty($this->db) && !$this->inTransaction){
			$this->db->beginTransaction();
			$this->inTransaction = true;
		}
	}
	
	public function commit(){
		if(!empty($this->db) && $this->inTransaction){
			$this->db->commit();
			$this->inTransaction = false;
		}
	}
	
	public function rollback(){
		if(!empty($this->db) && $this->inTransaction){
			$this->db->rollback();
			$this->inTransaction = false;
		}
	}
	
	public function getFieldValues($field, $table, $condition){
		$ret = array();
		$res = $this->db->query("SELECT $field FROM $table Where $condition");
		while($row = $res->fetch()){
			if(!empty($row[$field]))
				$ret[] = $row[$field];
		}
		return $ret;
	}
	
	public function getSqlKeys(){
// print_r($this->keys);	
		return $this->keys;
	}
	
	public function describe($table, $db = ''){
		$db = $this->getDbName($db);
//print_r("db = $db, table = $table \n");	
		if (empty($this->describes[$db][$table])){
			$dbAdapter = $this->getDb($db, $realDbName);
//print_r($dbAdapter);			
//			$res = $dbAdapter->query("SHOW FULL COLUMNS FROM $db.$table");
//			$this->describes[$db][$table] = $res->fetchAll();//$this->db->describeTable($table, $db);
			$this->describes[$db][$table] = $dbAdapter->describeTable($table, $realDbName);
		}
		return $this->describes[$db][$table];
	}
	
	public function getAllTables($db = '', $emptyFirst = false){
		$tables = array();
		if ($emptyFirst)
			$tables[''] = '==NONE==';
		$realDb = '';
		$adapter = $this->getDb($db, $realDb);
		$field = "Tables_in_".$realDb;
        $res = $adapter->query("show tables");
		while($row = $res->fetch()){
			$tables[$row[$field]] = $row[$field];
		}
		return $tables;
	}
	
    public function tableExist($table, $db = ''){
		$realDb = '';
		$dbAdapter = $this->getDb($db, $realDb);
        $sql = "show tables where tables_in_".$realDb."=".$dbAdapter->quote($table);
        $res = $dbAdapter->query($sql);
        return $res->rowCount();
    }
    
	public function fieldExist($table, $field, $db = ''){
		$cols = $this->getTableFields($table, $db);
		return isset($cols[$field]);
	}
	
	public function getTableFields($table, $db = ''){
		$cols = $this->describe($table, $db);//$this->db->describeTable($table, $db);
		return array_keys($cols);
	}
	
	public function extractData($vs, $table_name = '', $db_name = ''){
		$cols = $this->describe($table_name, $db_name);
//print_r($cols);
		$ret = array();
		foreach($vs as $field=>$v){
			if (isset($cols[$field]))
				$ret[$field] = $v;
		}
		return $ret;
	}

	public function getElementId($table, $valuePair, $keyFields = array(), &$is_new = true, $db = ''){
		$db = $this->getDbName($db);
		$dbAdapter = $this->getDb($db);
		$where = array(1);
		$whereV = array();
		$realVP = array();
		$desc = $this->describe($table, $db);
		foreach($desc as $k=>$v){
			if (isset($valuePair[$v['COLUMN_NAME']]))
				$realVP[$v['COLUMN_NAME']] = $valuePair[$v['COLUMN_NAME']];
		}
		if (empty($keyFields))
			$keyFields = array_keys($realVP);
			
		foreach($keyFields as $k){
			$where[] = "`$k`=:$k";
			// $where[] = "`$k`=".$dbAdapter->quote($realVP[$k]);
			$whereV[$k] = $realVP[$k];
		}
		$res = $dbAdapter->query("SELECT * FROM $table where ".implode(' AND ', $where), $whereV);
		// $res = $dbAdapter->query("SELECT * FROM $table where ".implode(' AND ', $where));
		if ($row = $res->fetch()){
			$realVP['id'] = $row['id'];
			$this->update($table, $realVP, "id=".$row['id'], $db);
			$is_new = false;
			return $row['id'];
		}
		$is_new = true;
// if($table == 'register_ver' && $realVP['register_id'] == 63 && $realVP['description'] == 'ADC Plus-Side General Calibration Value Register'){
	// $sql = "SELECT * FROM $table where ".implode(' AND ', $where);
	// print_r($sql);
// }
		return $this->insert($table, $realVP, $db);
	}

	function save($row, $table_name, $db_name = '', &$is_new = true){
		// $is_new = true;
		if(!isset($row['id']))
			$row['id'] = 0;
		return $this->getElementId($table_name, $row, array('id'), $is_new, $db_name);
	}
	
	function rowExist($row, $table_name, $db_name = '', &$data = array()){
		$dbAdapter = $this->getDb($db_name);
		$where = array(1);
		$whereV = array();
		$realVP = array();
		$desc = $this->describ($table_name, $db_name);
		foreach($desc as $k=>$v){
			if (isset($row[$v['Field']]))
				$realVP[$v['Field']] = $row[$v['Field']];
		}
		foreach($realVP as $k=>$v){
			$where[] = "$k=:$k";
			$whereV[$k] = $v;
		}
		$res = $dbAdapter->query("SELECT * FROM $table_name where ".implode(' AND ', $where), $whereV);
		if ($ret = $res->fetch()){
			$data = $ret;
			return $ret['id'];
		}
		return false;
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
	
    public function calcSql($params, $doLimit = true){
		$this->keys = array();
		$main = $this->generateMainSql($params);
// print_r($params);
        $where = $this->generateWhere($params['searchConditions']);
		$order = $this->getOrderSql($params);
		$group = isset($params['group']) ? $params['group'] : '';
		$limit = '';
        if ($doLimit)
            $limit = $this->getLimitSql($params['limit']);

		return compact('main', 'where', 'order', 'limit', 'group');
    }
    
    public function generateMainSql($params){
//print_r($params);
		if (empty($params['fields']))
			$params['fields'] = array($params['table'].'.*');
		$main['fields'] = implode(',', $params['fields']);
		if(!empty($params['from']))
			$main['from'] = $params['from'];
		else
			$main['from'] = $params['table'];
		return $main;
    }
    
    function generateWhere($criteria){
		if (empty($criteria))
			return 1;
        $whereSql = '';
        $cond = $this->formatWhere($criteria);
		
        if (!empty($cond)){
//print_r("Not Empty\n");        
            if ($this->isLeaf($cond))
                $whereSql = $this->generateLeafWhere($cond);
            else{
                $whereSql .= $this->generateFormatWhere($cond);
            }
        }
//print_r("wherersql = $whereSql\n");        
        if (empty($whereSql))
            $whereSql = 1;
        return $whereSql;
    }
    
    function getOrderSql($params){
//print_r($params);
		$order = '';
        if (!empty($params['order'])){
			if (is_array($params['order'])){
			    $tmp = array();
				foreach($params['order'] as $field=>$dir){
					if (is_int($field)){
						$tmp[] = $dir.' ASC';
					}
					else{
						$tmp[] = $field.' '.$dir;
					}
				}
				$order = implode(',', $tmp);
//print_r($order);        
			}
			else
				$order = $params['order'];
        }
        return $order;        
    }
    
    function getLimitSql($limit){
        if (!empty($limit)){
            if (is_array($limit)){
                $start = 0;
                $rows = 0;
                foreach($limit as $k=>$v){
                    if (is_int($k)){
                        if (empty($start))
                            $start = $v;
                        else
                            $rows = $v;
                    }
                    else if ($k == 'start')
                        $start = $v;
                    else if ($k == 'rows')
                        $rows = $v;
                }
                if ($rows == 0)
                    $limit = '';
                else
                    $limit = $start.','.$rows;
            }
        }
        return $limit;
    }
    
    public function generateFilterConditions($rules){
        $conditions = array();
    	//['eq','ne','lt','le','gt','ge','bw','bn','in','ni','ew','en','cn','nc']
        $qopers = array(
    				  'eq'=>"=",
    				  'ne'=>"<>",
    				  'lt'=>"<",
    				  'le'=>"<=",
    				  'gt'=>">",
    				  'ge'=>">=",
    				  'bw'=>"BW",
    				  'ew'=>'EW',
    				  'bn'=>"NOTBW",
    				  'en'=>"NOTEW",
    				  'cn'=>"LIKE" ,
    				  'nc'=>"NOT LIKE", 
    				  'in'=>"IN",
    				  'ni'=>"NOT IN",
            );
        $i =0;
        foreach($rules as $key=>$val) {
//print_r($val);
			$field = $val['field'];
            $op = $qopers[$val['op']];
            $value = isset($val['data']) ? $val['data'] : '';
			if((!empty($value) || $value == 0)&& $op)
                $conditions[] = compact('field', 'op', 'value');
        }
//print_r($conditions);		
        return $conditions;
    }
    
    function formatWhere($criteria){
        $formatWhere = array();
		if (!empty($criteria)){
			foreach($criteria as $key=>$cond){
				if(is_int($key)){
					if ($this->isLeaf($cond)){
						$formatWhere[] = $cond;
					}
					else{
						$formatWhere[] = $this->formatWhere($cond);
					}
				}
				else{
					$key = strtolower(trim($key));
					$formatWhere[$key] = $this->formatWhere($cond);
				}
			}
		}
//print_r($formatWhere);        
        return $formatWhere;
    }
    
   	function isLeaf($cond){
        $ret = false;
        if (!is_array($cond)){
            $ret = true;
		}
        elseif (isset($cond['field']) && isset($cond['op'])){// && isset($cond['value'])){
            $ret = true;
		}
        return $ret;   
    }
    
    function generateFormatWhere($where){
        $whereSql = '';
        foreach($where as $key=>$cond){
            if (empty($cond))
                continue;
            if (is_int($key))
                $key = ' and ';
            if ($this->isLeaf($cond)){
                $addQuote = false;
                if (!empty($whereSql)){
                    $whereSql .= $key.' (';
                    $addQuote = true;
                }
                $whereSql .= $this->generateLeafWhere($cond);
                if ($addQuote)
                    $whereSql .= ')';
            }
            else{
                if (!empty($whereSql))
                    $whereSql .= " $key ";
                $whereSql .= ' ('.$this->generateFormatWhere($cond).') ';
            }
        }
        //print_r($whereSql);        
        return $whereSql;
    }

    function generateLeafWhere($cond){
//print_r("leaf where");
//print_r($cond);
        $whereSql = '';
        if (is_int($cond)){
			return 'id='.$cond;
        }
        if (is_string($cond))
            return $cond;

        if ($cond['field'] == '__interTag'){
			//$sql = "SELECT * FROM `{$this->get('db')}`.`tag` WHERE id=".$cond['value'];
			$sql = "SELECT * FROM `tag` WHERE id=".$cond['value'];
			$res = $this->db->query($sql);
			$row = $res->fetch();
			if(preg_match("/^(.*)\.(.*)$/", $row['db_table'], $matches)){
				$this->getDb($matches[1], $realDbName);
				$row['db_table'] = $realDbName.".".$matches[2];
			}
			$cond['field'] = $row['db_table'].'.'.$row['id_field'];
			$cond['value'] = $row['element_ids'];
			$cond['op'] = 'in';
		}
        $field = $cond['field'];
        $op = strtolower(trim($cond['op']));
        if (is_array($cond['value']) && $op != 'between'){
// print_r($cond);
			$cond['value'] = implode(',', $cond['value']);
			if ($op != 'in' && $op != 'not in')
				$op = 'in';
        }

        switch($op){
			case '=':
			case '<>':
			case '>':
			case '<':
			case '>=':
			case '<=':
                $whereSql = $this->cellWhere($field, $op, $cond['value']);//"$field $op ".$this->db->quote($cond['value']);
				break;
			case 'in':
				if (empty($cond['value']))
					$whereSql = " 0 ";
				else
					$whereSql = $this->cellWhere($field, $op, $cond['value']);//"$field $op ({$cond['value']})";
				break;
			case 'not in':
				if (!empty($cond['value']))
					$whereSql = $this->cellWhere($field, $op, $cond['value']);//"$field $op ({$cond['value']})";
				break;
			case 'like':
                $whereSql = $this->cellWhere($field, 'REGEXP', $cond['value']);//"$field REGEXP ".$this->db->quote($cond['value']);
				break;
			case 'not like':
                $whereSql = $this->cellWhere($field, $op, $cond['value']);//"$field $op ".$this->db->quote('%'.$cond['value'].'%');
				break;
			case 'bw':// begin with
                $whereSql = $this->cellWhere($field, 'LIKE', $cond['value'].'%');//"$field LIKE ".$this->db->quote($cond['value']."%");
				break;
			case 'ew':// end with
                $whereSql = $this->cellWhere($field, 'LIKE', '%'.$cond['value']);//"$field LIKE ".$this->db->quote("%".$cond['value']);
				break;
			default:
                $whereSql = $this->cellWhere($field, $op, $cond['value']);//"$field $op ".$this->db->quote($cond['value']);
				break;
		}
//print_r($whereSql);		
        return $whereSql;
    }

	public function cellWhere($field, $op, $value){
// print_r("field = $field, op = $op, value = $value\n");	
		$ret = array();
		$fields = explode(',', $field);
		foreach($fields as $f){
			if ($op == 'in' || $op == 'not in')
				$ret[] = "$f $op ($value)";
			else if($op == 'between'){
				$ret[] = "$f $op '{$value['min']}' and '{$value['max']}'";
			}
			else{
				$ret[] = "$f $op ".$this->db->quote($value);
				if ($op == 'like' || $op == 'REGEXP')
					$this->keys[$f] = $value;
			}
		}
		if (count($ret) > 1)
			$sql = '('.implode(' OR ', $ret).')';
		else
			$sql = $ret[0];
		return $sql;
	}
	
    public function log(){
    
    }  

	public function fetch_tags($db, $table){
		$userAdmin = useradminFactory::get();
		$userList = $userAdmin->getUserList();
		$userList[0] = 'Unknown';
		$tags = array();
		$base_sql = "SELECT id, name, creater_id FROM tag ".
			" WHERE `db_table`='$db.$table'";
		if(!empty($this->userInfo->id)){
			$sql = $base_sql . " and creater_id={$this->userInfo->id} ORDER BY name ASC";
			$res = $this->db->query($sql);
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
		$res = $this->db->query($sql);
		while($row = $res->fetch()){
//print_r($row);		
			$row['name'] = $row['name'].' (--By '.$userList[$row['creater_id']].')';
			$tags[] = $row;
		}
		return $tags;
	}
	
    public function fillOptions(&$columnDef){
//print_r("db = $db, table = $table");
		$nameKey = isset($columnDef['nameKey']) ? $columnDef['nameKey'] : false;
        if ($columnDef['formatter'] == 'select_showlink' || $columnDef['formatter'] == 'ids' ||
			$columnDef['editable'] && $columnDef['edittype'] == 'select' || 
            $columnDef['search'] && $columnDef['stype'] == 'select'){

			$columnDef['blank'] = true;//!$columnDef['editrules']['required'];
            $ret = $this->getDataOptions($columnDef, false);//$db, $table, $conditions, array('new'=>false, 'blank'=>true, 'blank_item'=>$blank_item), $allFields);
			$options = $ret['options'];
// if($columnDef['name'] == 'owner_id')
// print_r($options);
// print_r($columnDef);	
// print_r($options);		
// print_r($field_limit);			
			if (empty($options)){
                $columnDef['edittype'] = $columnDef['stype'] = 'text';
            }
            else{
                if ($nameKey){
					foreach($options as $key=>$option){
						unset($options[$key]);
						if(is_array($option)){
							$displayField = $this->getDisplayField($option);
							$options[$displayField] = $option;
						}
						else{
							$options[$option] = $option;
						}
					}
                }
				$searchOptionsValue = $this->array2Str($options);
				
				unset($options[-1]);
				$formatOptionsValue = $this->array2Str($options);
				
				$addoptions = array();
				foreach($options as $id=>$item){
					if(!isset($item['isactive']) || $item['isactive'] == ISACTIVE_ACTIVE || $item['isactive'] == 0)
						$addoptions[$id] = $item;
				}
// if($columnDef['name'] == 'period_id'){				
// print_r("\n>>>>>>name = {$columnDef['name']}\n");
// print_r($columnDef['addoptions']);	
// }
                // if ($columnDef['edittype'] == 'select'){
                    if (empty($columnDef['addoptions']['value'])){
// print_r(">>>>>>>>>{$columnDef['name']}>>>>>>>>>>>>>>>");						
						$columnDef['addoptions']['value'] = $addoptions;
                    }
                    if (empty($columnDef['editoptions']['value'])){
						$columnDef['editoptions']['value'] = $options;
                    }
                    if (empty($columnDef['formatoptions']['value'])){
                        $columnDef['formatoptions']['value'] = $formatOptionsValue;
                    }
                    if (empty($columnDef['searchoptions']['value'])){
						$columnDef['searchoptions']['value'] = $searchOptionsValue;
					}
                // }
                if (!empty($columnDef['stype']) && $columnDef['stype'] == 'select' && empty($columnDef['searchoptions']['value'])){
                    $columnDef['searchoptions']['value'] = $searchOptionsValue;
//                    $columnDef['searchoptions']['dataUrl'] = "/jqgrid/jqgrid/oper/getSelectList/db/$db/table/$table";
                }
// if($columnDef['name'] == 'period_id'){				
// print_r($columnDef['addoptions']);	
// print_r("<<<<<<<<<<<<<<<<<<<<BR>\n");
// }				
            }
        }
    }

    /***
     * Get the select or checkbox options,通过action_list走，这样，所有的获取记录的通道都统一
     */                 
    public function getDataOptions($columnDef, $new =false){//}$db, $table, $conditions = null, $params = array('new'=>false, 'blank'=>false, 'blank_item'=>false), $allFields = false){
// print_r($columnDef);
		$ret = array();
		$options = array();
		if (!empty($columnDef['blank']))
			$options[0] = '';
		if (!empty($columnDef['blank_item']))
			$options[-1] = '==Blank==';
		$whereActive = "";
		$order = '';
        if(!empty($columnDef['data_source_sql'])){
			$sql = $columnDef['data_source_sql'];
			$dbAdapter = $this->db;
			$allFields = true;
			$res = $dbAdapter->query($sql);
			$rows = $res->fetchAll();
		}
		else{
			$allFields = isset($columnDef['data_source_all_fields']) ? $columnDef['data_source_all_fields'] : false;
			$db = isset($columnDef['data_source_db']) ? $columnDef['data_source_db'] : $this->dbName;
			$table = $columnDef['data_source_table'];

			$t = tableDescFactory::get($db, $table, array());
			$displayField = $t->getDisplayField();
			if(0){
				$list_params = array('db'=>$db, 'table'=>$table);
				$list_params['sidx'] = $displayField;
				$list_params['sord'] = 'asc';
				$action_list = actionFactory::get(null, 'list', $list_params);
				$rows = $action_list->getList();
			}
			else{
				$dbAdapter = $this->getDb($db, $realDbName);
	// print_r("allFields = $allFields\n<br />");			
				$sql = "SELECT ";
				if($allFields){
					$sql .= " * ";
				}
				else{	
					$sql .= " id, $displayField";
					if($this->fieldExist($table, 'isactive', $db))
						$sql .= ", isactive";
				}
				$sql .= " FROM $db.$table";
				$conditions = isset($columnDef['data_source_condition']) ? $columnDef['data_source_condition'] : array();
				if($columnDef['limit'] !== false){
					if(!empty($columnDef['limit'])){
						$conditions[] = array('field'=>"$db.$table.id", 'op'=>'IN', 'value'=>$columnDef['limit']);
					}
					else
						$conditions[] = array('field'=>"1", 'op'=>'=', 'value'=>0);
				}
				$sql .= " WHERE ".$this->generateWhere($conditions)." ORDER BY $displayField ASC";
				$res = $dbAdapter->query($sql);
				$rows = $res->fetchAll();
			}
		}
		foreach($rows as $row){
			// if($columnDef['limit'] !== false && in_array($row['id'], $columnDef['limit'])){
				if($allFields){
					$options[$row['id']] = $row;
				}
				else{
					$item = array('id'=>$row['id']);
					if (!empty($row[$displayField])){
						$item[$displayField] = $row[$displayField];
					}
					if (!empty($row['isactive'])){
						$item['isactive'] = $row['isactive'];
					}
					$options[$row['id']] = $item;//$row[$displayField];
				}
			// }
		}
		$ret['options'] = $options;
        return $ret;
    }
    
    public function getSelectList($params){ // return the dataUrl required data structure
		$columnDef = array('data_source_db'=>$params['db'], 'data_source_table'=>$params['table']);
		$columnDef['data_source_condition'] = isset($params['condition']) ? $params['condition'] : null;
		$ret = $this->getDataOptions($columnDef);
		$data = $ret['options'];
		// $db = $params['db'];
		// $table = $params['table'];
		// $conditions = isset($params['condition']) ? $params['condition'] : null;
		// $data = $this->getDataOptions($db, $table, $conditions);
		$selectList = '';
		if (!isset($params['selectTag']))
			$params['selectTag'] = true;
		if (!isset($params['blankItem']))
			$params['blankItem'] = false;
//print_r($params);
		if ($params['selectTag'])
			$selectList= "<SELECT>";
		if ($params['blankItem'])
			$selectList .= '<option value="0"/>';
		foreach($data as $k=>$v){
			$selectList .= '<option value="'.$k.'">'.$v.'</option>';
		}
		if ($params['selectTag'])
			$selectList .= "</SELECT>";
		return $selectList;
	}
	
	public function getMultiRowEditTemplate($data_source_db, $data_source_table, $value = array(), $params = array(), $removedFields = array(), $prefix = ''){
		$columnDef['formatter'] = $columnDef['edittype'] = 'multi_row_edit';
		$itemParams = $params;
		// $itemParams['fill'] = false;
// print_r($itemParams);
		$itemTable = tableDescFactory::get($data_source_db, $data_source_table, $itemParams);
		$itemOptions = $itemTable->getOptions();
// print_r($itemOptions);
		$columnDef['temp'] = $itemOptions['add'];
		foreach($columnDef['temp'] as $ik=>$iv){
			if(/*empty($iv['editable']) || */in_array($iv['index'], $removedFields))
				unset($columnDef['temp'][$ik]);
		}
		$columnDef['legend'] = isset($itemParams['label']) ? $itemParams['label'] : $data_source_table;
		if(empty($prefix))
			$prefix = $data_source_table;
		$columnDef['prefix'] = $prefix;
		$columnDef['data_source_db'] = $data_source_db;
		$columnDef['data_source_table'] = $data_source_table;
		$columnDef['value'] = $value;
		$columnDef['editable'] = true;
		return $columnDef;
	}

	public function embed_table($data_source_db, $data_source_table, $value = array(), $params = array(), $removedFields = array(), $prefix = ''){
		$columnDef = $this->getMultiRowEditTemplate($data_source_db, $data_source_table, $value, $params, $removedFields, $prefix);
		$columnDef['formatter'] = $columnDef['edittype'] = 'embed_table';
		if(empty($prefix))
			$prefix = $data_source_table;
		$columnDef['prefix'] = $prefix;
		return $columnDef;
	}
	
}
?>
