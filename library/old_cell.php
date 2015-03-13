<?php
require_once("datasource.php");
require_once("setting.php");
require_once("common.php");

/**
 * Cell Property:
 * 1. name
 * 2. id
 * 3. type: label, text, radio, checkbox, select, textarea, data, logfile,  
 * 4. value 
 * 5. viewStatus
 * 6. rowInTable
 * 7. columnInTable
 * 8. table   
 * 9. options
 * 
 * the options can have the following keys:
 * . refData
 * . required
 * . disabled
 * . readonly  
 * . raw: raw text, do not use the htmlentities to translate it 
 * . insert: Just for select, the value can be "blank"/"all"/"blank_all"/"all_blank"/""; 
 * . align 
 * . unique
 *      .uniqueType: "sql"/"array"
 *      .uniqueDataSource:
 *      .uniqueField:
 *      .uniqueRecordID:    
 * . width
 * . rows: just for textarea now.
 * . css: CSS class
 * . columns: How many columns the cell will divided into
 * . event: event array 
 * . affectEvents: what events will be called when the cell changes
 * . link:
 * . linktype: function or ''
 * . linkfield: database fields as parameters
 * . linktarget:
 * . linktitle:     
 *
 */      
class CCell{
    var $name;
    var $type;
    var $value;
    var $viewStatus;
    var $options;

    var $id;
    var $rowInTable;
    var $columnInTable;
    var $table;
    
    function CCell($name, $type = "none", $value = 0, $viewStatus = "edit", $options = null){
        $this->name = $name;
        $this->type = $type;
        $this->value = $value;
        $this->viewStatus = $viewStatus;
        $this->options = $options;
        
        $this->table = null;
        $this->rowInTable = 0;
        $this->columnInTable = 0;

        $replaced = array("[", "]");
        $this->id = str_replace($replaced, "", $name);
    }
    
    function AttachToTable(&$table, $row, $column){
        $this->table = $table;
        $this->rowInTable = $row;
        $this->columnInTable = $column;
        if (empty($this->options['defineid']) || $this->options['defineid'] != 'no_row')
            $this->id .= '_'.$this->rowInTable;
    }
    
    function SetViewStatus($viewStatus){
        $this->viewStatus = $viewStatus;
    }
    
    function SetOption($name, $value){
        if (!isset($this->options))
            $this->options = array();
        $this->options[$name] = $value;
    }
    
    function translate($value, $type){
        if (!isset($value) || (empty($value) && $value != 0))
            return $value;
        $ret = $value;
        $strRet = '';
        $values = array_unique(explode(",", $value));
        $db = new Dbop();
        if (isset($this->options['refData'])){
            switch($type){
                case 'label':
                    if(is_array($this->options['refData'])){
                        if($this->type == 'checkbox'){
                            foreach($values as $eachValue){
                                if (empty($eachValue))
                                    continue;
                                if ($strRet != '')
                                    $strRet .= ', ';
                                $strRet .= isset($this->options['refData'][$eachValue][$eachValue])?$this->options['refData'][$eachValue][$eachValue] : $eachValue;
                            }
                            $ret = $strRet;
                        }
                        else
                            $ret = isset($this->options['refData'][$values[0]][$values[0]])?$this->options['refData'][$values[0]][$values[0]] : $value;           
                    }
                    else if (is_string($this->options['refData'])){
                        $idField = null;
                        if (!empty($this->options['refidfield']))
                            $idField = $this->options['refidfield'];
                        if($this->type == 'checkbox'){
                            foreach($values as $eachValue){
                                if (!empty($eachValue)){
                                    if ($strRet != '')
                                        $strRet .= ', ';
                                    $strRet .= $db->SqlMapping($eachValue, $this->options['refData'], $idField);
                                }
                            }
                            $ret = $strRet;
                        }
                        else{
//echo ("sql = ".$this->options['refData'].", value=".$value.", type=".$this->type);
                            $ret = $db->SqlMapping($values[0], $this->options['refData'], $idField);
                        }
                    }
                    break;
                    
                case 'checkbox':
                    break;
                    
                default:
                    break;
            }
        }
        // handle http or mailto
        $ret = InsertLink($ret, "http://");
        $ret = InsertLink($ret, "mailto:");
        $ret = InsertLink($ret, "file://");
        // handle keywords
        if (isset($this->table->options['keywords']) && $this->table->options['keywords'] != ''){
            analyzeKeyword($this->table->options['keywords'], $keywords);
            $ret = hilitWords($ret, $keywords);
        }
        return $ret;
    }
    
