<?php 
require_once(APPLICATION_PATH.'/jqgrid/action_save.php');
class action_newElement extends action_save{
	protected function getParams($params){
		$options = $this->getOptions(false);
		$cols = $options['edit'];
		$legend = $this->table_desc->getCaption();
		return array('cols'=>$cols, 'legend'=>$legend, 'view_file'=>'newElement.phtml', 'view_file_dir'=>'/jqgrid', 'params'=>array('db'=>$this->db_name, 'table'=>$this->table_name));
	}
}

?>