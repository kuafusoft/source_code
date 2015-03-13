<?php
require_once('Zend/Db.php');

defined("BAT_ROWS") || define("BAT_ROWS", 500);

class transBase{
	protected $maps = array();
	protected $name_maps = array();
	protected $source_db = null, $target_db = null;
	
	function __construct($source_dsn, $target_dsn){
		$this->source_db = $this->getDb($source_dsn);
		$this->target_db = $this->getDb($target_dsn);
	}
	
	protected function getDb($dsn){
		if (empty($dsn))
			$source_dsn = array();
		else if (is_string($dsn))
			$dsn = array('dbname'=>$dsn);
		if (!isset($dsn['host']))$dsn['host'] = 'localhost';
		if (!isset($dsn['username']))$dsn['username'] = 'root';
		if (!isset($dsn['password']))$dsn['password'] = 'dbadmin';
		return Zend_Db::factory('PDO_MYSQL', $dsn);
	}
	
	function empty_table($table){
		$this->target_db->query("truncate table $table");
		return;
	}
	
	function getMap($target){
		return $this->maps[$target];
	}
	
	function preMap($table){
		// prepare the _map
		$res = $this->target_db->query("SELECT id, old_id FROM $table");
		while($row = $res->fetch())
			$this->maps[$table][$row['old_id']] = $row['id'];
		$res->closeCursor();
		unset($res);
	}
	
	function preNameMap($table){
		$res = $this->target_db->query("SELECT * FROM $table");
		while($row = $res->fetch()){
			$this->name_maps[$table][strtolower($row['name'])] = $row['id'];
		}
		$res->closeCursor();
		unset($res);
	}

	function trans_table($source_table, $target_table, $fieldMap = array()){
		print_r(">>>Start to process $source_table, current time is ".date('H:i:s')."\n");
		$method = 'trans_table_'.$target_table;
		if (!method_exists($this, $method))
			$newRow = $this->trans_table_default($source_table, $target_table, $fieldMap);
		else{
			$newRow = $this->$method($source_table);
		}
		print_r("Finished to process $source_table, current time is ".date('H:i:s')."<<<\n");
	}
	
	function trans_table_default($source_table, $target_table, $fieldMap = array()){
		//construct the map
		$this->preMap($target_table);

		$target = array();
		$i = 0;
		$source = $this->source_db->query("SELECT * FROM $source_table");
		while($source_row = $source->fetch()){
			$newId = $this->checkNewId($source_row, $target_table);
			if (!empty($newId))
				continue;
				
			$newRow = $this->trans_row($source_row, $target_table, $fieldMap);
			if (empty($newRow))
				continue;
			// if($j >= 49390){
// print_r($source_row);
// print_r($newRow);		
				// if ($j == 49390)
					// continue;
			// }
			$target[] = $newRow;
			$i ++;
			if ($i == BAT_ROWS){
				$this->insertRows($target, $target_table);
				$i = 0;
				unset($target);
				$target = array();
			}
		}
		$source->closeCursor();
		unset($source);
		if ($i != 0)
			$this->insertRows($target, $target_table);
		
		//reconstruct the map
		$this->preMap($target_table);
	}
	
	function trans_row($source_row, $target_table, $fieldMap = array()){
		$method = 'trans_row_'.$target_table;
		if (!method_exists($this, $method))
			$newRow = $this->trans_row_default($source_row, $fieldMap);
		else{
			$newRow = $this->$method($source_row);
		}
		if (!empty($newRow)){
			$newRow['old_id'] = $source_row['id'];
		}
		return $newRow;
	}

	function trans_row_default($source_row, $fieldMap = array()){
// print_r($source_row);
// print_r($fieldMap);
		if (empty($fieldMap)){
			$target = $source_row;
		}
		else{
			foreach($fieldMap as $old=>$new){
				if (is_int($old))
					$target[$new] = $source_row[$new];
				else
					$target[$new] = $source_row[$old];
			}
		}
		return $target;
	}
	
	function insertRows($valuepairs, $table){
// print_r($valuepairs);	
		$keys = implode(',', array_keys($valuepairs[0]));
// print_r($keys);		
		$values = array();
		$sql = '';
		$keyCollected = false;
		foreach($valuepairs as $valuepair){
			foreach($valuepair as $key=>$value){
    		    if (is_null($value))
    		        $valuepair[$key] = 'NULL';
    		    else
    				$valuepair[$key] = $this->target_db->quote($value);
    		}
			$values[] = "(".implode(',', $valuepair).")";
        }
        if (!empty($values)){
            $sql = "INSERT INTO $table ($keys) VALUES ".implode(',', $values);
// print_r($sql);
			try{
				$this->target_db->query($sql);
			}catch(Exception $e){
				print_r($sql);
				print_r($e->getMessage());
				exit;
			}
        }
		return $this->target_db->lastInsertId();
	}

	function checkNewId($vp, $target_table){
		$newId = 0;
		if (!empty($vp['id']) && !empty($this->maps[$target_table][$vp['id']]))
			$newId = $this->maps[$target_table][$vp['id']];
		else if (!empty($vp['name']) && !empty($this->name_maps[$target_table][strtolower($vp['name'])]))
			$newId = $this->name_maps[$target_table][strtolower($vp['name'])];
		return $newId;
	}
	
	function insertRow($vp, $table){
		$this->target_db->insert($table, $vp);
		return $this->target_db->lastInsertId();
	}
}
?>