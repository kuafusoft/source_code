<?php
require_once('PHPExcel.php');
require_once('toolfactory.php');
require_once('dbfactory.php');
require_once('exporter_base.php');

function replace($matches, $content){
//	print_r($matches);
	$content = json_decode($content, true);
//	print_r($content);
	$m = substr($matches[1], 2, -1);
//print_r("m = $m\n");
	return isset($content[$m]) ? $content[$m] : '';
}

class exporter_excel extends exporter_base{
    protected $objExcel = null;
    protected $styles = array();
	protected $tool = null;
	
	protected function init($params = array()){
		parent::init($params);
		$this->fileName .= '.xlsx';
		$this->tool = toolFactory::get('db');
		$this->tool->setDb($params['db']);
	}
	
	public function setOptions($jqgrid_action){
		$table_desc = $jqgrid_action->getTable_desc();
		$titles = $this->getTitle($table_desc);
		$data = $this->getData($table_desc, array(array('field'=>'id', 'op'=>'IN', 'value'=>$this->params['id'])));
		$this->params['sheets'][0] = array('title'=>$table_desc->getCaption(), 'startRow'=>2, 'startCol'=>1, 'header'=>array('rows'=>array($titles)), 'data'=>$data);
		$this->setGroupInfo();
	}
	
	/* 设置分组信息
	分组信息包含：哪个字段分组，是否需要增加SubTotal信息，subTotal哪些字段
	*/
	protected function setGroupInfo(){
		$this->params['sheets'][0]['groups'] = array(
		/*
			'id'=>array('subtotal'=>array('field1', 'field2', 'field4')),
			'name'=>array()
		*/
		);
	}
	
	protected function getTitle($table_desc){
		$options = $table_desc->getOptions();
//		$options['gridOptions']['colModel'] = $jqgrid_action->trimColModel($options['gridOptions']['colModel']);
		return $options['gridOptions']['colModel'];
	}
	
	protected function getData($table_desc, $searchConditions = array(), $order = array()){
		$list_params = array('db'=>$this->params['db'], 'table'=>$this->params['table'], 'id'=>$this->params['id']);
		$list_action = actionFactory::get(null, 'list', $list_params);
		$ret = $list_action->getList();
		return $ret;
		
		$db = dbFactory::get($table_desc->get('db'));
		$sqls = json_decode($this->params['sqls'], true);
		$sqls['where'] = $table_desc->get('table').".id IN (".implode(',', $this->params['id']).") AND ".$sqls['where'];
//		$sqls = $table_desc->calcSqlComponents(array('db'=>$table_desc->get('db'), 'table'=>$table_desc->get('table'), 'order'=>$order, 'searchConditions'=>$searchConditions), false);
// print_r($sqls);
		$sql = $table_desc->getSql($sqls, false);
//print_r($sql);		
		$res = $db->query($sql);
		$rows = array();
		while($row = $res->fetch()){
			$row = $table_desc->getMoreInfoForRow($row);
			$rows[] = $row; 
		}
		return $rows;
	}
	
