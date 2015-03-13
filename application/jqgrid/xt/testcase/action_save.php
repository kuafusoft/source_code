<?php
require_once(APPLICATION_PATH.'/jqgrid/action_save.php');
/*
保存前应先检查是否已经有自己创建的处于非published状态的且关联的project一致的Version存在，如果已经存在，则应询问是否覆盖到该Version

*/
class xt_testcase_action_save extends action_save{
	// protected function saveVer($db, $params){
		// $ver = $this->prepare($db, $this->ver_table, $params);
// // print_r($ver);
		// $ver_main = $this->prepare($db, 'testcase_ver_main', $ver);
// // print_r($ver_main);
		// $ver_main_id = parent::_saveOne($db, "testcase_ver_main", $ver_main);
		
		// $params['ver_id'] = $ver_main_id;
		// $ver_detail = $this->prepare($db, 'testcase_ver_detail', $ver);
// // print_r($ver_detail);
		// if(!empty($ver['ver_id'])){
			// $this->updateRecord($db, 'testcase_ver_detail', $ver_detail, 'ver_id');
		// }
		// else{
			// $this->newRecord($db, "testcase_ver_detail", $ver_detail);
		// }
		// return $ver_main_id;
	// }
	
	// protected function prepareLink($t_id, $ver_id){
		// $link = parent::prepareLink($t_id, $ver_id);
		// $fields = array('owner_id', 'testcase_priority_id', 'edit_status_id', 'auto_level_id');
		// $res = $this->db->query("SELECT * FROM testcase_ver WHERE id=$ver_id");
		// $row = $res->fetch();
		// foreach($fields as $f)
			// $link[$f] = $row[$f];
		// return $link;
	// }
}
?>