<?php
require_once('action_jqgrid.php');
require_once('exporterfactory.php');

class workflow_work_summary_action_import_note extends action_jqgrid{
	protected function getViewParams($params){
		$view_params = $params;
		$res = $this->db->query("SELECT name FROM prj WHERE id=".$params['id']);
		$row = $res->fetch();
		$view_params['name'] = $row['name'];
		
		// is leaf ?
		$isLeaf = true;
		$res = $this->db->query("SELECT id FROM prj where pid={$params['id']}");
		if ($res->fetch())
			$isLeaf = false;
			
		$work_summaries = array();
		$res = $this->db->query("SELECT id, name FROM work_summary WHERE frozen=0 AND creater_id=".$this->userInfo->id." ORDER BY created DESC");
		while($row = $res->fetch()){
			$work_summaries[$row['id']] = $row['name'];
		}
		$daily_note_types = array();
		$res = $this->db->query("SELECT id, name FROM daily_note_type ORDER BY name ASC");
		while($row = $res->fetch()){
			$daily_note_types[$row['id']] = $row['name'];
		}
		$es = array(
			array('label'=>'Project', 'name'=>'prj_id', 'type'=>'select', 'editable'=>false, 'editoptions'=>array('value'=>array($params['id']=>$view_params['name']))),
			array('editrules'=>array('required'=>true), 'post'=>array('type'=>'button', 'value'=>'...', 'id'=>'create_new_work_summary', 'title'=>'Create a New Work Summary'), 'label'=>'Work Summary', 'name'=>'work_summary_id', 'type'=>'select', 'editable'=>true, 'editoptions'=>array('value'=>$work_summaries)),
			array('editrules'=>array('required'=>true), 'label'=>'Note Type', 'name'=>'daily_note_type_id', 'type'=>'cart', 'editable'=>true, 'cart_db'=>'workflow', 'cart_table'=>'daily_note_type', 'editoptions'=>array('value'=>$daily_note_types)),
			array('editrules'=>array('required'=>true), 'label'=>'Select Scope', 'name'=>'note_scope_id', 'type'=>'select', 'editable'=>true, 'editoptions'=>array('value'=>array(1=>'All Notes', 2=>'Project Related Only'))),
		);
		if (!$isLeaf){
			$es[] = array('editrules'=>array('required'=>true), 'label'=>'Note Scope', 'name'=>'note_scope', 'type'=>'checkbox', 'editable'=>true, 'editoptions'=>array('value'=>array(1=>'Work Summary Notes', 2=>'Daily Note')));
			$es[] = array('editrules'=>array('required'=>true), 'label'=>'Include Levels', 'name'=>'include_level', 'type'=>'select', 'editable'=>true, 'editoptions'=>array('value'=>array(1=>'All Sub-levels', 2=>'Son-level Only')));
		}
		$view_params['es'] = $es;
		$view_params['view_file'] = "collect_note.phtml";
		$view_params['view_file_dir'] = '/jqgrid/workflow/prj';

		return $view_params;
	}
	
	protected function handlePost(){
print_r($this->params);	
		$res = $this->db->query("SELECT period.from, period.end FROM work_summary left join period on work_summary.period_id=period.id WHERE work_summary.id=".$this->params['work_summary_id']);
		$period = $res->fetch();
		
		$res = $this->db->query("SELECT id, ps FROM prj WHERE id={$this->params['prj_id']}");
		$prj = $res->fetch();
		
		if (!isset($this->params['include_level'])){
			$prjs = array('ids'=>$this->params['prj_id']);
		}
		else{
			if ($this->params['include_level'] == 1){
				$where = "prj.ps like '{$prj['ps']}%'";
			}
			else
				$where = "(prj.pid={$prj['id']} || prj.id={$prj['id']})";
			$res = $this->db->query("SELECT group_concat(id) as ids FROM prj WHERE $where");
			$prjs = $res->fetch();
		}		
		print_r($prj);
		print_r($prjs);
		
		if (!isset($this->params['note_scope'])){
			$this->params['note_scope'] = array(2);
		}
		
		
	}
	
}

?>