	protected function setStyles(){
        $normalStyle = array(
        	'font' => array(
        		'size'=>11,
        		'bold' => false,
			),
        	'alignment' => array(
        		'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
        	),
        	'borders' => array(
        		'allborders' => array(
        			'style' => PHPExcel_Style_Border::BORDER_THIN,
        		),
        	),
        	'fill' => array(
        		'type' => PHPExcel_Style_Fill::FILL_SOLID,
        		'color' => array(
        			'argb' => 'FFF7F7F7',
        		),
        	),
		);
		$altStyle = $normalStyle;
		$altStyle['fill']['color']['argb'] = 'FFE0E0F7';
		$rightStyle = $normalStyle;
		$rightStyle['alignment']['horizontal'] = PHPExcel_Style_Alignment::HORIZONTAL_RIGHT;
		
		$warningStyle = array(
        	'font' => array(
        		'size'=>12,
        		'bold' => true,
        		'color'=>array(
        			'argb' => 'FFFF0000',
        		),
			),
        	'borders' => array(
        		'allborders' => array(
        			'style' => PHPExcel_Style_Border::BORDER_THIN,
        		),
        	),
        	'fill' => array(
        		'type' => PHPExcel_Style_Fill::FILL_SOLID,
        		'color' => array(
        			'argb' => 'FFFFFF00',
        		),
        	),
		);
		$highlightStyle = $warningStyle;
		$highlightStyle['font']['color']['argb'] = 'FF0000FF';
		
		$titleStyle = array(
        	'font' => array(
        		'size'=>14,
        		'bold' => true,
//        		'italic'=>true,
        	),
        	'alignment' => array(
        		'horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        		'vertical'=>PHPExcel_Style_Alignment::VERTICAL_CENTER,
        	),
        	'borders' => array(
        		'allborders' => array(
        			'style' => PHPExcel_Style_Border::BORDER_THIN,
        		),
        	),
        	'fill' => array(
        		'type' => PHPExcel_Style_Fill::FILL_GRADIENT_LINEAR,
        		'rotation' => 90,
        		'startcolor' => array(
        			'argb' => 'FFA0A0A0',
        		),
        		'endcolor' => array(
        			'argb' => 'FFFFFFFF',
        		),
        	),
        );
		$summaryStyle = array(
        	'font' => array(
        		'bold' => true,
        		'size'=>20,
        		'color'=>array(
        			'argb' => 'FFF2F2F2',
        		),
        	),
        	'fill' => array(
        		'type' => PHPExcel_Style_Fill::FILL_SOLID,
        		'color'=>array(
        			'argb' => 'FF92D050',
        		),
        	),
        );
        $totalStyle = array(
        	'font' => array(
        		'bold' => true,
        		'size'=>14,
        	),
        	'borders' => array(
        		'allborders' => array(
        			'style' => PHPExcel_Style_Border::BORDER_THIN,
        		),
        	),
        	'fill' => array(
        		'type' => PHPExcel_Style_Fill::FILL_SOLID,
        		'color'=>array(
        			'argb' => 'FFC8D8EE',
        		),
        	),
        );
		$subtotalStyle = $totalStyle;
		$subtotalStyle['alignment']['horizontal'] = PHPExcel_Style_Alignment::HORIZONTAL_RIGHT;
		
		$normal_percentStyle = $normalStyle;
		$normal_percentStyle['numberformat'] = array('code' => '#0.00%');
		$normal_highPercent = $normal_middlePercent = $normal_lowPercent = $normal_percentStyle;
		$normal_highPercent['fill']['color'] = array('argb'=>'FF00FF00');
		$normal_middlePercent['fill']['color'] = array('argb'=>'FFFFFF00');
		$normal_lowPercent['fill']['color'] = array('argb'=>'FFFF0000');
		
		$alt_percentStyle = $altStyle;
		$alt_percentStyle['numberformat'] = array('code' => '#0.00%');
		$alt_highPercent = $alt_middlePercent = $alt_lowPercent = $alt_percentStyle;
		$alt_highPercent['fill']['color'] = array('argb'=>'FF00FF00');
		$alt_middlePercent['fill']['color'] = array('argb'=>'FFFFFF00');
		$alt_lowPercent['fill']['color'] = array('argb'=>'FFFF0000');

		$this->styles = array(//'percent'=>$percentStyle, 'high_percent'=>$highPercent, 'middle_percent'=>$middlePercent, 'low_percent'=>$lowPercent, 
			'normal_percent'=>$normal_percentStyle, 'normal_high_percent'=>$normal_highPercent, 'normal_middle_percent'=>$normal_middlePercent, 'normal_low_percent'=>$normal_lowPercent,
			'alt_percent'=>$alt_percentStyle, 'alt_high_percent'=>$alt_highPercent, 'alt_middle_percent'=>$alt_middlePercent, 'alt_low_percent'=>$alt_lowPercent,
			'title'=>$titleStyle, 'highlight'=>$highlightStyle, 'warning'=>$warningStyle, 'normal'=>$normalStyle, 'alt'=>$altStyle, 'right'=>$rightStyle, 'summary'=>$summaryStyle, 'total'=>$totalStyle, 'subtotal'=>$subtotalStyle);
	}
	
	public function export(){
		if (empty($this->params['title']))
			$this->params['title'] = 'Created by XiaoTian';
		if (empty($this->params['subject']))
			$this->params['subject'] = 'Created by XiaoTian';
		if (empty($this->params['description']))
			$this->params['description'] = 'This document is created by XiaoTian';
		if (empty($this->params['keywords']))
			$this->params['keywords'] = 'XiaoTian';
		if (empty($this->params['category']))
			$this->params['category'] = 'XiaoTian Generated Report';
		if (empty($this->params['filename']))
			$this->params['filename'] = 'tmp.xlsx';
		$this->params['creator'] = $this->userInfo->nickname;
        $this->objExcel = new PHPExcel();
        $this->objExcel->getProperties()
            ->setTitle($this->params['title'])
            ->setSubject($this->params['subject'])
            ->setCreator($this->params['creator'])
            ->setLastModifiedBy($this->params['creator'])
            ->setDescription($this->params['description'])
            ->setKeywords($this->params['keywords'])
            ->setCategory($this->params['category']);
        PHPExcel_Settings::setCacheStorageMethod(PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip);
        $this->objExcel->getDefaultStyle()->getAlignment()->setWrapText(true);
		$this->setStyles();
		
		return parent::export();
	}
	
