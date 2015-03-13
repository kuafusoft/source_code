<?php

require_once('table_desc.php');
require_once('kf_editstatus.php');

class xt_srs_node_ver extends table_desc{
    private $editStatus;
    public function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
        $this->options['list'] = array(
            'id'=>array('editable'=>false, 'hidden'=>true),
			'ver'=>array('editable'=>false, 'hidden'=>false, 'width'=>100, 'formatter'=>'updateViewEditPage'),
			'srs_node_id'=>array('hidden'=>true, 'label'=>'SRS Item'),
			'content'=>array('width'=>500, 'formatter'=>'text'), 
			'prj_ids'=>array('label'=>'Project', 'search'=>true, 'editable'=>true, 'view'=>true, 'width'=>200, 'editrules'=>array('required'=>true), 'type'=>'cart', 'cart_db'=>'xt', 'cart_table'=>'prj'),
			'*'=>array('hidden'=>true, 'editable'=>false)
        );
		$this->options['edit'] = array('ver', 'content', 'prj_ids', 'edit_status_id', 'updated', 'updater_id');
		$this->options['gridOptions']['sortname'] = 'ver';
		$this->options['gridOptions']['sortorder'] = 'DESC';
		$this->options['caption'] = 'Version Information';
    } 
	
    public function getMoreInfoForRow($row){
		if (!empty($row['id'])){
			$sql = " SELECT GROUP_CONCAT(DISTINCT prj_id) as prj_ids FROM prj_srs_node_ver where srs_node_ver_id={$row['id']}";
			$res = $this->db->query($sql);
			$tmp = $res->fetch();
			$row['prj_ids'] = $tmp['prj_ids'];
		}
		return $row;
	}

    public function getButtons(){
        $buttons = array(
			'abort'=>array('caption'=>'Abort the Versions'),
			'ask2review'=>array('caption'=>'Ask to Review'),
			'diff'=>array('caption'=>'Tell the difference'),
/*
            'link2prj'=>array('caption'=>'(un)Link to Projects',
                'buttonimg'=>'',
                'title'=>'Link to Projects or Drop from Projects'),
			'publish'=>array('caption'=>'Publish'),
			'review'=>array('caption'=>'Review'),
			'report'=>array('caption'=>'Generate Report',
				'title'=>'Generate kinds of reports'),
			'batch_edit'=>array('caption'=>'Batch Edit',
				'title'=>'Batch Edit'),
*/				
				
        );
        $buttons = array_merge($buttons, parent::getButtons());
		unset($buttons['tag']);
		unset($buttons['subscribe']);
		//unset($buttons['change_owner']);
		return $buttons;
    }
	
}
