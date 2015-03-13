<?php 
require_once(APPLICATION_PATH.'/jqgrid/useradmin/groups/action_save.php');
class useradmin_groups_action_newElement extends useradmin_groups_action_save{
	protected function handleGet(){
		$view_params = $this->getParams($this->params);
		$this->renderView( $view_params['view_file'], $view_params,  $view_params['view_file_dir']);
	}
	
	protected function getParams($params){
		$options = $this->getOptions();
		$cols = $options['edit'];
		$legend = $this->table_desc->getCaption();
		return array('cols'=>$cols, 'legend'=>$legend, 'view_file'=>'newElement.phtml', 'view_file_dir'=>'/jqgrid', 'params'=>array('db'=>$this->db_name, 'table'=>$this->table_name));
	}
}

?>