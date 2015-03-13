<?php
require_once('dbfactory.php');
require_once('exporter_txt.php');

class xt_testcase_exporter_codec_xml_cmd extends exporter_txt{
	protected function _export(){
		$this->str = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n";
		$this->str .= "<!--\n";
		$this->str .= "XML playlist used for Freescale codec test, based on XiaoTian test system.\n";
		$this->str .= "-->\n";
		$this->str .= "<playlist>\n";
		
		$db = dbFactory::get($this->params['db']);
		$ids = implode(',', $this->params['id']);
		$sql = "SELECT tc.code, tc.summary, testcase_type.name as type, module.name as module, testpoint.name as testpoint, testcase_category.name as category, ".
			" testcase_priority.name as priority, testcase_source.name as source, auto_level.name as auto_level, ".
			" ver.command, ver.resource_link, ver.objective, ver.precondition, ver.steps, ver.expected_result, ".
			" stream.name, stream.location, codec_stream_container.name as container, codec_stream_v4cc.name as v4cc,".
			" stream.v_width, stream.v_height, stream.v_framerate, stream.v_bitrate, stream.chromasubsampling,".
			" stream.v_track, codec_stream_a_codec.name as a_codec, stream.a_samplerate, stream.a_channel, stream.a_track, stream.a_bitrate,".
			" stream.subtitle, stream.duration".
			" FROM testcase tc left join testcase_ver ver on tc.id=ver.testcase_id ".
			" left join prj_testcase_ver link on link.testcase_ver_id=ver.id".
			" left join testcase_type on tc.testcase_type_id=testcase_type.id".
			" left join testcase_module module on tc.testcase_module_id=module.id".
			" left join testcase_source on tc.testcase_source_id=testcase_source.id".
			" left join testcase_priority on ver.testcase_priority_id=testcase_priority.id".
			" left join auto_level on ver.auto_level_id=auto_level.id".
			" left join testcase_category on tc.testcase_category_id=testcase_category.id".
			" left join testcase_testpoint testpoint on tc.testcase_testpoint_id=testpoint.id".
			" left join codec_stream stream on stream.testcase_id=tc.id".
			" left join codec_stream_container on stream.codec_stream_container_id=codec_stream_container.id".
			" left join codec_stream_v4cc on stream.codec_stream_v4cc_id=codec_stream_v4cc.id".
			" left join codec_stream_a_codec on stream.codec_stream_a_codec_id=codec_stream_a_codec.id".
			" WHERE tc.id in ($ids) and link.prj_id={$this->params['prj_id']}".
			" and link.edit_status_id in (".EDIT_STATUS_PUBLISHED.",".EDIT_STATUS_GOLDEN.")";
		$res = $db->query($sql);
		while($row = $res->fetch()){
			$this->str .= "  <teststream id=\"". $row['code'] . "\">\n";
			$this->str .= "      <caseinfo>\n";
			$this->str .= "          <type>" . $row['type'] . "</type>\n";
			$this->str .= "          <module>" . $row['module'] . "</module>\n";
			$this->str .= "          <testpoint>" . $row['testpoint'] . "</testpoint>\n";
			$this->str .= "          <category>" . $row['category'] . "</category>\n";
			$this->str .= "          <priority>" . $row['priority'] . "</priority>\n";
			$this->str .= "          <source>" . $row['source'] . "</source>\n";
			$this->str .= "          <autolevel>" . $row['auto_level'] . "</autolevel>\n";
			$this->str .= "          <objective><![CDATA[" . $row['objective'] . "]]></objective>\n";
			$this->str .= "          <environment><![CDATA[" . $row['precondition'] . "]]></environment>\n";
			$this->str .= "          <steps><![CDATA[" . $row['steps'] . "]]></steps>\n";
			$this->str .= "          <expected><![CDATA[" . $row['expected_result'] . "]]></expected>\n";
			$this->str .= "          <cmdline><![CDATA[" . $row['command'] . "]]></cmdline>\n";
			$this->str .= "          <location><![CDATA[" . $row['resource_link'] . "]]></location>\n";
			$this->str .= "      </caseinfo>\n";
			$this->str .= "      <streaminfo>\n";
			$this->str .= "          <clipname><![CDATA[" . $row['summary'] . "]]></clipname>\n";
			$this->str .= "          <container>" . $row['container'] . "</container>\n";
			$this->str .= "          <video>\n";
			$this->str .= "              <v4cc>" . $row['v4cc'] . "</v4cc>\n";
			$this->str .= "              <v_width>" . $row['v_width'] . "</v_width>\n";
			$this->str .= "              <v_height>" . $row['v_height'] . "</v_height>\n";
			$this->str .= "              <v_framerate>" . $row['v_framerate'] . "</v_framerate>\n";
			$this->str .= "              <v_bitrate>" . $row['v_bitrate'] . "</v_bitrate>\n";
			$this->str .= "              <chromasubsampling>" . $row['chromasubsampling'] . "</chromasubsampling>\n";
			$this->str .= "              <v_track>" . $row['v_track'] . "</v_track>\n";
			$this->str .= "          </video>\n";
			$this->str .= "          <audio>\n";
			$this->str .= "              <a_codec>" . $row['a_codec'] . "</a_codec>\n";
			$this->str .= "              <a_samplerate>" . $row['a_samplerate'] . "</a_samplerate>\n";
			$this->str .= "              <a_bitrate>" . $row['a_bitrate'] . "</a_bitrate>\n";
			$this->str .= "              <a_channel>" . $row['a_channel'] . "</a_channel>\n";
			$this->str .= "              <a_track>" . $row['a_track'] . "</a_track>\n";
			$this->str .= "          </audio>\n";
			$this->str .= "          <duration>" . $row['duration'] . "</duration>\n";
			$this->str .= "          <others>\n";
			$this->str .= "              <subtitle>" . $row['subtitle'] . "</subtitle>\n";
			$this->str .= "          </others>\n";
			$this->str .= "      </streaminfo>\n";
			$this->str .= "  </teststream>\n";
		}
		$this->str .= "</playlist>";					
	}
};

?>
