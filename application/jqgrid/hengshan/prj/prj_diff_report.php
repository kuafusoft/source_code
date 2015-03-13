<?php
require_once('base_report.php');

class prj_diff_report extends base_report{
    private $hiddenHeader = array();
	public function __construct($sheetTitles, $params = array()){
		parent::__construct($sheetTitles, $params);
	}
	
	protected function _writeHeader($worksheetIndex, $setWidth = true, $mergeColumns = array()){
	    $changedColumn = 2;
		$header0 = array(
			array('label'=>'Component', 'width'=>160, 'index'=>'category'),
			array('label'=>'Identifier', 'width'=>200, 'index'=>'code'),
		);
		$header1 = array(			
			array('label'=>'', 'width'=>160, 'index'=>'category'),
			array('label'=>'', 'width'=>200, 'index'=>'code'),
		);
		$result = $this->params['db']->query("SELECT * FROM prj WHERE id in ({$this->params['element']}) ORDER BY created DESC");
		while($row = $result->fetch()){
			$header0[] = array('label'=>$row['name'], 'width'=>300, 'index'=>'', 'columns'=>2);
			$header1[] = array('label'=>'Requirements Text / Data', 'width'=>500, 'index'=>'content_'.$row['id']);
			$header1[] = array('label'=>'Updated', 'width'=>120, 'index'=>'updated_'.$row['id']);
			$this->hiddenHeader[] = 'srs_node_info_id_'.$row['id'];
			$changedColumn += 2;
		}
		$header0[] = array('label'=>'Changed', 'width'=>160, 'index'=>'changed');
		$header1[] = array('label'=>'', 'width'=>160, 'index'=>'changed');
		parent::setColumnHeader($header0, $worksheetIndex);
		parent::_writeHeader($worksheetIndex);
		parent::setColumnHeader($header1, $worksheetIndex, 2);
		parent::_writeHeader($worksheetIndex);
		$this->mergeCells($worksheetIndex, 1, 0, 2, 0);		
		$this->mergeCells($worksheetIndex, 1, 1, 2, 1);	
		$this->mergeCells($worksheetIndex, 1, $changedColumn, 2, $changedColumn);		
	}
	
	protected function getData($sheetIndex){
//print_r($this->params);	
        $ret = array();
		$result = $this->params['db']->query("SELECT * FROM vw_srs_node WHERE prj_id in (".$this->params['element'].") ORDER BY category ASC, code ASC");
        while($row = $result->fetch()){
            if (empty($ret[$row['srs_node_id']]))
                $ret[$row['srs_node_id']] = array('category'=>$row['category'], 'code'=>$row['code']);
            $ret[$row['srs_node_id']]['content_'.$row['prj_id']] = $row['content'];
            $ret[$row['srs_node_id']]['updated_'.$row['prj_id']] = $row['updated'];
            $ret[$row['srs_node_id']]['srs_node_info_id_'.$row['prj_id']] = $row['srs_node_info_id'];
        }
//print_r($ret);		
		return $ret;
	}
	
    protected function calcStyle(&$content, $sheetIndex, $defaultStyle = array()){
		$style = array();
		$hiddenValue = array();
		foreach($this->hiddenHeader as $each){
            $hiddenValue[] = isset($content[$each]) ? $content[$each] : " ";
        }
        $hiddenValue = array_unique($hiddenValue);
        $warning = false;
        if (count($hiddenValue) > 1){
            $warning = true;
        }
        $content['changed'] = $warning ? 'true' : 'false';
		$srs_node_info_id = 0;
		$content_key = 0;
		
        foreach($this->columnHeaders[$sheetIndex] as $columnHeader){
            if (!isset($columnHeader['index']))
                continue;
            $key = $columnHeader['index'];
            $style[$key] = $warning ? 'warning' : 'normal';
		}
		return $style;
	}
}

?>
