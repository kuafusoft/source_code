<?php
require_once('importer_base.php');

class xt_zzvw_cycle_importer_caseType extends importer_base{

	protected function _import($fileName){
		// $this->parse($fileName);
		return $this->process();
	}
	
	protected function process(){
		$res = $this->tool->query("select cycle_id, testcase_type_id from zzvw_cycle_detail group by cycle_id");
		while($row = $res->fetch()){
print_r($row);
print_r("\n<BR />");
			if($row['testcase_type_id'])
				$this->tool->update("cycle", array("testcase_type_id"=>$row['testcase_type_id']), "testcase_type_id = 0 and id=".$row['cycle_id']);
		}
	}
}

?>