	protected function _export(){
		foreach($this->params['sheets'] as $sheetIndex=>&$sheet_desc){
			$sheet_desc['sheetIndex'] = $sheetIndex;
			if ($sheetIndex)
				$this->objExcel->createSheet();
		    $this->objExcel->setActiveSheetIndex($sheetIndex);
		    $sheet = $this->objExcel->getActiveSheet()->setTitle(substr($sheet_desc['title'], 0, 31)); // Title最大字符数31
			$this->exportSheet($sheetIndex, $sheet_desc);
			$sheetIndex ++;
		}
	}
	
	protected function save(){
		if (empty($dir))
			$dir = $this->tool->getExportDir();
		$fileName = $this->tool->formatFileName($dir .'/'.$this->fileName, '.xlsx');
        $writer = new PHPExcel_Writer_Excel2007($this->objExcel);
        $writer->save($fileName);
        return $fileName;
	}
	
	protected function exportSheet($sheetIndex, &$sheet_desc){
		if(!empty($sheet_desc['pre_text']))
			$this->writeCell($sheetIndex, 1, 0, $sheet_desc['pre_text']);
		if(!empty($sheet_desc['pre_img'])){
			$img = $sheet_desc['pre_img']['img'];
			$height = $sheet_desc['pre_img']['height'];
			$width = $sheet_desc['pre_img']['width'];
			$this->insertPicture($sheetIndex, 1, 0, $img, $height, $width);
		}
		if(!empty($sheet_desc['header']))
			$this->writeHeader($sheetIndex, $sheet_desc);
		if (!empty($sheet_desc['data']))
			$this->exportData($sheetIndex, $sheet_desc['data']);

		$this->lastTotal($sheetIndex, $sheet_desc);
	}

	protected function lastTotal($sheetIndex, $sheet_desc){
		if(empty($sheet_desc['last_total']))
			return;
		$totalRow = $this->getTotalRow($sheetIndex, $sheet_desc['last_total']);
		$style = $this->fillInStyle($sheetIndex, 'subtotal');
		$this->writeRow($totalRow, $sheetIndex, $style);
	}
	
	protected function getTotalRow($sheetIndex, $last_total_desc){
		$totalRow = array();
		$totalRow[$last_total_desc['locate']] = 'Total';
		foreach($last_total_desc['fields'] as $f){
			$totalRow[$f] = '='.$this->sumCells($sheetIndex, $f, $this->params['sheets'][$sheetIndex]['startRow'], $this->params['sheets'][$sheetIndex]['nextRow'] - 1);
		}
		return $totalRow;
	}
	
	protected function exportData($sheetIndex, $data){
    	if (is_object($data)){
			while($row = $data->fetch()){
				// 考虑group的事情
				$ret = $this->handleGroups($row, $sheetIndex);
				$this->writeRow($row, $sheetIndex);
				if ($ret['collapse']){
					$this->objExcel->getActiveSheet()->getRowDimension($this->params['sheets'][$sheetIndex]['nextRow'] - 1)->setOutlineLevel($ret['level'] + 1);
					$this->objExcel->getActiveSheet()->getRowDimension($this->params['sheets'][$sheetIndex]['nextRow'] - 1)->setVisible(false);
					$this->objExcel->getActiveSheet()->getRowDimension($this->params['sheets'][$sheetIndex]['nextRow'] - 1)->setCollapsed(true);
				}
			}
		}
		else if (is_array($data)){
	        foreach($data as $key=>$row){
				// 考虑group的事情
				$ret = $this->handleGroups($row, $sheetIndex);
	            $this->writeRow($row, $sheetIndex, array(), $key);
				if ($ret['collapse']){
					$this->objExcel->getActiveSheet()->getRowDimension($this->params['sheets'][$sheetIndex]['nextRow'] - 1)->setOutlineLevel($ret['level'] + 1);
					$this->objExcel->getActiveSheet()->getRowDimension($this->params['sheets'][$sheetIndex]['nextRow'] - 1)->setVisible(false);
					$this->objExcel->getActiveSheet()->getRowDimension($this->params['sheets'][$sheetIndex]['nextRow'] - 1)->setCollapsed(true);
				}
	        }
		}        
		$ret = $this->handleGroups(array(), $sheetIndex);
	}
	
