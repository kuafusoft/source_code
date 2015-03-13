<?php
require_once('jqgrid_action.php');

class xt_srs_node_ver_action_publish extends jqgrid_action{
	protected function handleGet(){
		$view_params = $this->getViewParams($this->params);
		$this->renderView($view_params['view_file'], $view_params, $view_params['view_file_dir']);
	}
	
	protected function handlePost(){
        $params = $this->params;
		$params['id'] = json_decode($params['id']);
		if (empty($params['from']))$params['from'] = 'testcase_ver';
		$strEditStatus = EDIT_STATUS_EDITING.','.EDIT_STATUS_REVIEW_WAITING.','.EDIT_STATUS_REVIEWING.','.EDIT_STATUS_REVIEWED;
		$strPrj = '';
		$strTestcaseIds = implode(',', $params['id']);
		if($params['from'] == 'testcase'){
			$res = $this->db->query("SELECT testcase_ver_id FROM prj_testcase_ver WHERE testcase_id in ($strTestcaseIds) AND prj_id={$params['prj_ids']} AND edit_status_id in ($strEditStatus)");
			while($row = $res->fetch())
				$ver[] = $row['testcase_ver_id'];
			if (!empty($ver))
				$strTestcaseIds = implode(',', $ver);
			else
				$strTestcaseIds = '';
		}
		if (!empty($strTestcaseIds)){
			$res = $this->db->query("SELECT * FROM prj_testcase_ver WHERE testcase_ver_id IN ($strTestcaseIds)");
			while($row = $res->fetch()){
				$this->db->delete('prj_testcase_ver', "prj_id={$row['prj_id']} and testcase_id={$row['testcase_id']} and (edit_status_id=1 OR edit_status_id=2)");
			}
			
			$sql = 'UPDATE testcase_ver ver, prj_testcase_ver link SET ver.edit_status_id='.EDIT_STATUS_PUBLISHED.
				', ver.update_comment=concat(update_comment, "\n\r['.$this->userInfo->nickname.' At '.date('Y-m-d H:i:s').']\n\r", :note)'.
				', link.edit_status_id='.EDIT_STATUS_PUBLISHED.
				' WHERE ver.id in ('.$strTestcaseIds.') AND ver.edit_status_id IN ('.$strEditStatus.') AND link.testcase_ver_id=ver.id';
	//print_r($sql);
			$this->db->query($sql, array('note'=>$params['note']));
		}
		return;
	}
	
	protected function getViewParams($params){
		$view_params = $params;
		$view_params['view_file'] = "publish.phtml";
		$view_params['view_file_dir'] = '/jqgrid/xt/testcase_ver';

		return $view_params;
	}

}

?>