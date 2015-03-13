<?php

require_once('action_jqgrid.php');

class xt_zzvw_cycle_detail_action_getcrossresult extends action_jqgrid{

   public function handlePost(){
		//$params = $this->parseParams();
		$params = $this->params;
		$res = $this->tool->query("SELECT testcase_id, d_code FROM ".$this->get('table')." WHERE id=".$params['id']);
		$str = '';
		if($info = $res->fetch()){
			$str .= '<table style="width:800px" class="table">';
			$str .= '<tr class="tabletitle"><td colspan="7">'.$info['d_code'].' ( Cross Project Test Result(s) ) :</td></tr>';
			$str .= '<tr class="tablecaption"><td style="width:5%">Result</td><td style="width:15%">Project</td>'.
				'<td style="width:15%">Release</td><td style="width:15%">Codec Stream</td><td style="width:25%">Cycle</td>'.
				'<td style="width:10%">CRID</td><td style="width:15%">CR Comment</td>';
			//
			$sql = 'SELECT result_type.name as result_type, cycle.name as cycle, prj.name as prj, rel.name as rel, codec_stream.name as codec_stream,'.
				' detail.comment as comment, detail.defect_ids as CRID FROM testcase_last_result lastresult'.
				' LEFT JOIN result_type ON lastresult.result_type_id=result_type.id LEFT JOIN cycle_detail detail ON lastresult.cycle_detail_id=detail.id LEFT JOIN prj ON lastresult.prj_id=prj.id'. 
				' LEFT JOIN codec_stream ON lastresult.codec_stream_id=codec_stream.id'.
				' LEFT JOIN rel ON lastresult.rel_id= rel.id LEFT JOIN cycle ON detail.cycle_id=cycle.id WHERE lastresult.testcase_id in ('.$info['testcase_id'].
				') ORDER BY lastresult.tested DESC limit 0, 5';
			$res = $this->tool->query($sql);
			$currentRow = 0;
			while($row = $res->fetch()){
				if($currentRow % 2)
					$class = 'odd';
				else
					$class = 'even';
				if(empty($row['prj']))
					$row['prj'] = 'null';
				if(empty($row['rel']))
					$row['rel'] = 'null';
				if(empty($row['cycle']))
					$row['cycle'] = 'null';
				if(empty($row['codec_stream']))
					$row['codec_stream'] = 'null';
				if(empty($row['comment']))
					$row['comment'] = 'null';
				if(empty($row['CRID']))
					$row['CRID'] = 'null';
				// if(!empty($row['result_type'])){
					$str .= '<tr class="'.$class.'">';
					$str .= '<td>'.$row['result_type'].'</td>';
					$str .= '<td>'.$row['prj'].'</td>';
					$str .= '<td>'.$row['rel'].'</td>';
					$str .= '<td>'.$row['codec_stream'].'</td>';
					$str .= '<td>'.$row['cycle'].'</td>';
					$str .= '<td>'.$row['comment'].'</td>';
					$str .= '<td>'.$row['CRID'].'</td>';
					$str .= '</tr>';
				// }
				$currentRow ++;
			}
			$str .= '</table>';
		}
		
		echo $str;
	}
	
}

?>