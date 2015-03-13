<?php
require_once('importer_excel.php');

class xt_codec_stream_importer_trickmode_type_rules extends importer_excel{
	protected $total = 0;
	private $rulesInfo;
	
	protected function process(){
		parent::process();
		print_r("total = ".$this->total);
	}
	
	protected function processSheetData($title, $sheet_data){
		$total = 0;
// print_r($sheet_data);
		foreach($sheet_data as $result){
			$this->processRes($result, $title);
			$total ++;
		}
		$this->processRules();
	}
	
	protected function processRes($result, $title){
		$resInfo['testcase_id'] = $this->getElementId('testcase', array('code'=>$result['code']), array('code'));
		$resInfo['mark'] = $result['mark'];
		unset($result['mark']);
		unset($result['code']);	
		foreach($result as $k=>$v){
			if($v == 'Y'){
				$k = ucwords($k);
				//$id = $this->getElementId('codec_stream_type', array('name'=>$k), array('name'));
				$this->rulesInfo[$k][$resInfo['testcase_id']] = $resInfo['testcase_id'] ; 
			}
		}
	}
	
	private function processRules(){
		foreach($this->rulesInfo as $type=>$data){
			$rules['testcase_ids'] = implode(",", $data);
			$rules['name'] = $type;
			$rules['mark'] = '';
			$codec_stream_type_id = $this->getElementId('codec_stream_type', $rules, array('name'));
		}
	}
};

?>
