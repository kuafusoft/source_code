<?php
require_once('importer_base.php');

class xt_zzvw_cycle_importer_caseVer extends importer_base{

	protected function _import($fileName){
		// $this->parse($fileName);
		return $this->process();
	}
	
	// protected function process(){
		// $res = $this->tool->query("select detail.id, detail.testcase_id, detail.testcase_ver_id, cycle.prj_id from cycle_detail detail left join cycle on cycle.id = detail.cycle_id");// where cycle_id>3341 and cycle.creater_id != 65");
		// $j = 0;
		// $a = 0;
		// $b = 0;
		// $f = 0;
		// while($row = $res->fetch()){
			// $a++ ;
			// $i = 0;
			// $result = $this->tool->query("select testcase_ver_id, testcase_id from prj_testcase_ver".
				// " where prj_id = ".$row['prj_id']." and testcase_id = ".$row['testcase_id'].
				// " and edit_status_id in (1,2) order by testcase_ver_id asc");
			// while($info = $result->fetch()){
				// $i += 1;
				// $b ++ ;
				// if($info['testcase_ver_id']){
					// if($info['testcase_ver_id'] != $row['testcase_ver_id'])
						// $this->tool->update("cycle_detail", array("testcase_ver_id"=>$info['testcase_ver_id']), "id=".$row['id']);
				// }
			// }
			// if(!$i){
				// $j++;
			// }
		// }
		// // print_r($j.": ".$a." : ".$b." : ".$f);
	// }
	protected function process(){		
		$cases = array();
		$num = array();
		$res = $this->tool->query("select * from prj_testcase_ver where edit_status_id = 1 ");// where cycle_id>3341 and cycle.creater_id != 65");
		while($row = $res->fetch()){
			if(isset($num[$row['testcase_id']][$row['prj_id']]) && $num[$row['testcase_id']][$row['prj_id']] >= 1){
				$num[$row['testcase_id']][$row['prj_id']]++;
				continue;
			}
			else{
				// $cases[$row['testcase_id']][$row['prj_id']] = $row['testcase_ver_id'];
				$num[$row['testcase_id']][$row['prj_id']] = 1;
			}
		}
		$total = 0;
		foreach($num as $k=>$prj){
			foreach($prj as $i=>$n){
				if($n != 1){
					$res0 = $this->tool->query("select * from prj_testcase_ver where testcase_id = ".$k." and prj_id = ".$i." and edit_status_id = 1 order by testcase_ver_id");
					while($info0 = $res0->fetch()){
						if(!empty($ver)){
							if($ver < $info0['testcase_ver_id']){
								$this->tool->delete("prj_testcase_ver", "testcase_ver_id=".$ver." and testcase_id = ".$k." and prj_id = ".$i);
								$ver = $info0['testcase_ver_id'];
							}
							else
								$this->tool->delete("prj_testcase_ver", "id=".$info0['id']);
						}
						else
							$ver = $info0['testcase_ver_id'];
					}
					unset($ver);
				}
			}
		}
		print_r($total);
		
	}
}

?>