<?php
require_once('table_desc.php');

class xt_srs_module extends table_desc{
	protected function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		$this->options['list'] = array(
			'*',
			'nextcode'=>array('hidden'=>true),
			'step'=>array('hidden'=>true),
			'pid'=>array('hidden'=>true),
			'items'
		);
		$this->options['query'] = array('normal'=>array('key'=>array('label'=>'Keyword')));
		$this->options['edit'] = 'code,content,nextcode,step,isactive';
        $this->options['gridOptions']['subGrid'] = true;
	}

    public function getMoreInfoForRow($row){
		$res = $this->db->query("SELECT COUNT(*) as cc FROM srs_node WHERE srs_module_id={$row['id']}");
		$cc = $res->fetch();
		$row['items'] = $cc['cc'];
		return $row;
	}
}
?>