    function _displayOneLabel($value, $rowData = null){
        $str = '';
        $value = $this->translate($value, 'label');
        $align = isset($this->options['align'])?$this->options['align']:'left';
        $str .= "<div id='".$this->id."' align='".$align."'";
        if (isset($this->options['event']))
            $str .= CreateEventStr($this->options['event']);
        if (isset($this->options['affectevents']))
            $str .= CreateEventStr($this->options['affectevents']);
        $str .= ">";

		if (isset($this->options["link"]) && $this->options["link"] != ""){
		    if (isset($this->options["linktype"]) && $this->options["linktype"] == 'function'){
                $preLink = "<a href=\"javascript:".htmlEnt($this->options["link"], ENT_QUOTES);
                if (isset($this->options['linkfields']) && $this->options['linkfields'] != ''){
                    $fields = explode(",", $this->options['linkfields']);
                    $tmp = '';
                    foreach($fields as $field){
                        if ($field == '__ROW__')
                            $linkvalue = $this->rowInTable;
                        else
                            $linkvalue = isset($rowData[$field])?$rowData[$field]:'';
                        if ($tmp != '')
                            $tmp .=',';
                        $tmp .= "'".htmlEnt($linkvalue, ENT_QUOTES)."'";
                    }
                    $preLink .= $tmp.")";
                }
            }
            else{
                $preLink = "<a ";
    			if (isset($this->options['linktarget']))
                    $preLink .= " target='".$this->options['linktarget']."' ";
    			if (isset($this->options['linktitle']))
    			    $preLink .= " title='".$this->options['linktitle']."'";
                $preLink .= " href=\"".htmlEnt($this->options["link"], ENT_QUOTES);

//			    $str .= "<a href=\"".htmlEnt($this->options["link"], ENT_QUOTES);
			    
                if (isset($this->options['linkfields']) && $this->options['linkfields'] != ''){
                    $fields = explode(",", $this->options['linkfields']);
                    $params = '';
                    foreach($fields as $field){
                        $linkvalue = isset($rowData[$field])?$rowData[$field]:'';
                        if ($params != '')
                            $params .= '&';
                        $params .= $field.'='.htmlEnt($linkvalue, ENT_QUOTES);
                    }
                    if (stripos($this->options["link"], "?") === false)
                        $preLink .= "?";
                    else if (stripos($this->options["link"], "&") === false && $params != '')
                        $preLink .= "&";
                    $preLink .= $params;
                }
//                $str .= "\"";
			}
//            $str .= " >";
		}
		
        $vs = array($value);
        if(!empty($this->options['linkseparate'])){
            $vs = explode($this->options['linkseparate'], $value);
        }
//print_r($vs);        
        $aTemp = array();
        foreach($vs as $v){
            if ($v == '')
                continue;
            $v = str_replace("\r\n", "<BR />", $v);
            $v = str_replace("\n", "<BR />", $v);
            $v = str_replace("\r", "<BR />", $v);
            if (isset($this->options['raw']) && $this->options['raw'] == false)
                $v = htmlEnt($value, ENT_QUOTES);
            if (!empty($this->options['linkseparate'])){
                $tmp = $preLink;
                if (stripos($this->options["link"], "?") === false)
                    $tmp .= "?";
                else if (stripos($this->options["link"], "&") === false)
                    $tmp .= "&";
                if (!empty($this->options['linklabel'])){
                    $tmp .= $this->options['linklabel']."=".$v;
                }
                else
                    $tmp .= "value=".$v;
                $tmp .= "\">".$v."</a>";
                $aTemp[] = $tmp;
            }
            else if (!empty($this->options['link'])){
                $aTemp[] = $preLink."\">".$v."</a>";
            }
            else{
                $aTemp[] = $v;
            }
        }
        
        if (!empty($this->options['linkseparate'])){
            $str .= implode($this->options['linkseparate'], $aTemp);
        }
        else{
            $str .= implode('', $aTemp);
        }
        $str .= "</div>";    
//print_r($str);
        return $str;
    }
    
