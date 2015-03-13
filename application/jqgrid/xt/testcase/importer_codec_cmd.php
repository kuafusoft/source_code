<?php
require_once('importer_base.php');

class xt_testcase_importer_codec_cmd extends importer_base{

	protected function parse($fileName){
		$parser = xml_parser_create();
		if (!($fp = fopen($fileName, "r"))) {
			die("could not open XML input");
		}
		if($data = fread($fp, filesize($fileName)))
		   xml_parse_into_struct($parser,$data,$vals,$index);

		xml_parser_free($parser);
		$i = 0;
		foreach($vals as $key=>$val){
			if(!empty($val['value'])){
				if($val['tag'] == 'CMDLINE'){
					$i++;
					$this->parse_result[$i]['cmdline'] = trim($val['value']);
				}
				else if($val['tag'] == 'LOCATION')
					$this->parse_result[$i]['location'] = trim($val['value']);
				else if($val['tag'] == 'TITLE'){
					$this->parse_result[$i]['title'] = trim($val['value']);
					
				}
			}
		}
	}
	
	protected function process(){
		foreach($this->parse_result as $k=>$data){
// print_r($data);
// print_r("\n<BR />");
			if(!empty($data['title']) && !empty($data['cmdline'])){
				$umb = dbFactory::get('umbrella');	
				$res0 = $this->db->query("select ver.id, ver.old_id from testcase tc left join testcase_ver ver on ver.testcase_id = tc.id where tc.summary = '{$data['title']}'");
				if($row0 = $res0->fetch()){
					$res1 = $umb->query("select code, command from zzvw_testcase_ver where id = {$row0['old_id']}");
					if($ver1 = $res1->fetch()){
print_r($ver1['code']);
print_r("\n<BR />");
						$this->db->update("testcase_ver", array("command"=>$ver1['command']), "id=".$row0['id']);
					}
				}
print_r("xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx");
print_r("\n<BR />");
				$res0 = $this->db->query("select ver.id, ver.old_id, tc.code, ver.command".
					" from testcase tc left join testcase_ver ver on ver.testcase_id = tc.id".
					" left join prj_testcase_ver ptv on ver.id = ptv.testcase_ver_id".
					" where tc.summary = '".$data['title']."' and ptv.testcase_ver_id is not null");
				if($row0 = $res0->fetch()){
					$res1 = $umb->query("select code, command from zzvw_testcase_ver where id = {$row0['old_id']}");
					if($ver1 = $res1->fetch()){
print_r($ver1['code']);
print_r("\n<BR />");
						$this->db->update("testcase_ver", array("command"=>$ver1['command']), "id=".$row0['id']);
					}
				}
print_r("yyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyy");
print_r("\n<BR />");				
				$res = $this->db->query("select distinct ver.id as testcase_ver_id, tc.code, ver.command".
					" from testcase tc left join testcase_ver ver on ver.testcase_id = tc.id".
					" left join prj_testcase_ver ptv on ver.id = ptv.testcase_ver_id".
					" left join prj on prj.id = ptv.prj_id".
					" where tc.summary = '".$data['title']."' and prj.os_id=1 and ptv.testcase_ver_id is not null".
					" and ptv.edit_status_id in (1,2) and tc.isactive = 1");
				if($row = $res->fetch()){
print_r($row['code']);
print_r("\n<BR />");
					if(empty($row['command']) || $row['command'] == '' || $row['command'] == 'NA' || $row['command'] == 'empty' )
						$this->db->update("testcase_ver", array('command'=>$data['cmdline']), "id=".$row['testcase_ver_id']);
				}
			}
		}
	}
}
?>