	protected function insertPicture($sheedIndex, $row, $col, $img, $height, $width){
		$objDrawing = new PHPExcel_Worksheet_Drawing();
		$objDrawing->setName('Logo');
		$objDrawing->setDescription('Logo');
		$objDrawing->setPath($img);
		$objDrawing->setHeight($height);
		$objDrawing->setWidth($width);
		$objDrawing->setCoordinates($this->RC2LN($row, $col));
		
		$this->objExcel->setActiveSheetIndex($sheedIndex);
		$objDrawing->setWorksheet($this->objExcel->getActiveSheet());
	}
	/*
	header = array(
		'rows'=>array(
			array(
				array('index'=>'', 'name'=>'', 'label'=>'', 'formatter'=>'', 'formatoptions'=>array(), 'cols'=>1),
				array(),
			},
			array(
			
			)
		),
		'mergeCols'=>array(
			col=>array(startrow,endrow),
			col=>array()
		)
	)
	*/
	protected function writeHeader($sheetIndex, &$sheet_desc){
		if (!isset($sheet_desc['startRow']))$sheet_desc['startRow'] = 2;
		if (!isset($sheet_desc['startCol']))$sheet_desc['startCol'] = 1;
		$startRow = $row = $sheet_desc['startRow'];
		$startCol = $sheet_desc['startCol'];
		$sheet_desc['nextRow'] = $startRow + count($sheet_desc['header']['rows']);
		$isSecond = false;
		foreach($sheet_desc['header']['rows'] as &$header){
			$col = $startCol;
			foreach($header as &$cell){
				$hidden = isset($cell['hidden']) ? $cell['hidden'] : false;
				$hidedlg = isset($cell['hidedlg']) ? $cell['hidedlg'] : false;
				if($hidden && $hidedlg){
					$cell['index'] = ''; // do not export the column
					continue;
				}
				if (!isset($cell['cols']))
					$cell['cols'] = 1;
				if (!isset($cell['width']))
					$cell['width'] = 100;
				$this->writeHeaderCell($sheetIndex, $cell, $row, $col, $isSecond);
				if ($hidden)
					$this->hideColumn($sheetIndex, $col);
				$col += $cell['cols'];
			}
			$row ++;
			$isSecond = true;
		}
//		$sheet_desc['nextRow'] = $row;
		if (isset($sheet_desc['header']['mergeCols'])){
			foreach($sheet_desc['header']['mergeCols'] as $field=>$rows){
				$col = $this->headerIndex($sheetIndex, $field);
				$this->mergeCells($sheetIndex, $rows[0], $col, $rows[1], $col);
			}
		}
	}

	protected function hideColumn($sheetIndex, $col){
		$this->objExcel->getActiveSheet()->getColumnDimensionByColumn($col)->setVisible(false);
	}
	
	protected function writeHeaderCell($sheetIndex, $cell, $row, $col, $isSecond = false){
		if (!isset($cell['comment']))$cell['comment'] = '';
		$style = $this->styles['title'];
		if ($isSecond){
			$style['font']['size'] -= 2;
			$style['font']['bold'] = false;
		}
        $this->objExcel->setActiveSheetIndex($sheetIndex);
        $sheet = $this->objExcel->getActiveSheet();
		// $sheet->getStyleByColumnAndRow($col, $row)->applyFromArray($this->styles['title']);
		$setWidth = isset($cell['setwidth']) ? $cell['setwidth'] : true;
		if ($setWidth)
			$sheet->getColumnDimensionByColumn($col)->setWidth($cell['width']/6);
//print_r($cell);			
		$this->writeCell($sheetIndex, $row, $col, $cell['label'], $cell['comment']);
		$this->objExcel->getActiveSheet()->getStyleByColumnAndRow($col, $row)->applyFromArray($style);
		if ($cell['cols'] > 1){
			for($i = 1; $i < $cell['cols']; $i ++){
				$this->writeCell($sheetIndex, $row, $col + $i, '', '');
				$this->objExcel->getActiveSheet()->getStyleByColumnAndRow($col + $i, $row)->applyFromArray($style);
			}
			$startRow = $endRow = $row;
			$endCol = $col + $i - 1;
			$this->mergeCells($sheetIndex, $row, $col, $row, $endCol);
		}
	}
	
	public function addStyle($style){ // style = array('name'=>array())
		foreach($style as $name=>$p)
			$this->styles[$name] = $p;
	}
	
    public function getObjExcel(){
		return $this->objExcel;
	}
	
	public function getSheetIndex($sheetIndex){
		if (empty($sheetIndex))
			return 0;
		if (is_int($sheetIndex)){
			return $sheetIndex;
		}
		if (is_string($sheetIndex)){
			$index = 0;
			foreach($this->params['sheets'] as $index=>$sheet_desc){
				if ($sheet_desc['title'] == $sheetIndex)
					return $index;
			}
		}
		return 0;
	}
	