    function displayLabel($rowData = null){
        $str = '';
        if (isset($this->options['columns']) && $this->options['columns'] > 0 && $this->type == 'checkbox'){
            $values = $this->value;
            if (is_string($values) && isset($values{0}) && $values{0} == ',')
                $values = substr($values, 1, -1);
            $values = explode(",", $values);
            $values = array_unique($values);
            if (count($values) < 2)
                $str .= $this->_displayOneLabel($values[0], $rowData);
            else{
                $str .= "<table width='100%'>";
                $changeRow = true;
                $i = 0;
                foreach($values as $value){
                    if ($changeRow)
                        $str .= "<tr>";
                    
                    $str .= "<td>";
                    $str .= $this->_displayOneLabel($value, $rowData);
                    $str .= "</td>";
                    $i ++;
                    if ($i == $this->options['columns']){
                        $changeRow = true;
                        $i = 0;
                    }
                    else
                        $changeRow = false;
                        
                    if ($changeRow)
                        $str .= "</tr>";
                }
                $str .= "</table>";
            }
        }
        else{
            $str .= $this->_displayOneLabel($this->value, $rowData);
        }
        return $str;
    }
    
    function displaySelection($type, $rowData = null){
        $str = '';
        $neverLabel = isset($this->options['neverlabel']) ? $this->options['neverlabel'] : false;
        if ($this->table->viewStatus == 'view' && !$neverLabel)
            $str .= $this->displayLabel($rowData);
        else{
            $columns = 0;
            
            if (isset($this->options['columns']) && $this->options['columns'] > 0)
                $columns = $this->options['columns'];
            $disabled = false;
            if (isset($this->options['disablecheckfield']) && isset($rowData[$this->options['disablecheckfield']]) 
                    && $rowData[$this->options['disablecheckfield']] != $this->options['disablecheckvalue']){
                $disabled = true;
            }
            else if (isset($this->options['disabled']))
                $disabled = $this->options['disabled'];
            $readonly = isset($this->options['readonly']) ? $this->options['readonly'] : false;
            $width = isset($this->options['width']) ? $this->options['width'] : '100%';
            $event = isset($this->options['event']) ? $this->options['event'] : null;
            $affectEvents = isset($this->options['affectevents']) ? $this->options['affectevents'] : null;
            if (isset($affectEvents)){
                if (isset($event)){
                    $onchange = isset($event['onchange']) ? $event['onchange'] : null;
                    $event['onchange'] = array();
                    if (isset($onchange))
                        $event['onchange'][] = $onchange;
                    foreach($affectEvents as $affectEvent)
                        $event['onchange'][] = $affectEvent; 
                }
                else{
                    $event['onchange'] = array();
                    foreach($affectEvents as $affectEvent)
                        $event['onchange'][] = $affectEvent; 
                }
            }
            if (isset($this->options['raw']) && ($this->options['raw'] == 'false' || $this->options['raw'] == false))
                $value = htmlEnt($this->value, ENT_QUOTES);
            else
                $value = $this->value;
            
            $refData = isset($this->options['refData']) ? $this->options['refData'] : null;
            if ($type == 'checkbox')
                $str .= CreateCheckBoxesStr($this->name, $this->id, $refData, $value, $disabled, $readonly, $columns, $event);
            else if ($type == 'select'){
                $insert = isset($this->options['insert']) ? $this->options['insert'] : null;
                $str .= CreateSelectStr($this->name, $this->id, $refData, $value, $width, $insert, $disabled, $readonly, $event);
            }
            else if ($type == 'radio'){
                $str .= CreateRadioOptionsStr($this->name, $this->id, $refData, $value, $disabled, $readonly, $columns, $event);
            }
        }
        return $str;
    }

