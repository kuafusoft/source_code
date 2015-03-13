<?php
require_once(APPLICATION_PATH.'/jqgrid/action_list.php');

class action_linkage extends action_list{//}action_jqgrid{
	protected function filterParams(){
		$t = array('value'=>$this->params['value'], 'field'=>$this->params['field']);
		if(isset($this->params['cond']))
			$t['cond'] = $this->params['cond'];
		unset($this->params['field']);
		unset($this->params['value']);
		unset($this->params['cond']);

		$params = parent::filterParams();//$this->params;
        $displayField = $this->table_desc->getDisplayField();
		$params['order'] = "$displayField ASC";
		$params['limit'] = array('start'=>0, 'rows'=>0);
		$params['page'] = 1;
		if(!empty($t['value'])){
			$params['searchConditions'][] = array(array('field'=>$t['field'], 'value'=>$t['value'], 'op'=>'='));
		}
// print_r($params);
		return $params;
	}

	protected function handlePost(){
		$ret = parent::handlePost();
		return $ret['rows'];
	}
}

?>