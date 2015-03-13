<?php
require_once(APPLICATION_PATH.'/jqgrid/action_ver_save.php');
/*
保存前应先检查是否已经有自己创建的处于非published状态的且关联的project一致的Version存在，如果已经存在，则应询问是否覆盖到该Version

*/
class xt_srs_node_action_save extends action_ver_save{
	protected function prepareLink($t_id, $ver_id){
		$link = parent::prepareLink($t_id, $ver_id);
		$fields = array('updater_id', 'edit_status_id');
		$res = $this->db->query("SELECT * FROM srs_node_ver WHERE id=$ver_id");
		$row = $res->fetch();
		foreach($fields as $f)
			$link[$f] = $row[$f];
		return $link;
	}
	
	protected function afterSave($srs_node_id){
		// 检测是否需要更新srs_module表的相关信息：nextcode
		$res = $this->db->query("SELECT id, nextcode, step FROM srs_module WHERE id={$this->params['srs_module_id']}");
		$srs_module = $res->fetch();
		$code = $this->params['code'];
		if(preg_match('/.*_(\d*?)$/', $code, $matches)){
			$currentCode = (int)$matches[1];
			print_r($matches);
			if ($currentCode >= $srs_module['nextcode']){
				$diff = $currentCode - $srs_module['nextcode'];
				$delta = (int)($diff / $srs_module['step']) + 1;
				$nextcode = $srs_module['nextcode'] + $delta * $srs_module['step'];
				$srs_module['nextcode'] = $nextcode;
				$this->db->update('srs_module', $srs_module, 'id='.$srs_module['id']);
			}
		}
	}
}
?>