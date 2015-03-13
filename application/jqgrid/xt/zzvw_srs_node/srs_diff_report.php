<?php
require_once('base_report.php');

class srs_diff_report extends base_report{
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
		$result = $this->params['db']->query("SELECT * FROM tag WHERE id in ({$this->params['tag']}) ORDER BY modified DESC");
		while($row = $result->fetch()){
			$header0[] = array('label'=>$row['name'], 'width'=>300, 'index'=>'', 'columns'=>3);
			$header1[] = array('label'=>'Requirements Text / Data', 'width'=>500, 'index'=>'content_'.$row['id']);
			$header1[] = array('label'=>'Project', 'width'=>150, 'index'=>'project_'.$row['id']);
			$header1[] = array('label'=>'Updated', 'width'=>120, 'index'=>'updated_'.$row['id']);
			$this->hiddenHeader[] = 'srs_node_info_id_'.$row['id'];
			$changedColumn += 3;
		}
		$header0[] =  array('label'=>'Changed', 'width'=>300, 'index'=>'changed');
		$header1[] =  array('label'=>'', 'width'=>300, 'index'=>'changed');
		parent::setColumnHeader($header0, $worksheetIndex);
		parent::_writeHeader($worksheetIndex);
		parent::setColumnHeader($header1, $worksheetIndex, 2);
		parent::_writeHeader($worksheetIndex);
		$this->mergeCells($worksheetIndex, 1, 0, 2, 0);		
		$this->mergeCells($worksheetIndex, 1, 1, 2, 1);		
		$this->mergeCells($worksheetIndex, 1, $changedColumn, 2, $changedColumn);		
	}
	
	protected function getData($sheetIndex){
		$result = $this->params['db']->query("SELECT * FROM tag WHERE id in ({$this->params['tag']}) ORDER BY modified DESC");
		$tags = $result->fetchAll();
//print_r($tags);		
		// should generate the excel report
		foreach($tags as $tagRow){
			$result = $this->params['db']->query("SELECT * FROM vw_srs_node_history where id in ({$tagRow['element_id']}) ORDER BY category ASC, code ASC");
			$data[$tagRow['id']] = $result->fetchAll();
		}
		$tagFields = array('id', 'srs_node_info_id', 'content', 'project', 'prj_id', 'updated');
		foreach($data as $tagId=>$tagData){
			foreach($tagData as $d){
				if (empty($ret[$d['srs_node_id']]))
					$ret[$d['srs_node_id']] = $d;
				foreach($tagFields as $f){
					$ret[$d['srs_node_id']][$f."_".$tagId] = $d[$f];
					unset($ret[$d['srs_node_id']][$f]);
				}
			}
		}

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

		$style = array();
		$srs_node_info_id = 0;
		$content_key = 0;
        foreach($this->columnHeaders[$sheetIndex] as $columnHeader){
            if (!isset($columnHeader['index']))
                continue;
            $key = $columnHeader['index'];
            $style[$key] = 'normal';
            if (strpos($key, 'content_') !== false){
				if (preg_match('/content_(\d*)/', $key, $matches)){
					$tagId = $matches[1];
					if (!empty($srs_node_info_id) && isset($content["srs_node_info_id_$tagId"]) && $content["srs_node_info_id_$tagId"] != $srs_node_info_id){
						$style[$content_key] = 'warning';
					}
					$srs_node_info_id = isset($content["srs_node_info_id_$tagId"]) ? $content["srs_node_info_id_$tagId"] : 0;
					$content_key = $key;					
				}
			} 
		}
		return $style;
	}
}

?>
