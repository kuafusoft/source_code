<?php
require_once('base_report.php');

defined('CURRENT_SHEET')
    || define('CURRENT_SHEET', 1);
defined('HISTORY_SHEET')
    || define('HISTORY_SHEET', 0);

class prjs_export extends base_report{
    private $prjInfo = array();
    private $prjIds = array();
    private $data = array();
	public function __construct($sheetTitles, $params = array()){
		parent::__construct($sheetTitles, $params);
		$this->prjIds = implode(',', $this->params['element']);
		$result = $this->params['db']->query("SELECT * FROM prj WHERE id in ({$this->prjIds})");
		$this->prjInfo = $result->fetchAll();
		$noStyle = $this->styles['normal'];
		$noStyle['font']['color'] = array('argb' => 'FFFF0000');
		$yesStyle = $this->styles['normal'];
		$yesStyle['font']['color'] = array('argb' => 'FF0000FF');
		$blankStyle = $this->styles['normal'];
		$blankStyle['fill']['color'] = array('argb' => 'FFFFFFFF');
		$categoryStyle = $this->styles['normal'];
		$categoryStyle['font']['bold'] = true;
		$addStyle = $this->styles['normal'];
		$addStyle['fill']['color'] = array('argb' => 'FF0000FF');
		$addStyle['font']['bold'] = true;
		$removeStyle = $this->styles['normal'];
		$removeStyle['font']['bold'] = true;
		$removeStyle['fill']['color'] = array('argb' => 'FFFF0000');

//		$categoryStyle['font']['size'] = 20;
		$categoryStyle['fill']['color'] = array('argb' => 'FFDDDDDD');
        $this->styles['no'] = $noStyle;
        $this->styles['yes'] = $yesStyle;
        $this->styles['blank'] = $blankStyle;
        $this->styles['category'] = $categoryStyle;
        $this->styles['add'] = $addStyle;
        $this->styles['remove'] = $removeStyle;
	}
	
	protected function _writeHeader($worksheetIndex, $setWidth = true, $mergeColumns = array()){
        $header = array();
		if ($worksheetIndex == HISTORY_SHEET){
		    $header[] = array('label'=>'Updated', 'width'=>200, 'index'=>'updated');
		    $header[] = array('label'=>'Author', 'width'=>200, 'index'=>'creater');
		}
		else
		    $header[] = array('label'=>'Component', 'width'=>160, 'index'=>'category');
		$header[] = array('label'=>'Identifier', 'width'=>200, 'index'=>'code');
		$header[] = array('label'=>'Requirements Text / Data', 'width'=>1000, 'index'=>'content');
		foreach($this->prjInfo as $prjInfo){
            $header[] = array('label'=>$prjInfo['name'], 'width'=>100, 'index'=>'prj_'.$prjInfo['id']);
        }
		parent::setColumnHeader($header, $worksheetIndex);
		parent::_writeHeader($worksheetIndex);
	}
	
	protected function getData($sheetIndex){
	    if (empty($this->data)){
            $userAdmin = new Application_Model_Useradmin($this);
            $userTable = $userAdmin->getUserTable();
    	    $blankRow = array('category'=>'', 'code'=>'', 'content'=>'');
    	    $categoryRow = $blankRow;
    	    $sql = "SELECT srs_category_id, category_content, category, code, content, updated, group_concat(concat(link_status_id, ':', prj_id, ':', link_status)) as prjs, concat(user.nickname, '[', user.username, ']') as creater ".
                " FROM vw_srs_node_history left join $userTable user ON vw_srs_node_history.creater_id=user.id ".
                " where prj_id in ({$this->prjIds}) group by srs_node_info_id ORDER BY category ASC, code ASC, updated DESC";
    		$result = $this->params['db']->query($sql);
            $using = array();
            $history = array();
            $lastCategory = 0;
            while($row = $result->fetch()){
                $using = array();
                $history = $row;
                $prjs = array();
                $prjs2 = array();
                $tmp = explode(',', $row['prjs']);
                foreach($tmp as $prj){
                    $detail = explode(':', $prj);
                    $prjs[$detail[0]][] = $detail[1];
                    $prjs2[$detail[1]] = $detail[2];
                }       
//print_r($prjs);
//print_r($prjs2);                         
                if (isset($prjs[1])){
                    $using = $row;
                    foreach($this->params['element'] as $prj){
                        if (in_array($prj, $prjs[1])){ // using
                            $using['prj_'.$prj] = 'yes';
                        }
                        else
                            $using['prj_'.$prj] = 'no';
                    }
                }
                foreach($this->params['element'] as $prj){
                    if (isset($prjs2[$prj]))
                        $history['prj_'.$prj] = $prjs2[$prj];
                    else
                        $history['prj_'.$prj] = '';
                }
//print_r($using);
//print_r($history);                
                $categoryRow['content'] = $row['category_content'];
                if (empty($lastCategory)){
                    $this->data[CURRENT_SHEET][] = $categoryRow;
                }
                else if ($lastCategory != $row['srs_category_id']){ // add a blank row
                    $this->data[CURRENT_SHEET][] = $blankRow;
                    $this->data[CURRENT_SHEET][] = $categoryRow;
                }
                $lastCategory = $row['srs_category_id'];
                if (!empty($using))
                    $this->data[CURRENT_SHEET][] = $using;
                $this->data[HISTORY_SHEET][] = $history;
            }
        }
		return $this->data[$sheetIndex];
	}

    protected function calcStyle(&$content, $sheetIndex, $defaultStyle = array()){
		$style = array();
		switch($sheetIndex){
    		case CURRENT_SHEET:
                foreach($this->columnHeaders[$sheetIndex] as $columnHeader){
                    if (!isset($columnHeader['index']))
                        continue;
                    $key = $columnHeader['index'];
                    if (isset($content[$key]) && $content[$key] == 'no')
                        $style[$key] = 'no';
                    else if (isset($content[$key]) && $content[$key] == 'yes')
                        $style[$key] = 'yes';
                    else if (empty($content['content']))
                        $style[$key] = 'blank';
                    else if (empty($content['code']))
                        $style[$key] = 'category';
                    else
            			$style[$key] = isset($defaultStyle[$key]) ? $defaultStyle[$key] : 
            				(isset($columnHeader['style']) ? $columnHeader['style'] : 'normal');
        		}
        		break;
        	case HISTORY_SHEET:
                foreach($this->columnHeaders[$sheetIndex] as $columnHeader){
                    if (!isset($columnHeader['index']))
                        continue;
                    $key = $columnHeader['index'];
                    if (isset($content[$key]) && $content[$key] == 'Add')
                        $style[$key] = 'add';
                    else if (isset($content[$key]) && $content[$key] == 'Remove')
                        $style[$key] = 'remove';
                    else
            			$style[$key] = isset($defaultStyle[$key]) ? $defaultStyle[$key] : 
            				(isset($columnHeader['style']) ? $columnHeader['style'] : 'normal');
        		}
        	    break;
        }
		return $style;
	}
	
}

?>
