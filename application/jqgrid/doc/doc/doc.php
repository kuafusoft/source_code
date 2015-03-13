<?php
require_once('table_desc.php');

class doc_doc extends table_desc{
    public function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		$this->options['list'] = array(
			'id', 
			'doc_type_id'=>array('label'=>'Document Type'),
			'name'=>array('editable'=>true, 'unique'=>true, 'editrules'=>array('required'=>true)),
			'location'=>array('editable'=>false),
			'keyword_id'=>array('label'=>'Keywords'),
			'doc_db_tb_id'=>array('hidden'=>true, 'hidedlg'=>true),
		);
		$this->options['edit'] = array('name', 'keyword'=>array('type'=>'text'));
		$this->options['add'] = array('doc_type_id', 'name', 'upload_file'=>array('label'=>'Upload File', 'type'=>'file'), 'keyword'=>array('type'=>'text'), 'hidden_frame'=>array('type'=>'iframe'));
		
		$this->linkTables = array('m2m'=>array('keyword'));
		$this->options['gridOptions']['label'] = 'Document';
    }

	protected function getButtons(){
		$buttons = array(
			'upload_doc'=>array('caption'=>'Upload'),
			'diff'=>array('caption'=>'Diff')
		);
		return $buttons;
	}
	
	// protected function _getLimit($params){
		// return array(1, 2, 3, 4, 5, 10);
	// }
	public function accessMatrix(){
		// $access_matrix = parent::accessMatrix();
		$access_matrix['all']['all'] = false;
		$access_matrix['admin']['all'] = $access_matrix['assistant_admin']['all'] = true;
		return $access_matrix;
	}
}
?>