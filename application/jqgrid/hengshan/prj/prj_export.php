<?php
require_once('base_report.php');

class prj_export extends base_report{
	public function __construct($sheetTitles, $params = array()){
		parent::__construct($sheetTitles, $params);
	}
	
	protected function _writeHeader($worksheetIndex, $setWidth = true, $mergeColumns = array()){
		$header0 = array(
			array('label'=>'Component', 'width'=>160, 'index'=>'category'),
			array('label'=>'Identifier', 'width'=>200, 'index'=>'code'),
			array('label'=>'Requirements Text / Data', 'width'=>500, 'index'=>'content'),
			array('label'=>'Project', 'width'=>150, 'index'=>'project'),
			array('label'=>'Updated', 'width'=>120, 'index'=>'updated'),
		);
		parent::setColumnHeader($header0, $worksheetIndex);
		parent::_writeHeader($worksheetIndex);
	}
	
	protected function getData($sheetIndex){
		$result = $this->params['db']->query("SELECT * FROM vw_srs_node where prj_id={$this->params['element']} ORDER BY category ASC, code ASC");
        $ret = $result->fetchAll();
		return $ret;
	}
}

?>
