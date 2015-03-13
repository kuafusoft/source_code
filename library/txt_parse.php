<?php
require_once('file_parse.php');

class txt_parse extends file_parse{
	protected function _parse(){
		$this->handler = fopen($this->filename, 'rb');
		if(!$this->handler)
			die('fail to open the file '.$filename);

		while(!feof($this->handler)){
			$buffer = trim(fgets($this->handler, 4096));
			$this->parseLine($buffer);
			$this->currentLine ++;
		}
		fclose($this->handler);
	}
	
	protected function parseLine($buffer){
		print_r($buffer);
	}
}
?>
