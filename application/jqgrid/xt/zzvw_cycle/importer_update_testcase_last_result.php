<?php
require_once('importer_base.php');

class xt_zzvw_cycle_importer_update_testcase_last_result extends importer_base{
	protected function _import($fileName){
		// $this->parse($fileName);
		return $this->process();
	}
	
	// protected function process(){
// // print_r("xxx");
		// $id = 0;
		// for($i = 0; $i < 70; $i ++){
			// $ff = $this->processLastResult($id);
			// if($ff == 'error')
				// break;
			// $id = $ff;
		// }
	// }
	
	// private function processLastResult($id){
	protected function process(){
print_r("process update"."\n<br />");
		$num = 0;
		$res = $this->tool->query("select cycle.prj_id, cycle.rel_id, cycle_detail.testcase_id, cycle_detail.codec_stream_id, 
			cycle_detail.finish_time, cycle_detail.id  as cycle_detail_id, cycle_detail.result_type_id
			from cycle_detail 
			left join cycle on cycle.id = cycle_detail.cycle_id 
			where (cycle.prj_id, cycle.rel_id, cycle_detail.testcase_id, cycle_detail.codec_stream_id, cycle_detail.finish_time) 
				in (select cycle.prj_id, cycle.rel_id, cycle_detail.testcase_id, cycle_detail.codec_stream_id, max(cycle_detail.finish_time) 
				from cycle_detail 
				left join cycle on cycle.id = cycle_detail.cycle_id 
				where cycle_detail.result_type_id != 0 and cycle_detail.finish_time > 0 
				and cycle_detail.cycle_id != 0 and cycle_detail.cycle_id is not null 
				and cycle.prj_id is not null and cycle.rel_id is not null 
				group by cycle.prj_id, cycle.rel_id, cycle_detail.testcase_id, cycle_detail.codec_stream_id)");
		while($row = $res->fetch()){
			$insert = array('testcase_id'=>$row['testcase_id'], 'cycle_detail_id'=>$row['cycle_detail_id'], 'result_type_id'=>$row['result_type_id'], 
				'prj_id'=>$row['prj_id'], 'rel_id'=>$row['rel_id'], 'codec_stream_id'=>$row['codec_stream_id'], 'tested'=>$row['finish_time']);
			$this->tool->insert('testcase_last_result_0717', $insert);
		}
	}
}

?>