    protected function writeRow($content, $sheetIndex = 0, $defaultStyle = array(), $contentKey = null){
// print_r($content);	
		$sheetIndex = $this->getSheetIndex($sheetIndex);
        if (empty($this->params['sheets'][$sheetIndex]))
            return;
		$sheet_desc = $this->params['sheets'][$sheetIndex];
        $column = $sheet_desc['startCol'];
		$row = $sheet_desc['nextRow'];
		
//    	$style = $this->calcStyle($content, $sheetIndex, $defaultStyle);
		$content = $this->translate($content, $sheetIndex);
// print_r($content);
		$i = count($sheet_desc['header']['rows']) - 1;
        foreach($sheet_desc['header']['rows'][$i] as $columnHeader){
            if (empty($columnHeader['index']))
                continue;
            $key = $columnHeader['index'];
			$default = isset($defaultStyle[$key]) ? $defaultStyle[$key] : '';
			$v = $content[$key];
//			$v = $this->translate($content, $key, $columnHeader);
			$ref = '';
			if (!empty($columnHeader['ref']) && !empty($content))
				$ref = $this->getRef($content, $columnHeader['ref']);
// print_r("key = $key, v = $v\n");
            $this->writeCell($sheetIndex, $row, $column, $v, '', $ref);
			$style = $this->calcStyle($sheetIndex, $key, $content, $default);
			if (!isset($this->styles[$style])) $style = 'normal';
			$this->objExcel->getActiveSheet()->getStyleByColumnAndRow($column, $row)->applyFromArray($this->styles[$style]);
			$column ++;
            if ($columnHeader['cols'] > 1){
				for($j = 1; $j < $columnHeader['cols']; $j ++){
	            	$this->writeCell($sheetIndex, $row, $column, '');
					$this->objExcel->getActiveSheet()->getStyleByColumnAndRow($column, $row)->applyFromArray($this->styles[$style]);
					$column ++;
				}
				$endCol = $column - 1;
	        	$startCol = $endCol - $columnHeader['cols'] + 1;
				$this->mergeCells($sheetIndex, $row, $startCol, $row, $endCol);
			}
        }
        $this->params['sheets'][$sheetIndex]['nextRow'] ++;
    }

	// $this->params['sheets'][0]['groups'] = array(
		// 'id'=>array('subtotal'=>array('locate'=>'field', 'fields'=>array('field1', 'field2', 'field4'))),
		// 'name'=>array()
	// );
	protected function handleGroups($content, $sheetIndex){
		static $last = array();
		if (!empty($this->params['sheets'][$sheetIndex]['groups'])){
			$groups = $this->params['sheets'][$sheetIndex]['groups'];
			$style = $this->fillInStyle($sheetIndex, 'subtotal');
			
			foreach($groups as $level=>$prop){
				$field = $prop['index'];
				if (empty($last[$sheetIndex][$field])){
					$last[$sheetIndex][$field] = array(
						'startrow'=>$this->params['sheets'][$sheetIndex]['nextRow'],
						'lastcontent'=>isset($content[$field]) ? $content[$field] : null,
						'col'=>$this->headerIndex($sheetIndex, $field)
					);
					continue;
				}
			}
			return $this->group($sheetIndex, $groups, $content, $last);
		}
	}
	
	protected function group($sheetIndex, $groups, $content, &$last){
		$ret = array('collapse'=>false, 'level'=>0);
		$levels = count($groups);
		$style = $this->fillInStyle($sheetIndex, 'subtotal');
		for($level = 0; $level < $levels; $level ++){
			$prop = $groups[$level];
			$field = $prop['index'];
			if (!isset($content[$field]))$content[$field] = null;
			if ($content[$field] != $last[$sheetIndex][$field]['lastcontent']){ //不同，则该级别后的所有级别都应分组
				for($current = $levels - 1; $current >= $level; $current --){
					$handled = $groups[$current];
					$handledField = $handled['index'];
					if (!isset($content[$handledField]))$content[$handledField] = null;
					if (!empty($handled['subtotal'])){
						$subtotalRow = $this->getSubtotalRow($sheetIndex, $handledField, $handled['subtotal'], $last);
						$this->writeRow($subtotalRow, $sheetIndex, $style);
						for($i = $current + 1; $i < $levels; $i ++){ //那些没有subtotal并且已经赋予了startrow的字段需要重新处理
							$last[$sheetIndex][$groups[$i]['index']]['startrow'] ++;
						}
						$ret['collapse'] = true;
						$ret['level'] = $level;
					}
//print_r(array($sheetIndex, $handledField, $last[$sheetIndex][$handledField]['startrow'], $last[$sheetIndex][$handledField]['col'], $this->params['sheets'][$sheetIndex]['nextRow'] - 1, $last[$sheetIndex][$handledField]['col']));
					$this->mergeCells($sheetIndex, $last[$sheetIndex][$handledField]['startrow'], $last[$sheetIndex][$handledField]['col'], $this->params['sheets'][$sheetIndex]['nextRow'] - 1, $last[$sheetIndex][$handledField]['col']);
					$last[$sheetIndex][$handledField]['startrow'] = $this->params['sheets'][$sheetIndex]['nextRow'];
					$last[$sheetIndex][$handledField]['lastcontent'] = $content[$handledField];
				}
				break;
			}
			elseif (!empty($prop['subtotal'])){
				$ret['collapse'] = true;
				$ret['level'] = $level;
			}
		}
		return $ret;
	}
	