    function displayOneText($checkUnique = null, $password = false, $rowData = null){
        $str = '';
        $value = $this->value;
        if (isset($this->options['raw']) && ($this->options['raw'] == 'false' || $this->options['raw'] == false))
            $value = htmlEnt($value, ENT_QUOTES);
        $disabled = isset($this->options['disabled']) ? $this->options['disabled'] : false;
        $readonly = isset($this->options['readonly']) ? $this->options['readonly'] : false;
        $str .= "<input type='".($password?"password":"text")."'";
        if (isset($checkUnique) && $checkUnique != '')
            $str .= " onblur='".$checkUnique."'";
        $str .=  " name='".$this->name."' id='".$this->id."' value='".htmlEnt($value, ENT_QUOTES)."' ";
        if ($disabled)
            $str .= " disabled='disabled' ";
        if ($readonly)
            $str .= " readonly='readonly' ";
        if (!empty($this->options['invalidchars']))
            $str .= " invalidchars='".$this->options['invalidchars']."'";
        if (!empty($this->options['validchars']))
            $str .= " validchars='".$this->options['validchars']."'";
        if (!empty($this->options['min']))
            $str .= " min='".$this->options['min']."'";
        if (!empty($this->options['max']))
            $str .= " max='".$this->options['max']."'";
        if (isset($this->options['event']))
            $str .= CreateEventStr($this->options['event']);
        if (isset($this->options['affectevents']))
            $str .= CreateEventStr($this->options['affectevents']);
        $str .= " style='width:100%' />";
        return $str;
    }
    
    function displayText($password = false, $rowData = null){
        $str = '';
        if ($this->table->viewStatus == 'view'){
            if ($password)
                $str .= $this->_displayOneLabel("*********************", $rowData);
            else
                $str .= $this->displayLabel($rowData);
        }
        else{
            if (isset($this->options['unique']) && $this->options['unique'] == true){
                $uniqueType = 'sql';
                if (isset($this->options['uniqueType']))
                    $uniqueType = $this->options['uniqueType'];
                $uniqueDatasource = isset($this->options['uniqueDataSource']) ? $this->options['uniqueDataSource'] : "";
                $uniqueField = isset($this->options['uniqueField']) ? $this->options['uniqueField'] : "";
                $uniqueRecordID = isset($this->options['uniqueRecordID']) ? $this->options['uniqueRecordID'] : "0"; 
                $table = $this->table;
                $checkUnique = 'table_CheckUnique("'.$table->phpfile.'", "'.$table->name.'", "'.$this->id.'", "'
                    .$uniqueType.'", "'.$uniqueDatasource.'", "'.$uniqueField.'", "'.$uniqueRecordID.'")';
                $str .= "<table width='100%'><tr>
                    <td width='100%'>";
                $str .= $this->displayOneText($checkUnique, $password, $rowData);
                $str .= "</td>
                    <td width='18'>
                        <img width='18' id='".$this->id."_img' src='../pic/aHelp.png'></img>
                    </td>
                    </tr>
                    </table>";        
            }
            else{
                $str .= $this->displayOneText(null, $password, $rowData);
            }
        }
        return $str;
    }
 
    function displayTextArea($rowData = null){
        $str = '';
        if ($this->table->viewStatus == 'view'){
            $str .= $this->displayLabel();
        }
        else{
            $rows = isset($this->options['rows']) ? $this->options['rows'] : 3;
            $disabled = isset($this->options['disabled']) ? $this->options['disabled'] : false;
            $readonly = isset($this->options['readonly']) ? $this->options['readonly'] : false;
            
			if (isset($this->options["width"])){
				$strcols = " cols='".$this->options["width"]."' ";
			}	
			else{
				$strcols = " style='width:100%' ";
			}
			$value = $this->value;
            if (isset($this->options['raw']) && ($this->options['raw'] == 'false' || $this->options['raw'] == false))
                $value = htmlEnt($value, ENT_QUOTES);
            
            $str .= "<textarea ";
            if ($disabled)
                $str .= " disabled='disabled' ";
            if ($readonly)
                $str .= " readonly='readonly' ";                         
            $str .= " name='".$this->name."' id='".$this->id."' rows='".$rows."' ".$strcols.">".$value."</textarea>";
        }
        return $str;
    }
 
