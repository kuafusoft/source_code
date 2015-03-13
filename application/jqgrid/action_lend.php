<?php
require_once('action_jqgrid.php');

class action_lend extends action_jqgrid{
	protected function handlePost(){
		/*借出的过程分为几部：
		1. 借出：修改设备的状态为在途，同时给借入者发送一条通知，设置一个签收的Task，给设备Owner发送一条通知，给系统设置一个自动催收的Task
		2. 借入者：
			签收，触发：修改设备的状态为完成，修改设备的used_by_id，给Owner一条通知，取消系统自动催收的Task
			拒绝签收，触发：修改设备状态为退回，给Owner一条通知，取消催收的Task，设置确认的Task
		3. Owner：
			催收：触发：给借入者发送一条通知
			确认：触发：修改设备状态
		
		*/
		$ids = implode(',', json_decode($this->params['id']));
		$this->db->update($this->get('table'), array('owner_id'=>$this->params['select_item']), "id in ($ids)");
	}
	
	protected function getViewParams($params){
		$view_params = $params;
		$view_params['type'] = 'Borrower';
		$view_params['view_file'] = 'select_item.phtml';
		$view_params['view_file_dir'] = '/jqgrid';
		$view_params['blank'] = 'false';
		$ownerList = $this->userAdmin->getUserList(array('active'=>1));
		foreach($ownerList as $id=>$name)
			$view_params['items'][$id] = compact('id', 'name');
		return $view_params;
	}
}

?>