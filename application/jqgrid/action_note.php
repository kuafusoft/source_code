<?php 
require_once('action_base.php');
class action_note extends action_base{
	protected function handleGet(){
		$result = $this->model->getDbAdapter()->query("SELECT id, note from `{$this->table}` WHERE id={$this->params['element']}");
//print_r($result);
		$this->params['note'] = $result->fetch();
		$this->model->renderView('note.phtml', $this->params, '/jqgrid');
	}
	
	protected function handlePost(){
		$this->model->getDbAdapter()->update($this->table, array('note'=>$this->params['note']), "id=".$this->params['element']);
		return json_encode($this->params);
	}
}

?>