    function displayDate($rowData = null){
        $str = '';
        if ($this->table->viewStatus == 'view'){
            $str .= $this->displayLabel();
        }
        else{
            $disabled = isset($this->options['disabled']) ? $this->options['disabled'] : false;
            $readonly = isset($this->options['readonly']) ? $this->options['readonly'] : false;
    		$str .= "<input name='".$this->name."' type='text' id='".$this->id."' ";
            if ($disabled)
                $str .= " disabled='disabled' ";
            $str .= " readonly='readonly' ";                         
            if (isset($this->options['event']))
                $str .= CreateEventStr($this->options['event']);
    		$str .= " value='".htmlEnt(isset($this->value)?$this->value:"",ENT_QUOTES)."'/>";
    		$str .= "<a href='javascript:popupCalendar(\"".$this->name."\")'><img src='../pic/cal.gif' width='16' height='16' border='0' alt='Click Here to Pick up date'></a>";
		}
		return $str;
    }
    
    function displayLogFileWithName($rowData = null){
        $fileName = GetLogRoot();
        if (isset($this->options['logfilename']))
            $fileName .= $this->options['logfilename'];
        if (!empty($this->options['logfilenamefield'])){
            if (isset($rowData[$this->options['logfilenamefield']]))
                $fileName .= $rowData[$this->options['logfilenamefield']];
        }
        else{
            if (isset($rowData[$this->name]))
                $fileName .= $rowData[$this->name];
        }
        if (isset($this->options['logfile_ext']))
            $fileName .= $this->options['logfile_ext'];
        $str = '';
		$str .= "<div id='".$this->name."_log2_Div'>";
		if (file_exists($fileName))
    	    $str .= CreateLogFileStrWithName($this->table, $this->name, $fileName);
		$str .=  "</div>";
        return $str;           
    }

    function displayLogFile($rowData = null){
        $directory = $this->options['directory'];
        if (!empty($this->options['directoryfield'])){
            $fields = explode(',', $this->options['directoryfield']);
            $tmpdir = '';
            foreach($fields as $field){
                if ($tmpdir != '')
                    $tmpdir .= '_';
                $tmpdir .= $rowData[$field];
            }
            $directory .= $tmpdir;//$rowData[$this->options['directoryfield']];
        }
        $strLogFilePath = GetLogRoot().$directory;
        $disabled = $this->table->viewStatus == 'view'?true:false;
        // display the existed log files
        $str = '';
		$str .= "<div id='".$this->name."_log_Div'>";
		if (file_exists($strLogFilePath))
    	    $str .= CreateLogFilesStr($this->table, $this->name, $directory, $disabled);
		$str .=  "</div>";
        
        // display the input elements
        if ($this->table->viewStatus != 'view'){
            $str .= "<form action='".GetWebRoot()."/inc2/log_process.php' id='".$this->name."_uploadLogForm' name='".$this->name."_uploadLogForm' 
                    encType='multipart/form-data' method='post' target='hidden_logframe'>";   
            $str .= "<input type='file' id='".$this->name."_logfile' name='".$this->name."_logfile' style='width:450' 
                        onChange='javascript:enableUploadButton(this, \"".$this->name."_upload\")' />";
            $str .= "<input type='submit' id='".$this->name."_upload' value='Upload File' disabled='disabled' />";
            $str .= "<br />";   
            $str .= "<iframe name='hidden_logframe' id='hidden_logframe' style='display:none'>";
            $str .= "</iframe>";   
            $str .= "<input type='hidden' name='purpose' value='upload' />";
            $str .= "<input type='hidden' name='cellName' value='".$this->name."' />";
            $str .= "<input type='hidden' name='tablename' value='".$this->table->name."' />";
            $str .= "<input type='hidden' name='directory' value='".$directory."' />";
            $str .= "<input type='hidden' name='response' value='".$this->table->phpfile."' />";
            $str .= "<input type='hidden' name='recordid' value='".(isset($rowData['recordid']) ? $rowData['recordid'] : 0)."' />";
            if (isset($this->table->options['uploadfunc']))
                $str .= "<input type='hidden' name='uploadfunc' value='".$this->table->options['uploadfunc']."' />";
            $str .= "</form>";
        }
        return $str;           
    }
    
