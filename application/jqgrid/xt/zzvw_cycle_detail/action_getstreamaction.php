<?php

require_once('action_jqgrid.php');

class xt_zzvw_cycle_detail_action_getstreamaction extends action_jqgrid{

   public function handlePost(){
		$params = $this->parseParams();
		$res = $this->db->query("SELECT detail.cycle_id as cycle_id, detail.codec_stream_id as codec_stream_id, codec_stream.name as codec_stream FROM cycle_detail detail".
			" LEFT JOIN codec_stream ON detail.codec_stream_id=codec_stream.id WHERE detail.id=".$params['id']);
		$str = '';
		if($info = $res->fetch()){
			if(!empty($info['codec_stream_id'])){
				$str .= '<table style="width:800px" class="table">';
				$str .= '<tr class="tabletitle"><td colspan="6">'.$info['codec_stream'].' ( Result(s) In The Cycle ) :</td></tr>';
				$str .= '<tr class="tablecaption"><td style="width:20%">Prj</td><td style="width:10%">Result</td><td style="width:15%">CRID</td>'.
					'<td style="width:40%">CR Comment</td><td style="width:15%">Release</td>';
				$sql = 'SELECT result_type.name as result_type, cycle.name as cycle, prj.name as prj, rel.name as rel, codec_stream.name as codec_stream,'.
					' detail.comment as comment, detail.defect_ids as CRID FROM testcase_last_result lastresult'.
					' LEFT JOIN result_type ON lastresult.result_type_id=result_type.id LEFT JOIN cycle_detail detail ON lastresult.cycle_detail_id=detail.id'.
					' LEFT JOIN prj ON lastresult.prj_id=prj.id LEFT JOIN codec_stream ON lastresult.codec_stream_id=codec_stream.id'.
					' LEFT JOIN rel ON lastresult.rel_id= rel.id LEFT JOIN cycle ON detail.cycle_id=cycle.id'.
					' WHERE lastresult.testcase_id in ('.$params["case_id"].') AND lastresult.codec_stream_id='.$info['codec_stream_id'].
					' ORDER BY lastresult.tested DESC limit 0, 5';
				$res = $this->db->query($sql);
				$currentRow = 0;
				$i = 0;
				while($row = $res->fetch()){
					if($currentRow % 2)
						$class = 'odd';
					else
						$class = 'even';
					if(empty($row['CRID']))
						$row['CRID'] = 'null';
					if(empty($row['comment']))
						$row['comment'] = 'null';
					if(empty($row['result_type']))
						$row['result_type'] = 'null';
					if(empty($row['rel']))
						$row['rel'] = 'null';
					if(empty($row['prj']))
						$row['prj'] = 'null';
					if(!empty($row['result_type'])){
						$i++;
						$str .= '<tr class="'.$class.'">';
						$str .= '<td>'.$row['prj'].'</td>';
						$str .= '<td>'.$row['result_type'].'</td>';
						$str .= '<td>'.$row['CRID'].'</td>';
						$str .= '<td>'.$row['comment'].'</td>';
						$str .= '<td>'.$row['rel'].'</td>';
						//$str .= '<td>'.$row['cycle'].'</td>';
						$str .= '</tr>';
					}
					$currentRow ++;
				}
				$str .= '</table>';
			}
			else{
				$str .= '<table style="width:800px" class="table">';
				$str .= '<tr class="tabletitle"><td colspan="5">'.$info['codec_stream_id'].' ( Result(s) In The Cycle ) :</td></tr>';
				$str .= '<tr class="tablecaption"><td style="width:15%">Trick Mode</td><td style="width:10%">Env</td>'.
						'<td style="width:5%">Result</td><td style="width:10%">CRID</td>'.
						'<td style="width:30%">CR Comment</td></table>';//<td style="width:30%">Cycle</td>';
			}
		}
		echo $str;
	}
	
}

?>