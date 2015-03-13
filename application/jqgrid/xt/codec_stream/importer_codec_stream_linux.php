<?php
require_once('importer_excel.php');

class xt_codec_stream_importer_codec_stream_linux extends importer_excel{
	protected $total = 0;
	private $tags;
	
	protected function _import($fileName){
		$this->parse($fileName);
		return $this->process();
	}
	
	protected function process(){
		$code = array(
			'DivxDec_License_Check_001',  
			'AC3_license protection',
			'ASF_parser_license protection',  
			'AACPlus_license protection_clone',
			'RADec_License_Check_001',
			'RVDec_License_Check_001',
			'DDPlus_license protection_001',
			'CodecDoc_ReleaseNotes_001',
			'CodecDoc_UserGuide_002',
			'CodecDoc_UserGuide_001',
			'Linux_EQ_Conf_BassBoost_001',
			'Linux_EQ_Conf_BassBoost_002',
			'Linux_EQ_Conf_BassBoost_003', 
			'LoopTest_MPEG4_002',
			'LoopTest_H264_003',
			'LoopTest_MemoryCheck_004',
			'MultiChannel_Conf_SoundOutput_003',
			'MultiChannel_Conf_SoundOutput_001',
			'MultiChannel_Conf_SoundOutput_002',
			'PkgTest_Acceptance_001',
			'Glsink_Pkg_Test_001',
			'Transcoding_001',
			'Transcoding_002',
			'Transcoding_003',
			'Transcoding_004',
			'Multi-overlay_001',
			'Multi-overlay_002',
			'Multi-instance_001',
			'Multi-instance_002',
			'Multi-instance_003',
			'Multi-instance_004',
			'Multi-instance_005',
			'Multi-instance_006',
			'Multi-instance_007',
			'Multi-instance_008',
			'Multi-instance_009',
			'Linux_App_Album_01',
			'VDOA_Test_001',
			'AC3Dec_Conf_TrickMode_001',
			'TrickMode_Conf_WMA_001',
			'TrickMode_Conf_WMAPro_001',
			'TrickMode_Conf_WMALossless_001',
			'TrickMode_Conf_MPEG4_001',
			'TrickMode_Conf_MPEG2_001',
			'VC1Dec_TrickMode_001',
			'H264Dec_Conf_TrickMode_001',
			'FLV_Conf_TrickMode_001',
			'OGGDec_Conf_TrickMode_001',
			'WAVDec_Conf_TrickMode_001',
			'AACLCDec_Conf_TrickMode_001',
			'AACPlusDec_Conf_TrickMode_001',
			'XvidDec_Conf_TrickMode_001',
			'MP3Dec_TrickMode_001',
			'DDPlusDec_Trickmode_001',
			'VC1Dec_Perf_DualDisplay_001',
			'MPEG4Dec_Perf_DualDisplay_001',
			'H264Dec_Perf_DualDisplay_001'
		);
		$module = array(
			'normal'=>array(
				'AudioRec',
				'H263Enc',
				'H264Enc',
				'MJPEGEnc',
				'MP3Enc',
				'MPEG4Enc',
				'Streaming',
				'VideoRec',
				'WMA8Enc'
			),
			'picture'=>array(
				'BMPDec', 
				'GifDec', 
				'JPEGDec', 
				'JPEGEnc', 
				'PNGDec'
			),
			'speech'=>array(
				'G.711',
				'G.723.1',
				'G.726',
				'G.729',
				'NB_AMR',
				'WB_AMR'
			),
			'del from linux'=>array(
				'FLAC',
				'RADec',
				'RVDec',
				'WEBPDec'
			)
			
		);
print_r("XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX");
print_r("\n<BR />");		
		foreach($this->parse_result as $sheet){
			foreach($sheet as $row){
				if(empty($row['android']) && empty($row['linux']))
					$parse_module[] = $row['codec_stream_format'];
				else if(!empty($row['android']) && empty($row['linux'])){
					$linux_module[] = $row['codec_stream_format'];
				}
			}
		}
		$ids = array();
		foreach($code as $streamid){
// print_r($streamid);
			$info['code'] = $streamid;
			$info['isactive'] = 99;
			$info['note'] = 'As Cases In XiaoTian 3.0';
			$codec_stream_id = $this->getId('codec_stream', $info, array('code'));
			if($codec_stream_id == 'error'){
				print_r($streamid);
				print_r("\n<BR />");
			}
			else 
				$ids[] = $codec_stream_id;
		}
print_r("zzzzzzzzzzzzzzzzzzzzzzzzzzz");
print_r(count($ids));
print_r("\n<BR />");
		unset($info);
		foreach($module as $type){
			foreach($type as $format){
				$info['name'] = $format;
				$format_id = $this->getId('codec_stream_format', $info, array('name'));
				
				if($format_id != 'error'){
					$res = $this->db->query("select id from codec_stream where codec_stream_format_id=".$format_id);
					while($row = $res->fetch()){
						$ids[] = $row['id'];
					}
				}
				else{
print_r("xxxxxxxxxxxxxxxxxxxxx");
					print_r($format);
					print_r("\n<BR />");
				}
			}
		}
print_r(count($ids));
print_r("\n<BR />");
		unset($info);
		//不存在的case_module
		foreach($parse_module as $format){
			$info['name'] = $format;
			$info['isactive'] = 2;
			$format_id = $this->getId('codec_stream_format', $info, array('name'));
			
			if($format_id != 'error'){
				$res = $this->db->query("select id, isactive from codec_stream where codec_stream_format_id=".$format_id);
				while($row = $res->fetch()){
print_r($row['isactive']);
print_r("\n<BR />");
					$ids[] = $row['id'];
				}
			}
			else{
print_r("xxxxxxxxxxxxxxxxxxxxx");
				print_r($format);
				print_r("\n<BR />");
			}
		}
print_r(count($ids));
print_r("\n<BR />");
		unset($info);
		//不属于linux的module
		foreach($linux_module as $format){
			$info['name'] = $format;
			$format_id = $this->getId('codec_stream_format', $info, array('name'));
			
			if($format_id != 'error'){
				$res = $this->db->query("select id from codec_stream where codec_stream_format_id=".$format_id);
				while($row = $res->fetch()){
					$ids[] = $row['id'];
				}
			}
			else{
print_r("xxxxxxxxxxxxxxxxxxxxx");
				print_r($format);
				print_r("\n<BR />");
			}
		}
//主要是tag上消除
print_r(count($ids));
print_r("\n<BR />");
		$ids = array_unique($ids);
print_r(count($ids));
print_r("\n<BR />");
		unset($info);
		$res = $this->db->query("select id, element_ids from tag where name = 'Linux'");
		if($info = $res->fetch()){
			$element_ids = $info['element_ids'];
			$tag_id = $info['id'];
		}
		$element_ids = explode(",", $element_ids);
print_r(count($element_ids));
print_r("\n<BR />");
		foreach($element_ids as $k=>$id){
			if(in_array($id, $ids)){

				unset($element_ids[$k]);
			}
		}
print_r(count($element_ids));
print_r("\n<BR />");
		$element_ids = implode(",", $element_ids);
		$this->db->update('tag', array('element_ids'=>$element_ids), 'id='.$tag_id);
		
		
	}
	