    function Display($rowData = null, $displayDetail = false, $detailFunction = ''){
        $str = '';
        if (!isset($this->value) || trim($this->value) == ''){
            switch($this->type){
                case 'label':
                case 'none':
                case 'text':
                case 'textarea':
                    $this->value = '';
                    break;
                    
/*
                case 'select':
                case 'radio':
                case 'checkbox':
                    $this->value = NULL;
                    break;
*/                    
                default:
//                    $this->value = '&nbsp';
                    break;
            }
        }
/*  
        $str .= "<td id='".$this->id."_td'";
//        echo "<td name='".$this->name."' id='".$this->id."_td'";
        $width = $this->table->GetColumnWidth($this->columnInTable, $this->name);
        if (isset($width) && $width != 0 && $width != '')
            $str .= " width='".$width."'";
        if (isset($this->options['aligh']))
            $str .= " align='".$this->options['aligh']."'";
        if (isset($this->options['td_event']))
            $str .= CreateEventStr($this->options['td_event']);
        $str .= ">";
*/        
        $showDetail = false;
        if ($this->columnInTable == 0 && $displayDetail && $detailFunction != '')
            $showDetail = true;
        if ($showDetail)
            $str .= "<table width='100%'><tr width='100%'><td>";
        switch($this->type){
            case 'label':
            case 'none':
            default:
                $str .= $this->displayLabel($rowData);
                break;
                
            case 'rawlabel':
                $str .= $this->displayLabel(true, $rowData);
                break;
                
            case 'date':
                $str .= $this->displayDate($rowData);
                break;
                
            case 'password':
                $str .= $this->displayText(true, $rowData);
                break;
          
            case 'checkbox':
            case 'select':
            case 'radio':
                $str .= $this->displaySelection($this->type, $rowData);
                break;
            
            case 'text':
                $str .= $this->displayText(false, $rowData);
                break;
          
            case 'textarea':
                $str .= $this->displayTextArea($rowData);
                break;
                
            case 'logfile':
                $str .= $this->displayLogFile($rowData);
                break;
                
            case 'logfilewithname':
                $str .= $this->displayLogFileWithName($rowData);
                break;
                
            case 'select_link':
                if (empty($this->value))
                    $str .= $this->displaySelection('select', $rowData);
                else
                    $str .= $this->displayLabel($rowData);
                break;
        }
        if ($showDetail){
            $prefixID = $this->table->name."_".$this->rowInTable."_detail";
            $imgID = $prefixID."_img";
            $recordid = isset($rowData['recordid']) ? $rowData['recordid'] : 0;
            $str .= "</td><td width='14px'>";
            $str .= "<a href=\"javascript:$detailFunction($recordid,'".$prefixID."')\" title='Display/Hide Detail Information'>";
//            $str .= "<a href=\"javascript:table_displayDetail('".$this->table->phpfile."', '".$this->table->name."', ".$this->rowInTable.", ".$rowData['recordid'].")\" 
//                title='Display/Hide Detail Information'>";
            $str .= "<img width='10' id='".$imgID."' src='../pic/plus2.gif' alt='Display/Hide Detail Information'></img>";
            $str .= '</a>';
            $str .= "</td></tr></table>";
        }
//        $str .= "</td>";
        return $str;
    }
    
    function GetOption($name){
        if (!isset($this->options))
            return null;
        return $this->options[$name];
    }
};

?>
