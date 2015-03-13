<?php
require_once('dbfactory.php');
require_once('toolfactory.php');
interface iImporter{
	public function setOptions($jqgrid_action);
	public function import();
}

class importer_base implements iImporter{
	protected $db = null;
	protected $tool = null;
	protected $parse_result = array();
	
	public function __construct($params = array()){
		$this->init($params);
	}
	
	protected function init($params){
		$this->params = $params;
//print_r($this->params);		
		$this->fileName = $this->params['db'].'_'.$this->params['table'];
		$className = get_class($this);
		if (preg_match("/(.*)_importer$/", $className, $matches)){
			$this->fileName = $matches[1];
		}
//		$this->fileName .= '_'.$this->params['id'];
		$this->db = dbFactory::get($this->params['db']);
		$this->tool = toolFactory::get(array('tool'=>'db'));
	}
	
	public function setOptions($jqgrid_action){

    }
    
  // if ($_FILES["file"]["error"] > 0)
    // {
    // echo "Return Code: " . $_FILES["file"]["error"] . "<br>";
    // }
  // else
    // {
    // echo "Upload: " . $_FILES["file"]["name"] . "<br>";
    // echo "Type: " . $_FILES["file"]["type"] . "<br>";
    // echo "Size: " . ($_FILES["file"]["size"] / 1024) . " kB<br>";
    // echo "Temp file: " . $_FILES["file"]["tmp_name"] . "<br>";

    // if (file_exists("upload/" . $_FILES["file"]["name"]))
      // {
      // echo $_FILES["file"]["name"] . " already exists. ";
      // }
    // else
      // {
      // move_uploaded_file($_FILES["file"]["tmp_name"],
      // "upload/" . $_FILES["file"]["name"]);
      // echo "Stored in: " . "upload/" . $_FILES["file"]["name"];
      // }
    // }
	public function import(){
		$fileName = $this->save();
		return $this->_import($fileName);
	}
	
	protected function _import($fileName){
		$this->parse($fileName);
		return $this->process();
	}
	
	protected function parse($fileName){
		// fill in the parse_result
	}
	
	protected function process(){
		// process the parse_result
		return true;
	}
	
	protected function save(){
		$fileName = UPLOAD_ROOT."/" . $_FILES['uploaded_file']["name"];
		if (file_exists($fileName)){
			$fileName = $this->tool->formatFileName($fileName);
		}
		move_uploaded_file($_FILES['uploaded_file']["tmp_name"], $fileName);
        return $fileName;
	}
	
	public function getElementId($table, $valuePair, $keyFields = array(), &$is_new = true){
		static $elements = array();
		$cached = false;
		if (!empty($keyFields)){
			$inter = array_intersect(array('name', 'code'), $keyFields);
			if (!empty($inter)){
				$cached = true;
				$keyField = $inter[0];
			}
		}
		if (!$cached || empty($elements[$table][$valuePair[$keyField]])){
			$where = array();
			$realVP = array();
			$res = $this->tool->query("describe $table");
			while($row = $res->fetch()){
				if (isset($valuePair[$row['Field']]))
					$realVP[$row['Field']] = $valuePair[$row['Field']];
			}
			if (empty($keyFields))
				$keyFields = array_keys($realVP);
			foreach($keyFields as $k){
				$where[] = "$k=:$k";
				$whereV[$k] = $realVP[$k];
			}
			$res = $this->tool->query("SELECT * FROM $table where ".implode(' AND ', $where), $whereV);
			if ($row = $res->fetch()){
				$this->tool->update($table, $realVP, "id=".$row['id']);
				$is_new = false;
				return $row['id'];
			}
			$is_new = true;
			$this->tool->insert($table, $realVP);
			$element_id = $this->db->lastInsertId();
			if ($cached)
				$elements[$table][$keyField] = $element_id;
			return $element_id;
		}
		$is_new = false;
		return $elements[$table][$valuePair[$keyField]];
	}

};

?>
