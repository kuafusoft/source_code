<?php

require_once('action_jqgrid.php');

class xt_zzvw_cycle_detail_action_getlatestresult extends action_jqgrid{

   public function handlePost(){
		//$params = $this->parseParams();
		$params = $this->params;
		$res = $this->tool->query("SELECT testcase_id, d_code FROM ".$this->get('table')." WHERE id=".$params['id']);
		$str = '';
		if($info = $res->fetch()){
			$str .= '<table style="width:600px" class="table">';
			$str .= '<tr class="tabletitle"><td colspan="6">'.$info['d_code'].' ( 5 Latest Test Result(s) ) :</td></tr>';
			$str .= '<tr class="tablecaption"><td style="width:5%">Result</td>'.
				'<td style="width:10%">Env</td><td style="width:10%">Codec Stream</td>'.
				'<td style="width:10%">CRID</td><td style="width:20%">CR Comment</td>'.
				'<td style="width:35%">Cycle</td>';
			$sql = 'SELECT result_type.name as result_type, defect_ids, comment, cycle.name as cycle, test_env.name as test_env, codec_stream.name as codec_stream FROM cycle_detail detail'.
			' LEFT JOIN result_type ON detail.result_type_id=result_type.id LEFT JOIN cycle ON detail.cycle_id=cycle.id'. 
			' LEFT JOIN test_env ON detail.test_env_id=test_env.id LEFT JOIN codec_stream ON detail.codec_stream_id=codec_stream.id'. 
			' WHERE detail.testcase_id='.$info['testcase_id'].' AND detail.id NOT LIKE '.$info['testcase_id'].
			' ORDER BY detail.finish_time DESC limit 0, 5';
			$res = $this->tool->query($sql);
			$currentRow = 0;
			while($row = $res->fetch()){
				if($currentRow % 2)
					$class = 'odd';
				else
					$class = 'even';
				if(empty($row['defect_ids']))
					$row['defect_ids'] = 'null';
				if(empty($row['comment']))
					$row['comment'] = 'null';
				if(empty($row['cycle']))
					$row['cycle'] = 'null';
				if(empty($row['test_env']))
					$row['test_env'] = 'null';
				if(empty($row['codec_stream']))
					$row['codec_stream'] = 'null';
				if(!empty($row['result_type'])){
					$str .= '<tr class="'.$class.'">';
					$str .= '<td>'.$row['result_type'].'</td>';
					$str .= '<td>'.$row['test_env'].'</td>';
					$str .= '<td>'.$row['codec_stream'].'</td>';
					$str .= '<td>'.$row['defect_ids'].'</td>';
					$str .= '<td>'.$row['comment'].'</td>';
					$str .= '<td>'.$row['cycle'].'</td>';
					$str .= '</tr>';
				}
				$currentRow ++;
			}
			$str .= '</table>';
		}
		
		echo $str;
	}
	
}

?>