<?php
require_once('file_parse.php');

class excel_parse extends file_parse{
	protected function _parse(){
		/**  Identify the type of $inputFileName  **/
		$inputFileType = PHPExcel_IOFactory::identify($this->fileName);
		/**  Create a new Reader of the type that has been identified  **/
		$reader = PHPExcel_IOFactory::createReader($inputFileType);
		$reader->setReadDataOnly(true);
		$objExcel = $reader->load($this->fileName);
		$this->analyzeExcel($objExcel);
	}

	protected function analyzeExcel($excel){
		foreach($excel->getWorksheetIterator() as $index=>$sheet){
			$title = $sheet->getTitle();
			// $title = strtolower($title);
			$needParse = true;
//print_r($this->sheetsNeedParse);
			if (!empty($this->params['sheetsNeedParse']) && !in_array($title, $this->params['sheetsNeedParse']))
				$needParse = false;
//print_r("title = $title, need = $needParse\n");
			if ($needParse){
				$method = preg_replace('/[\s-=]/', '_', $title);
				$method = 'analyze_'.$method;
//print_r("title = $title, method = $method\n");				
				if (method_exists($this, $method)){
					$this->{$method}($sheet, $title);
				}
				else{
					$this->default_analyze_sheet($sheet, $title);
				}
			}
		}
		$excel->disconnectWorksheets();
	}
		
}
?>
