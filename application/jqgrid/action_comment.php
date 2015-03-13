<?php 
require_once('action_base.php');
class action_comment extends action_base{
	protected function handleGet(){
        $commentTable = $this->table.'_comment';
        $indexField = $this->table.'_id';
		$userTable = $this->model->getUserTable();
		$sql = "SELECT comment.*, concat(user.nickname, '(', user.username, ')') as commentator ".
			" FROM `$commentTable` comment LEFT JOIN `$userTable` user ON comment.commentator_id=user.id ".
			" WHERE `$indexField`=".$this->params['element']." ORDER BY created DESC"
		$result = $this->db->query($sql);
//print_r($result);
		$params['comments'] = $result->fetchAll();
		$this->renderView('comment.phtml', $params, '/jqgrid');
	}
	
	protected function handlePost(){
        $commentTable = $this->table.'_comment';
        $indexField = $this->table.'_id';
		$currentUser = $this->model->getCurrentUser();
		$vp = array($indexField=>$this->params['element'], 'comment'=>$this->params['comment'], 'commentator_id'=>$currentUser['id']);
		$this->db->insert($commentTable, $vp);
	}
	
}

?>