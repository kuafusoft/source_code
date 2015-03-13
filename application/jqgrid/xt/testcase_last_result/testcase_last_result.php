<?php

require_once('table_desc.php');

class xt_testcase_last_result extends table_desc{
	public function getMoreInfoForRow($row){
		$sql = "SELECT cycle_detail.build_result_id, cycle_detail.defect_ids as cr, cycle.name as cycle, compiler.name as compiler, ".
			" prj.name as prj, module.name as module, testcase.code as code, ver.expected_result".
			" FROM cycle_detail left join testcase_ver ver on cycle_detail.testcase_ver_id=ver.id".
			" left join cycle on cycle_detail.cycle_id=cycle.id".
			" left join compiler on cycle.compiler_id=compiler.id".
			" left join prj on cycle.prj_id=prj.id".
			" left join testcase on ver.testcase_id=testcase.id".
			" left join testcase_module module on testcase.testcase_module_id=module.id".
			" WHERE cycle_detail.id={$row['cycle_detail_id']}";
		$res = $this->tool->query($sql);
		$more = $res->fetch();
		$row = array_merge($row, $more);
		return $row;
	}
}