	public function getId($table, $valuePair, $keyFields = array(), &$is_new = true){
		static $elements = array();
		$cached = false;
		if (!empty($keyFields)){
			if(in_array('code', $keyFields)){
				$cached = true;
				foreach($keyFields as $k=>$v){
					if($v == 'code')
						$keyField = $keyFields[$k];
				}
			}
			else if(in_array('name', $keyFields)){
				$cached = true;
				foreach($keyFields as $k=>$v){
					if($v == 'name')
						$keyField = $keyFields[$k];
				}
			}
		}
		if (!$cached || empty($elements[$table][$valuePair[$keyField]])){
			$where = array();
			$realVP = array();
			$res = $this->db->query("describe $table");
			while($row = $res->fetch()){
				if (isset($valuePair[$row['Field']]))
					$realVP[$row['Field']] = $valuePair[$row['Field']];
			}
// if($table == 'testcase_ver')
// print_r($realVP);
			if (empty($keyFields))
				$keyFields = array_keys($realVP);
			foreach($keyFields as $k){
				$where[] = "$k=:$k";
				$whereV[$k] = $realVP[$k];
			}
			$res = $this->db->query("SELECT * FROM $table where ".implode(' AND ', $where), $whereV);
			if ($row = $res->fetch()){
				$this->db->update($table, $realVP, "id=".$row['id']);
				$is_new = false;
				return $row['id'];
			}
			return "error";
			// $is_new = true;
			// $this->db->insert($table, $realVP);
			// $element_id = $this->db->lastInsertId();
			// if ($cached)
				// $elements[$table][$keyField] = $element_id;
			// return $element_id;
		}
		$is_new = false;
		return $elements[$table][$valuePair[$keyField]];
	}
};

?>
