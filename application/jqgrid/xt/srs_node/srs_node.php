<?php
require_once('table_desc.php');

class xt_srs_node extends table_desc{
	protected $prj_exist = false;
    public function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		$this->options['linktype'] = 'infoLink_ver';
		$this->options['list'] = array(
            'id'=>array('editable'=>false),
			'srs_module_id'=>array('label'=>'Module'),
			'code',
			'content'=>array('label'=>'Content', 'search'=>true, 'editable'=>true, 'view'=>true),
			'prj_ids'=>array('label'=>'Project', 'search'=>true, 'editable'=>true, 'view'=>true),
			'edit_status_ids'=>array('excluded'=>true, 'label'=>'Edit Status', 'search'=>true, 'view'=>true),
			'updater_ids'=>array('excluded'=>true, 'search'=>true),
			'ver_ids'=>array('label'=>'Versions', 'hidden'=>true, 'hidedlg'=>true, 'formatter'=>'text'), 
            '*'
        );
		$this->options['query'] = array(
			'buttons'=>array(
				'new'=>array('label'=>'New', 'onclick'=>'XT.go("/jqgrid/jqgrid/newpage/1/oper/information/db/xt/table/srs_node/element/0")', 'title'=>'Create New Testcase'),
			), 
			'normal'=>array('key'=>array('label'=>'Keyword'), 'os_id', 'board_type_id', 'chip_id', 'prj_id'=>array('label'=>'Project'), 'edit_status_id')
		);
		$this->options['edit'] = array('srs_module_id', 'code');
		$this->parent_table = 'srs_module';
		$this->options['caption'] = 'SRS Item';
    } 

	public function getNextCode($srs_module_id){
		if (empty($srs_module_id)){
			$res = $this->db->query("SELECT * FROM srs_module WHERE 1 ORDER BY code ASC");
		}
		else
			$res = $this->db->query("SELECT * FROM srs_module WHERE id=$srs_module_id");
		$row = $res->fetch();
		return $row['code'].'_'.$row['nextcode'];
	}
	
    public function getMoreInfoForRow($row){
		if (!$this->prj_exist){ // 没有Project过滤项
			$sql = "SELECT group_concat(DISTINCT srs_node_ver_id) as ver_ids, group_concat(DISTINCT prj_id) as prj_ids,".
				" group_concat(distinct prj_srs_node_ver.edit_status_id) as edit_status_ids, group_concat(distinct prj_srs_node_ver.updater_id) as updater_ids".
				" FROM prj_srs_node_ver ".
				" WHERE srs_node_id=".$row['id'];
			$res = $this->db->query($sql);
			$prj = $res->fetch();
			$row['ver_ids'] = $prj['ver_ids'];
			$row['prj_ids'] = $prj['prj_ids'];
			$row['edit_status_ids'] = $prj['edit_status_ids'];
			$row['updater_ids'] = $prj['updater_ids'];
		}
		if (!empty($row['ver_ids'])){
			$sql = " SELECT GROUP_CONCAT(concat('<span style=\"color:red\">[VER', ver, ']</span>', content) SEPARATOR '\n=======\n') as content FROM srs_node_ver where id IN ({$row['ver_ids']})";
			$res = $this->db->query($sql);
			$tmp = $res->fetch();
			$row['content'] = $tmp['content'];
		}
		return $row;
	}

	// Begin to Calc SQL
	protected function getSpecialFilters(){
		return array('os_id', 'board_type_id', 'chip_id', 'prj_id', 'edit_status_ids', 'edit_status_id', 'key', 'updater_ids');
	}
	
	protected function specialSql($special, &$ret){
		$this->prj_exist = count($special);
		if ($this->prj_exist){
			$ret['main']['fields'] .= ", group_concat(distinct prj_srs_node_ver.srs_node_ver_id) as ver_ids, group_concat(distinct prj_srs_node_ver.prj_id) as prj_ids, ".
				"group_concat(distinct prj_srs_node_ver.edit_status_id) as edit_status_ids, group_concat(distinct prj_srs_node_ver.updater_id) as updater_ids";
			$ret['main']['from'] .= ' LEFT JOIN prj_srs_node_ver ON srs_node.id=prj_srs_node_ver.srs_node_id';
			$ret['group'] = "prj_srs_node_ver.srs_node_id";
			$prj_id = false;
			$prj_where = '1';
			$prj_cond = array();
			foreach($special as $c){
				switch($c['field']){
					case 'prj_id':
						$prj_id = true;
						$ret['where'] .= ' AND '.$this->tool->generateLeafWhere($c);
						break;
					case 'os_id':
					case 'chip_id':
					case 'board_type_id':
						$prj_where .= ' AND '.$this->tool->generateLeafWhere($c);
						break;
					case 'edit_status_ids':
					case 'edit_status_id':
						$c['field'] = 'edit_status_id';
						$ret['where'] .= ' AND '.$this->tool->generateLeafWhere($c);
						break;
					case 'updater_ids':
						$c['field'] = 'updater_id';
						$ret['where'] .= ' AND '.$this->tool->generateLeafWhere($c);
						break;
					default:
						$ret['where'] .= ' AND '.$this->tool->generateLeafWhere($c);
				}
			}
			if (!$prj_id && $prj_where != '1'){
				$res = $this->db->query("SELECT GROUP_CONCAT(id) as ids FROM prj WHERE $prj_where");
				$row = $res->fetch();
				if (empty($row)){
					$ret['where'] .= ' AND 0';
				}
				else{
					$ret['where'] .= ' AND '.$this->tool->generateLeafWhere(array('field'=>'prj_id', 'op'=>'IN', 'value'=>$row['ids']));
				}
			}
		}
	}
	// End of Calc SQL
	
	protected function getButtons(){
        $buttons = array(
            'link2prj'=>array('caption'=>'(un)Link to Projects',
                'buttonimg'=>'',
                'title'=>'Link to Projects or Drop from Projects'),
			'publish'=>array('caption'=>'Publish'),
			'change_owner'=>array('caption'=>'Change Owner', 'buttonimg'=>'', 'title'=>'Change the owner for the selected items'),
			
//			'batch_edit'=>array('caption'=>'Batch Edit', 'title'=>'Batch Edit'),
        );
        $buttons = array_merge($buttons, parent::getButtons());
//		unset($buttons['add']);
		return $buttons;
	}
	
}