	protected function getSubtotalRow($sheetIndex, $field, $subtotal, $last){
		$subtotalRow[$subtotal['locate']] = 'Sub-total';
		if(isset($subtotal['label']))
			$subtotalRow[$subtotal['locate']] = $subtotal['label'];
		if(!empty($subtotal['count'])){
			$subtotalRow[$subtotal['locate']] = $this->params['sheets'][$sheetIndex]['nextRow'] - $last[$sheetIndex][$field]['startrow'];
		}
		else{
			foreach($subtotal['fields'] as $f){
				$subtotalRow[$f] = '='.$this->sumCells($sheetIndex, $f, $last[$sheetIndex][$field]['startrow'], $this->params['sheets'][$sheetIndex]['nextRow'] - 1);
			}
		}
//print_r($subtotalRow);		
		return $subtotalRow;
	}
	
	protected function collapse($sheetIndex, $row){
    	$this->objExcel->setActiveSheetIndex($sheetIndex);
		$this->objExcel->getActiveSheet()->getRowDimension($row)->setCollapsed(true);
	}
	
	protected function getRef($content, $ref){
//print_r("ref=$ref, content=");		
//print_r($content);		
		return preg_replace_callback('/(:\{.*?})/', create_function('$matches', 'return replace($matches,\''.json_encode($content).'\');'), $ref);
	}
			
	protected function calcStyle($sheetIndex, $headerIndex, $content, $default = ''){
		$column = $this->headerIndex($sheetIndex, $headerIndex);
		$count = count($this->params['sheets'][$sheetIndex]['header']['rows']);
// print_r("index = $sheetIndex, headerIndex = $headerIndex, count = $count, startCol = {$this->params['sheets'][$sheetIndex]['startCol']}\n");	
// print_r($this->params['sheets'][$sheetIndex]['header']['rows'][$count - 1]);
		$columnHeader = $this->params['sheets'][$sheetIndex]['header']['rows'][$count - 1][$column - $this->params['sheets'][$sheetIndex]['startCol']];
//		$headerIndex = $columnHeader['index'];
		$style = isset($columnHeader['style']) ? $columnHeader['style'] : 
			(!empty($default) ? $default : 
				($this->params['sheets'][$sheetIndex]['nextRow'] %2 ? 'normal' : 'alt'));
		if ($style == 'percent'){
			$v = $this->getCalculatedValue($sheetIndex, $headerIndex, $this->params['sheets'][$sheetIndex]['nextRow']);
			if (is_numeric($v)){
				if ($v >= 0.8)
					$style = 'high_percent';
				elseif ($v >= 0.6)
					$style = 'middle_percent';
				elseif ($v >= 0)
					$style = 'low_percent';
			}
		}
		if (in_array($style, array('percent', 'high_percent', 'middle_percent', 'low_percent'))){
			if ($this->params['sheets'][$sheetIndex]['nextRow'] %2)
				$style = 'normal_'.$style;
			else
				$style = 'alt_'.$style;
		}
		return $style;
	}
	
	protected function fillInStyle($sheetIndex, $style){
        $styles = array();
		$count = count($this->params['sheets'][$sheetIndex]['header']['rows']);
		foreach($this->params['sheets'][$sheetIndex]['header']['rows'][$count - 1] as $columnHeader){
            if (!isset($columnHeader['index']))
                continue;
            $key = $columnHeader['index'];
			$styles[$key] = $style;
		}
		return $styles;
	}
	
    protected function writeCell($worksheetIndex, $row, $column, $v, $comment = '', $ref = ''){
		if (empty($style))
			$style = 'normal';
    	$this->objExcel->setActiveSheetIndex($worksheetIndex);
		if(is_null($v))
			$v = '';
		// elseif(strpos($v, '=') === 0){
			// $v = "'".$v;
		// }
		$this->objExcel->getActiveSheet()->setCellValueByColumnAndRow($column, $row, $v);
		
		$LN = $this->RC2LN($row, $column);
		if (!empty($comment)){
			$this->objExcel->getActiveSheet()->getComment($LN)->setAuthor($this->params['creator']);
			$objCommentRichText = $this->objExcel->getActiveSheet()->getComment($LN)->getText()->createTextRun($this->params['creator'].':');
			$objCommentRichText->getFont()->setBold(true);
			$this->objExcel->getActiveSheet()->getComment($LN)->getText()->createTextRun("\r\n");
			$this->objExcel->getActiveSheet()->getComment($LN)->getText()->createTextRun($comment);
		}
		
		if (!empty($v) && !empty($ref)){
			$this->objExcel->getActiveSheet()->getCell($LN)->getHyperlink()->setUrl("sheet://'$ref'!A1");			
		}
    }
	
	protected function translate($content, $sheetIndex){
		$sheet_desc = $this->params['sheets'][$sheetIndex];
		$i = count($sheet_desc['header']['rows']) - 1;
        foreach($sheet_desc['header']['rows'][$i] as $k=>$header){
			$key = $header['index'];
			if(!empty($header['formatoptions']['value'])){
				if(empty($header['str2array']))
					$this->params['sheets'][$sheetIndex]['header']['rows'][$i][$k]['str2array'] = $this->tool->str2Array($header['formatoptions']['value']);
				
				$data = $this->params['sheets'][$sheetIndex]['header']['rows'][$i][$k]['str2array'];//$this->tool->str2Array($header['formatoptions']['value']);
			}
// print_r($key."\n");
// print_r($content[$key]."\n");
			if (!isset($content[$key]))
				$v = '';
			else
				$v = $content[$key];
			if (!empty($v)){
				if(!empty($header['formatter'])){
// print_r($header['formatter']);			
					switch($header['formatter']){
						case 'select':
						case 'checkbox':
						case 'select_showlink':
						case 'resultLink':
						case 'testorLink':
						case 'bResultLink':
							if (!empty($data[$v]))
								$v = $data[$v];
							break;
						case 'ids':
							$vs = explode(',', $v);
							$each = array();
							foreach($vs as $tmp){
								if (!empty($tmp)){
									if(isset($data[$tmp]))
										$each[] = $data[$tmp];
									else	
										$each[] = $tmp;
								}
							}
							$v = implode(',', $each);
							break;
						case 'multi_row_edit':
							$last_v = array();
							foreach($v as $v_item){
								if(empty($v_item))
									continue;
								foreach($v_item as $kk=>$ee){
	// print_r("kk = $kk, ee= $ee\n");							
									if(!empty($header['temp'][$kk]['formatoptions']['value'])){
										if(empty($header['temp'][$kk]['str2array']))
											$this->params['sheets'][$sheetIndex]['header']['rows'][$i][$k]['temp'][$kk]['str2array'] = 
												$this->tool->str2Array($header['temp'][$kk]['formatoptions']['value']);
									}
									if(isset($this->params['sheets'][$sheetIndex]['header']['rows'][$i][$k]['temp'][$kk]['str2array']))
										$v_item[$kk] = $this->params['sheets'][$sheetIndex]['header']['rows'][$i][$k]['temp'][$kk]['str2array'][$ee];
								}
								switch($header['formatoptions']['subformat']){
									case 'temp':
										$temp = $header['formatoptions']['temp'];
										$last_v[] = str_replace('<BR />', "\n", $this->tool->vsprintf($temp, $v_item));
										break;
								}
							}
							$v = implode("\n\n", $last_v);
							break;
					}
				}
			}
			else // 在formatter是multi_row_edit的情况下，v 可能是空数组
				$v = '';
			$content[$key] = $v;
		}
		return $content;
	}

	protected function RC2LN($row, $col){ //(col, row)=>'A'n
		$map = array(0=>'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 
			'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ',
			'BA', 'BB', 'BC', 'BD', 'BE', 'BF', 'BG', 'BH', 'BI', 'BJ', 'BK', 'BL', 'BM', 'BN', 'BO', 'BP', 'BQ', 'BR', 'BS', 'BT', 'BU', 'BV', 'BW', 'BX', 'BY', 'BZ',			
			'CA', 'CB', 'CC', 'CD', 'CE', 'CF', 'CG', 'CH', 'CI', 'CJ', 'CK', 'CL', 'CM', 'CN', 'CO', 'BP', 'BQ', 'BR', 'BS', 'BT', 'BU', 'BV', 'BW', 'BX', 'BY', 'BZ',			
			'DA', 'DB', 'DC', 'DD', 'DE', 'DF', 'DG', 'DH', 'DI', 'DJ', 'DK', 'DL', 'DM', 'DN', 'DO', 'DP', 'DQ', 'DR', 'DS', 'DT', 'DU', 'DV', 'DW', 'DX', 'DY', 'DZ',			
			'EA', 'EB', 'EC', 'ED', 'EE', 'EF', 'EG', 'EH', 'EI', 'EJ', 'EK', 'EL', 'EM', 'EN', 'EO', 'EP', 'EQ', 'ER', 'ES', 'ET', 'EU', 'EV', 'EW', 'EX', 'EY', 'EZ',			
			'FA', 'FB', 'FC', 'FD', 'FE', 'FF', 'FG', 'FH', 'FI', 'FJ', 'FK', 'FL', 'FM', 'FN', 'FO', 'FP', 'FQ', 'FR', 'FS', 'FT', 'FU', 'FV', 'FW', 'FX', 'FY', 'FZ',			
		);
		if($col < 182)
			$letter = $map[$col];
		else{
			$ret = array();
			$loop = (int)($col / 26);
			$rest = $col % 26;
			if ($loop > 0){
				$ret[] = chr(ord('A') + $loop - 1);
			}
			$ret[] = chr(ord('A') + $rest);
			$letter = implode('', $ret);
		}
		return $letter.$row;
	}

	protected function mergeCells($worksheetIndex, $startRow, $startCol, $endRow, $endCol){
		$beginLetter = $this->RC2LN($startRow, $startCol);
		$endLetter = $this->RC2LN($endRow, $endCol);
        $this->objExcel->setActiveSheetIndex($worksheetIndex);
		$sheet = $this->objExcel->getActiveSheet();
		$sheet->mergeCells($beginLetter.':'.$endLetter);
	}
	
	protected function headerIndex($sheetIndex, $headerIndex){
		static $fieldIndex = array();
		if (empty($fieldIndex[$sheetIndex][$headerIndex])){
			$fieldIndex[$sheetIndex][$headerIndex] = -1;
			$count = count($this->params['sheets'][$sheetIndex]['header']['rows']);
			foreach($this->params['sheets'][$sheetIndex]['header']['rows'][$count - 1] as $k=>$ch){
				if($ch['index'] == $headerIndex){
					$fieldIndex[$sheetIndex][$headerIndex] = $k + $this->params['sheets'][$sheetIndex]['startCol'];
					break;
				}
			}
		}
		return $fieldIndex[$sheetIndex][$headerIndex];
	}
	
	protected function sumCells($worksheetIndex, $headerIndex, $startRow, $endRow){
		$sum = '';
//		$column = $this->params['sheets'][$worksheetIndex]['startCol'];
		$index = $this->headerIndex($worksheetIndex, $headerIndex);
		if ($index !== -1){
//			$column += $index;
			$s = $this->RC2LN($startRow, $index);
			$e = $this->RC2LN($endRow, $index);
			$sum = "SUM($s:$e)";
		}
		return $sum;
	}
	
	protected function getCalculatedValue($sheetIndex, $headerIndex, $row){
    	$this->objExcel->setActiveSheetIndex($sheetIndex);
        $sheet = $this->objExcel->getActiveSheet();
		$v = '';
		$index = $this->headerIndex($sheetIndex, $headerIndex);
		if ($index != -1){
			$s = $this->RC2LN($row, $index);
			$cell = $sheet->getCell($s);
//print_r($cell);			
			$v = $cell->getValue();
			if (!empty($v) && $v[0] == '='){
				$v = $cell->getCalculatedValue();
			}
		}
		return $v;
	}
	
	protected function div($sheetIndex, $row, $field1, $field2){
		$index1 = $this->headerIndex($sheetIndex, $field1);
		$index2 = $this->headerIndex($sheetIndex, $field2);
		$fenzi = $this->RC2LN($row, $index1);
		$fenmu = $this->RC2LN($row, $index2);
// print_r("field1 = $field1, field2 = $field2, index1 = $index1, index2 = $index2, row = $row, fenzi = $fenzi, fenmu = $fenmu\n");
/*		
    	$this->objExcel->setActiveSheetIndex($sheetIndex);
        $sheet = $this->objExcel->getActiveSheet();
		$cell = $sheet->getCell($fenmu);
		$v = $cell->getCalculatedValue();
		if (!empty($v))
*/		
		return "=$fenzi/$fenmu";
	}
};

?>
