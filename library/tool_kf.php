<?php
require_once('kf_form.php');

function _P($s){
	print_r("\n<br>");
	print_r($s);
	print_r("<br>\n");
}

class tool_kf{
	public function p_t($tip){
		static $lastMicroSec = 0;
		$currentMicroSec = microtime(true);
		$str = ">>>>>".$tip.":".$currentMicroSec;
		if($lastMicroSec == 0)
			$lastMicroSec = $currentMicroSec;
		else{
			$str .= ", gap from the last is ".($currentMicroSec - $lastMicroSec);
			$lastMicroSec = $currentMicroSec;
		}
		$str .= "<<<<<<<\n<BR>";
		print_r($str);
	}
	
	public function vsprintf($str, $v){
// print_r($str);
// print_r($v);
		//%(module_id)s has %(question_type_id)s question
		$patterns = array();
		$replace = array();
		preg_match_all('/(\%\((.*?)\).)/', $str, $matches, PREG_SET_ORDER);
		if(count($matches) > 0){
			foreach($matches as $val){
				$val[0] = preg_replace(array('/\(/', '/\)/'), array('\(', '\)'), $val[0]);
				$patterns[] = '/'.$val[0].'/';
				$replace[] = isset($v[$val[2]]) ? $v[$val[2]] : '';
			}
		}
		$str = preg_replace($patterns, $replace, $str);
		return $str;
	}
	
	public function insertLink($str){
		$pattern = array('/(https?:\/\/.*?)([ ,.]?\s)/', '/mailto:(.*?)([ ,.]?\s)/');
		$replace = array('<a href="${1}">${1}</a>${2}', 'mailto:<a href="mailto:${1}">${1}</a>${2}');
		return preg_replace($pattern, $replace, $str);
	}
	
	public function getWeekStartEndDay($gdate = "", $first = 0){
		if(!$gdate) $gdate = date("Y-m-d");
		$w = date("w", strtotime($gdate));
		$dn = $w ? $w - $first : 6;
		$st = date("Y-m-d", strtotime("$gdate -".$dn." days"));
		$en = date("Y-m-d", strtotime("$st +6 days"));
		return array($st, $en);
	}
	
    public function createDirectory($directory){
        if (!file_exists($directory)){
            if (strtoupper(substr(PHP_OS,0,3))=='WIN'){
            	mkdir($directory, 0700, true);
        	}
        	else
        		system('/bin/mkdir -p '.escapeshellarg($directory) . ' -m 777');
        }
    }
	
	public function uniformFileName($fileName){
		return str_replace(' ', '_', $fileName);
	}
    
    public function formatFileName($fileName, $suffix = ''){
        $ret = $this->uniformFileName($fileName); // replace with "_"
		$pathInfo = pathInfo($ret);
		$dir = $pathInfo['dirname'];
		$suffix = ".".$pathInfo['extension'];
		$baseName = $pathInfo['filename'];
		// if (empty($suffix) && preg_match('/(.*?)(\.+.*)$/', $ret, $matches) !== FALSE){
			// if (!empty($matches[2]))
				// $suffix = $matches[2];
		// }
		// $baseName = basename($ret, $suffix);
		$this->createDirectory($dir);
		$i = 1;
		while(file_exists($ret)){
			$ret = $dir.'/'.$baseName.'_'.$i.$suffix;
			$i ++;
		}
		return $ret;
    }

    public function moveFile($fileName, $dest){
    	$dest = $this->uniformFileName($dest);
        $path_parts = pathinfo($dest);
        $this->createDirectory($path_parts['dirname']);  
        if (file_exists($dest)){
            $base = $path_parts['dirname'].'/'.$path_parts['filename'];
            $i = 1;
            do{
                $dest = $base.'_'.$i ++;
            }while(file_exists($dest.'.'.$path_parts['extension']));
            $dest .= '.'.$path_parts['extension'];
        }
        move_uploaded_file($fileName, $dest);
//			copy($fileName, $dest);
        return $dest;
    }
    
	function saveFile($str, $fileName = '', $dir = ''){
		if (empty($dir))
			$dir = EXPORT_ROOT;
		if (empty($fileName))
			$fileName = "tmp.txt";
		$suffix = '.txt';
		if (preg_match('/^.*(\..*?)$/', $fileName, $matches))
			$suffix = $matches[1];
//print_r($suffix);			
		$fileName = $this->formatFileName($dir.'/'.$fileName, $suffix);
		$fp = fopen($fileName, 'wb');
		fwrite($fp, $str);
		fclose($fp);
		return $fileName;
	}
	
	function hilitWords($str, $words){
		$patterns = array();
		$replaces = array();
		foreach($words as $word){
	//        $word = str_replace("\"", "\\\"", $word);
			$patterns[] = "/(".$word.")/i";
			$replaces[] = '<span style="color:#FF0000;background-color:#CCCCCC">${1}</span>';
		}
		$str = preg_replace($patterns, $replaces, $str);
		return $str;
	}

	function replaceParam($str, $ds = null){
		$pattern = array();
		$replacements = array();
		if (preg_match_all("/<%(\w*?)%>/", $str, $matches) !== FALSE){
			foreach($matches[1] as $param){
				$pattern[] = "/<%".$param."%>/";
				$replacements[] = isset($ds[$param]) ? $ds[$param] : '';
			}
		}
		$ret = preg_replace($pattern, $replacements, $str);
		return $ret;
	}
	
	public function array_dup($a){
		$dup = array();
		$uni = array_unique($a);
		if (count($a) > count($uni)){
			$uni_key = array_keys($uni);
			$key = array_keys($a);
			$diff_key = array_diff($key, $uni_key);
//print_r($a);
//print_r($uni);		
//print_r($diff_key);
			foreach($diff_key as $key)
				$dup[] = $a[$key];
		}
		return $dup;
	}

    public function array_extends($output, $default){
        foreach($default as $key=>$v){
            if (isset($output[$key])){
                if (is_array($v)){
                    $output[$key] = $this->array_extends($output[$key], $v);
                }            
                else
                    $output[$key] = $v;
            }
            else{
                $output[$key] = $v;
            }
        }
        return $output;
    } 
	
	public function extractItems($keys, $vs){
		$ret = array();
		foreach($keys as $k=>$v){
			$hasDefaultValue = !is_int($k);
			if (is_int($k))$k = $v;
			if(isset($vs[$k]))
				$ret[$k] = $vs[$k];
			elseif ($hasDefaultValue){
				$ret[$k] = $v;
			}
		}
		return $ret;
	}
	
	public function getYearMonthList($off, $length, $blank_item = true){ //$off：和当前日期的变差月数，负数为以前月，正数为未来月
		$list = array(0=>'');
		$today = getdate();
		$base = $today['mon'] + $off - $length + 1;
		for($i = 0; $i < $length; $i ++){
			$ym = date('Y-m', mktime(0, 0, 0, $base + $i, 1, $today['year']));
			$list[$ym] = $ym;
		}
		return $list;
	}
	
	public function getWeekList($preWeek = 8, $postWeek = 10){
		$currentYear = (int)date('y');
		$currentWorkWeek = (int)date('W');
		$refData = array();
		for($i = $currentWorkWeek - $preWeek; $i < $currentWorkWeek + $postWeek; $i ++){
			$j = $i;
			$year = $currentYear;
			if ($i > 52){
				$j = $i - 52;
				$year = $currentYear + 1;
			}
			else if ($i <= 0){
				$j = $i + 52;
				$year = $currentYear - 1;
			}
			if ($j < 10)
				$week = $year.'WK0'.$j;
			else
				$week = $year.'WK'.$j;
			$refData[$week] = $week;
			// if ($j < 10)
				// $refData[$i] = $year.'WK0'.$j;
			// else
				// $refData[$i] = $year.'WK'.$j;
		}
		return $refData;
	}
	
	public function array2Str($arr, $sep = ':'){
		$str = array();
		$displayField = '';
		foreach($arr as $id=>$name){
			if(is_array($name)){
				if(empty($displayField)){
					$displayField = $this->getDisplayField($name);
				}
				$str[$name['id']] = $name['id'].$sep.$name[$displayField];
			}
			else
				$str[$id] = $id.$sep.$name;
		}
		return implode(';', $str);
	}
	
	public function str2Array($str){
		if(is_array($str))
			return $str;
		$ret = array();
		$a = explode(';', $str);
		foreach($a as $v){
			$b = explode(':', $v);
			$ret[$b[0]] = $b[1];
		}
// print_r($str);
// print_r($ret);
		return $ret;
	}
    
    public function getDisplayField($desc){
        $ret = 'id';
        $candidates = array('code', 'nickname', 'name', 'username', 'subject', 'title', 'content', 'ver');
        foreach($candidates as $candidate){
            if (isset($desc[$candidate])){
                $ret = $candidate;
                break;
            }
        }
        return $ret;
    }
	
	public function genSelect($data, $options = array('blank'=>false, 'blank_item'=>false)){
		$list = array();
		$list[] = "<select>";
		if ($options['blank'])
			$list[] = "<option id='option_0' value='0'></option>";
		if ($options['blank_item'])
			$list[] = "<option id='option_blank_item' value='-1'>===Blank===</option>";
		foreach($data as $k=>$v){
			$list[] = "<option id='option_{$k}' value='$k'>$v</option>";
		}
		$list[] = "</select>";
		return implode(',', $list);
	}
	
	public function RC2LN($row, $col){ //(col, row)=>'A'n
    	$ret = array();
		$loop = (int)($col / 26);
		$rest = $col % 26;
		if ($loop > 0){
			$ret[] = chr(ord('A') + $loop - 1);
		}
		$ret[] = chr(ord('A') + $rest);
//print_r("col=$col, loop = $loop, ret =");
//print_r($ret);
		return implode('', $ret).$row;
	}
	
	public function genPattern($id){
		return "^$id$|^$id,|,$id,|,$id$";
	}
	
    public function getExportDir(){
        return EXPORT_ROOT;        
    }
    
    public function getExportFileName($fileName, $ext = '', $format = true){
        $exportDir = $this->getExportDir();
        if ($format)
            $file = $this->formatFileName($exportDir .'/'.$fileName, $ext);
        else
            $file = $exportDir .'/'.$fileName;
//print_r($file);
        return $file;
    }

    // send an appointment
    /*
    params: 
        0. from
        1. subject
        2. description
        3. email
        4. start_time
        5. end_time
        6. location
    */
    function sendAppointment($params){
        $dtStart = date("Ymd\THis", strtotime($params['start_time']));
        $dtEnd = date("Ymd\THis", strtotime($params['end_time']));
        //--------------------
        //create text file
        $ourFileName = $this->formatFileName("icsFile/calendar.txt", "txt");
        $fh = fopen($ourFileName, 'w') or die("can't open file2");
        
        $stringData = "
            BEGIN:VCALENDAR\n 
            PRODID:-//Microsoft Corporation//Outlook 11.0 MIMEDIR//EN\n 
            VERSION:2.0\n 
            METHOD:REQUEST\n 
            BEGIN:VEVENT\n 
            ORGANIZER:MAILTO:organizer@domain.com\n 
            DTSTAMP:".date('Ymd').'T'.date('His')."\n
            DTSTART:$dtStart\n 
            DTEND:$dtEnd\n 
            TRANSP:OPAQUE\n 
            SEQUENCE:0\n 
            UID:".date('Ymd').'T'.date('His')."-".rand()."-domain.com\n 
            SUMMARY:{$params['subject']}\n 
            DESCRIPTION:{$params['description']}\n
            PRIORITY:5\n 
            X-MICROSOFT-CDO-IMPORTANCE:1\n 
            CLASS:PUBLIC\n 
            END:VEVENT\n 
            END:VCALENDAR";
        fwrite($fh, $stringData);
        fclose($fh);
        
        //email temp file
        $fileatt = "icsFile/calendar.txt"; // Path to the file
        $fileatt_type = "application/octet-stream"; // File Type
        $fileatt_name = "ical.ics"; // Filename that will be used for the file as the attachment
        
        $email_from = $params['from'];//"fromPerson@domain.com"; // Who the email is from
        $email_subject = $params['subject']; //"Email test"; // The Subject of the email
        $email_message = $params['description']; //"this is a sample message \n\n next line \n\n next line"; // Message that the email has in it
        
        $email_to = $params['email'];//"toPerson@domain.com"; // Who the email is too
        
        $headers = "From: ".$email_from;
        
        $file = fopen($fileatt,'rb');
        $data = fread($file,filesize($fileatt));
        fclose($file);
        
        $semi_rand = md5(time());
        $mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";
        
        $headers .= "\nMIME-Version: 1.0\n" .
        "Content-Type: multipart/mixed;\n" .
        " boundary=\"{$mime_boundary}\"";
        
        $email_message .= "This is a multi-part message in MIME format.\n\n" .
        "--{$mime_boundary}\n" .
        "Content-Type:text/html; charset=\"iso-8859-1\"\n" .
        "Content-Transfer-Encoding: 7bit\n\n" .
        $email_message . "\n\n";
        
        $data = chunk_split(base64_encode($data));
        
        $email_message .= "--{$mime_boundary}\n" .
        "Content-Type: {$fileatt_type};\n" .
        " name=\"{$fileatt_name}\"\n" .
        //"Content-Disposition: attachment;\n" .
        //" filename=\"{$fileatt_name}\"\n" .
        "Content-Transfer-Encoding: base64\n\n" .
        $data . "\n\n" .
        "--{$mime_boundary}--\n";
        
        $ok = @mail($email_to, $email_subject, $email_message, $headers);
        
        if($ok) {
        
        } else {
            die("Sorry but the email could not be sent. Please go back and try again!");
        } 
         
    }
	
	function propStr($e, $prop, $empty = true){
		$str = "";
		if (isset($e[$prop]) && (!is_array($e[$prop])) && ($empty || !empty($e[$prop]))){
			$str = " $prop='{$e[$prop]}";
			if ($prop == 'name' && ($e['type'] == 'checkbox' || $e['type'] == 'cart')){
				//如果name没有[]，则添加之
				if(stripos($e['name'], '[') === false){
					$str .= "[]";
				}
			}
			$str .= "'";
		}
		return $str;
	}
	// create element with 3 section:pre, e, post
	// e = {'id', 'name', 'label', 'type', 'class', 'value', 'disabled', 'editable', 'editoptions'=>{'value', 'multiple', 'size'}}
	// 对于不同的字段，应加入输入检测，如数字类只允许输入0-9，email则必须符合Email格式等
	function ce($e, $strParams, $oper){
		$strPre = $this->generatePre($e, $strParams, $oper);
		$strE = $this->generateInput($e, $strParams, $oper);
		return $strPre.$strE;
	}
	
	private function generatePre($e, $strParams, $oper){
		if (empty($e['label']))
			return '';
		$strPre = "<span id='{$e['id']}_label'>{$e['label']}</span>";
		if (/*!empty($e['editable']) && */!empty($e['unique'])){
			$img_display = "";
//			if (!empty($e['readonly'])){
//				$img_display = ' style="display:none"';
//			}
			$img_src = '/img/aHelp.png';
//			if ($oper != 'new')
//				$img_src = '/img/aCheck.png';
			$strPre .= "<img id='img_unique_check' width='18' height='18' $img_display src='$img_src'>";
		}
		if (!empty($e['required'])){
			$strPre .= "<span style='color:red'>*</span>";
		}
		$strPre = "<td class='e-pre' style='text-align:right'>$strPre:</td>";
		return $strPre;
	}
	
	private function generatePost($e, $strParams, $oper){
		if (!empty($e['post'])){
			$post = $e['post'];
			if(is_string($post))
				$post = array('type'=>'text', 'value'=>$post);
			$type = isset($post['type']) ? $post['type'] : 'text';
			if(!isset($post['value']))
				$post['value'] = '';
			if(!isset($post['title']))
				$post['title'] = '';
			if (empty($post['class']))
				$post['class'] = 'e-post';
			else
				$post['class'] .= ' e-post';
			switch($type){
				case 'button':
					$strPost = "<button type='button' class='{$post['class']}' value='{$post['value']}' id='{$post['id']}' title='{$post['title']}'>{$post['value']}</button>";
					break;
				case 'text':
					$strPost = "<span class='{$post['class']}' id='{$e['id']}_post' title='{$post['title']}'>{$post['value']}</span>";
					break;
			}
		}
		return $strPost;
	}
	
	private function generateInput($e, $strParams, $oper){
		$strE = '';
		if (!empty($e['post'])){
			$strE = "<td><table style='width:100%'><tr>";
		}
		$event = '';
		if (empty($e['class']))
			$e['class'] = 'ces';
		else
			$e['class'] .= ' ces';
		$value = $this->propStr($e, 'value');
		$original_value = $this->propStr($e, 'original_value');
		$unique = '';
		//if (!empty($e['editable']))
			$unique = $this->propStr($e, 'unique', false);
		$props = array('id', 'name', 'required', 'editable', 'readonly', 'class', 'rows', 'ignored');
		foreach($props as $prop){
			$$prop = $this->propStr($e, $prop, false);
		}
		$disabled = '';
		if (!empty($e['readonly']))
			$disabled = "disabled='disabled'";
		$event .= " onblur='javascript:XT.checkElement(this, $strParams)' ";
		if (empty($e['placeholder']) && empty($e['defval']))
			$e['placeholder'] = "placeholder='Please Input {$e['label']} Here'";
		$style = " style='width:100%;'";
		$colspan = isset($e['colspan']) ? "colspan='{$e['colspan']}'" : '';
		$strE .= "<td class='cont-td' $colspan>";
		$date = ($e['type'] == 'date') ? "date='date'" : '';
		$prop_edit = "prop_edit='readonly'";
			
		$optionData = array();
		if (!empty($e['editoptions']['value']))
			$optionData = $this->str2Array($e['editoptions']['value']);
			
		switch($e['type']){
			case 'span':
				if (!empty($e['value']))
					$strE .= "<span params='$strParams'>{$e['value']}</span>";
				break;
			case 'img':
			case 'image':
				$strE .= "<img src='{$e['value']}'/>";
				break;
			case 'file':		
				if(!empty($e['editoptions']['value'])){
					$strE .= "<table><tbody><tr><td class='cont-td'>";
					$revert = array('%21'=>'!', '%2A'=>'*', '%27'=>"'", '%28'=>'(', '%29'=>')');
					foreach($e['editoptions']['value'] as $value){
						if(empty($value))
							continue;
						$v = explode(" ", $value);
						$path = strtr(rawurlencode($v[0]), $revert);
						$strE .= '<a href="/download.php?filename='.$path.'&remove=0">'.basename($value).'bytes</a>';
						$strE .= '<a>; </a>';
					}
					$strE .= "</td></tr></tbody></table>";
				}
				$strE .= "<input type='file' $required $id $name><input type='submit' id='upload_button' value='Upload'>";
				break;
			case 'date':
				$e['type'] = 'text';
			// case 'file':
			case 'password':
			case 'text':
			case 'hidden':
				// 需要根据不同的数据类型，设置不同的检查函数
//print_r($e['data_type']);
				$invalidChar = '';
				$min = '';
				$max = '';
				if (!isset($e['invalidChar'])){
					if (!empty($e['displayField']) && $oper != 'query'){
						$invalidChar = '[\\\/\?&%$#@!~`]';
					}
					else if ($e['data_type'] == 'int'){
						$invalidChar = '[^\d-]';
						if ($e['name'] == 'progress'){
							$min = "min='0'";
							$max = "max='100'";
						}
					}
					else if ($e['data_type'] == 'float' || $e['data_type'] == 'double')
						$invalidChar = '[^0-9\.-]';
				}
				else
					$invalidChar = $e['invalidChar'];
				if (!empty($invalidChar))
					$invalidChar = "invalidchar='$invalidChar'";
				
				$email = '';
				if ($e['name'] == 'email')
					$email = "email='1'";
				$strE .= "<input type='{$e['type']}' {$e['placeholder']} $ignored $id $name $prop_edit $invalidChar $min $max $email $class $value $readonly $editable $original_value $required $unique $style $event $date>";
				break;
				
			case 'textarea':
// print_r($e['value']);
				// $replace = array("\n\r", "\n", "\r");
				$e['value'] = str_replace(array("\r"), array(""), $e['value']);
				//计算行数
				if(!empty($e['value'])){
					$rowCount = substr_count($e['value'], "\n") + 1;
					$rows = "rows='$rowCount'";
				}
				$style = " style='width:100%; height:auto; overflow-y:visible;'";
				// $e['value'] = nl2br($e['value']);
				$strE .= "<textarea type='{$e['type']}' {$e['placeholder']} onfocus='XT.textareaScroll(this)' $ignored $id $name $class $prop_edit $readonly $editable $required $style $original_value $rows>{$e['value']}</textarea>";
				break;
				
			case 'select':
				$prop_edit = "prop_edit='disabled'";
				if (is_string($e['value']))
					$aV = explode(',', $e['value']);
				else
					$aV = array($e['value']);
// print_r($aV);
				$multiple = '';
				$size = '';
				$single_multi = '';
				$title = '';
				if (!empty($e['editoptions']['multiple']))
					$multiple = " multiple='multiple'";
				if (!empty($e['editoptions']['size']))
					$size = " size='{$e['editoptions']['size']}'";
				if(!empty($e['single_multi']))
					$single_multi = " single_multi='".json_encode($e['single_multi'])."'";
				if(!empty($e['title']))
					$title = " title='".$e['title']."'";
				$strE .= "<select type='{$e['type']}' $ignored $id $name $class $title $prop_edit $disabled $editable $style $original_value $multiple $size $required $single_multi>";
//print_r($e['editoptions']['value']);		
				foreach($optionData as $k=>$v){
					if(is_array($v)){
						$k = $v['id'];
						$name_field = $this->getDisplayField($v);
					}
					$selected = '';
					if (in_array($k, $aV))
						$selected = "selected='true' style='color:red' ";
					if(is_array($v)){
// print_r($v);
						$strE .= "<option value='$k' $selected";
						foreach($v as $kk=>$vv){
							if($kk == $name_field || $kk == 'id')
								continue;
							if($kk == 'note')
								$strE .= " title='".htmlentities($vv, ENT_QUOTES)."'";
							else
								$strE .= " $kk='$vv' ";
						}
						$strE .= ">{$v[$name_field]}</option>";
					}
					else{
						$strE .= "<option value='$k' $selected>$v</option>";
					}
				}

				$strE .= "</select>";
				break;
			case 'checkbox':
				$prop_edit = "prop_edit='disabled'";
				$aV = explode(',', $e['value']);
				$strE .= "<fieldset id='fieldset_{$e['name']}' $disabled $editable $prop_edit><table style='width:100%'>";
				$currentCol = 0;
				$cols = isset($e['cols']) ? $e['cols'] : 4;
//print_r("cols = $cols");	
				foreach($optionData as $k=>$v){
					if (empty($k))continue;
					if (!$currentCol)
						$strE .= "<tr>";
					$checkedStyle = '';
					$checked = '';
					if(is_array($v) && isset($v['id']))
						$k = $v['id'];
					if (in_array($k, $aV)){
						$checkedStyle = " style='color:red'";
						$checked = "checked='checked'";
						$original_value = "original_value='1'";
					}
					else
						$original_value = "original_value='0'";;
					$strE .= "<td><label $checkedStyle>".
						"<input type='checkbox' value='$k' $ignored $name $required $class $prop_edit $disabled $editable $original_value $checked";
					if(is_array($v)){
						$displayField = $this->getDisplayField($v);
						foreach($v as $kk=>$vv){
							if($kk == $displayField || $kk == 'id')
								continue;
							if($kk == 'note')
								$strE .= " title='".htmlentities($vv, ENT_QUOTES)."'";
							else
								$strE .= " $kk='$vv'";
						}
						$strE .= ">{$v[$displayField]}</label></td>";
					}
					else{
						$strE .= ">$v</label></td>";
					}
					$currentCol ++;
					if ($currentCol == $cols){
						$strE .= "</tr>";
						$currentCol = 0;
					}
				}
				if ($currentCol)
					$strE .= "</tr>";
				$strE .= "</table></fieldset>";
				break;
			
			case 'cart': //购物车模式,主要用于大量选项的多选，比如对多个Projects的多选
// print_r($e);				
				$prop_edit = "prop_edit='disabled'";
				$aV = explode(',', $e['value']);
				$cols = isset($e['cols']) ? $e['cols'] : 4;
				$onMouseOut = "XT.hideCartButton(\"div_cart_{$e['name']}\")";
				$onMouseOver = "onmouseover='XT.showCartButton(\"div_cart_{$e['name']}\")'";
				$style = "style='width:100%;'";
				if (empty($e['editable']))
					$onMouseOver = '';

				$single_multi = '';
				if(!empty($e['single_multi']))
					$single_multi = " single_multi='".json_encode($e['single_multi'])."'";

				$strE .= "<div id='div_cart_{$e['name']}' $disabled $editable $prop_edit onmouseout='$onMouseOut' $onMouseOver $single_multi>";
				$strE .= "<fieldset id='fieldset_{$e['name']}'><table cols='$cols' id='table_cart_{$e['name']}' style='width:100%'>";
				$currentCol = 0;
//print_r("cols = $cols");		
// print_r($optionData);
				foreach($optionData as $k=>$v){
					if (empty($k))continue;
// print_r($v);					
					$checked = '';
					if (in_array($k, $aV)){
						$checkedStyle = " style='color:red'";
						$checked = "checked='checked'";
						$original_value = "original_value='1'";
						if (!$currentCol)
							$strE .= "<tr>";
						$strE .= "<td><label><input type='checkbox' $checked value='$k' $name $class $prop_edit $disabled $editable $required $original_value";
						if(is_array($v)){
							$name_field = $this->getDisplayField($v);
// print_r($v);						
							foreach($v as $kk=>$vv){
								if($kk == 'name' || $kk == 'id')
									continue;
								if($kk == 'note')
									$strE .= " title='".htmlentities($vv, ENT_QUOTES)."'";
								else
									$strE .= " $kk='$vv'";
							}
							$strE .= ">{$v[$name_field]}</label></td>";
						}
						else{
							$strE .= ">$v</label></td>";
						}
						$currentCol ++;
						if ($currentCol == $cols){
							$strE .= "</tr>";
							$currentCol = 0;
						}
					}
				}
				if ($currentCol)
					$strE .= "</tr>";
				$strE .= "</table></fieldset>";
				// 增加Add和Remove按钮
				if (empty($e['cart_data']))
					$e['cart_data'] = '{}';
				$onAddClick = "onclick='XT.selectToCart(\"{$e['name']}\", \"{$e['cart_db']}\", \"{$e['cart_table']}\", \"{$e['label']}\", {$e['cart_data']})'";
				$onResetClick = "onclick='XT.resetCart(\"{$e['name']}\", \"{$e['cart_db']}\", \"{$e['cart_table']}\", \"{$e['label']}\", {$e['cart_data']})'";
				$onClearClick = "onclick='XT.clearCart(\"{$e['name']}\", \"{$e['cart_db']}\", \"{$e['cart_table']}\", \"{$e['label']}\", {$e['cart_data']})'";
				
				$display = "";
				$display = "style='display:none'";
				$strE .= "<div id='cart_button' $display>".
					"<button type='button' editable='1' id='cart_add_{$e['name']}' cart='{$e['name']}' $required $onAddClick>Add</button>".
					"<button type='button' editable='1' id='cart_reset_{$e['name']}' cart='{$e['name']}' $required $onResetClick>Reset</button>".
					"<button type='button' editable='1' id='cart_clear_{$e['name']}' cart='{$e['name']}' $required $onClearClick>Clear All</button>".
					"</div>";
				$strE .= "</div>";
				break;
							
			case 'radio':
				$prop_edit = "prop_edit='disabled'";
				$strE .= "<fieldset id='fieldset_{$e['name']}' $disabled $editable $prop_edit><table style='width:100%'>";
				$currentCol = 0;
				$cols = isset($e['cols']) ? $e['cols'] : 4;
// print_r($optionData);				
				if(count($optionData) < $cols)
					$cols = count($optionData);
// print_r("cols = $cols");	
				foreach($optionData as $k=>$v){
					if (empty($k))continue;
					if (!$currentCol)
						$strE .= "<tr>";
					$checked = '';
					if ($k == $e['value'])
						$checked = " checked='checked'";
					$strE .= "<td><label>".
						"<input type='radio' value='$k' $ignored $name $required $class $prop_edit $disabled $editable $original_value $checked";
					if(is_array($v)){
						foreach($v as $kk=>$vv){
							if($kk == 'name' || $kk == 'id')
								continue;
							if($kk == 'note')
								$strE .= " title='".htmlentities($vv, ENT_QUOTES)."'";
							else
								$strE .= " $kk='$vv'";
						}
						$strE .= ">{$v['name']}</label></td>";
					}
					else{
						$strE .= ">$v</label></td>";
					}
					$currentCol ++;
					if ($currentCol == $cols){
						$strE .= "</tr>";
						$currentCol = 0;
					}
				}
				if ($currentCol)
					$strE .= "</tr>";				
				// foreach($e['editoptions']['value'] as $k=>$v){
					// $checked = '';
					// if ($k == $e['value'])
						// $checked = " checked='checked'";
					// $strE .= "<label><input type='radio' $ignored $name $class $prop_edit $disabled $editable $original_value value='$k' $checked>$v</label> ";
				// }
				$strE .= "</table></fieldset>";
				break;
			case 'div': //占位，便于后续替换
				$strE .= "<div $id $style $class>";
				if(!empty($e['temp'])){
					$sub = $e['temp'];
					$ces = $sub['ces'];
					$editable = isset($sub['editable']) ? $sub['editable'] : true;
					$v = isset($sub['value']) ? $sub['value'] : null;
					$cols = isset($sub['cols']) ? $sub['cols'] : 3;
					$strSub = $this->_cf($ces, $editable, $v, $cols);
// print_r($e['temp']);
					// print_r($strSub)					;
					$strE .= $this->_cf($ces, $editable, $v, $cols);
					
				}
				$strE .= "</div>";
				break;
			case 'fieldset':
				$strE .= "<fieldset $id $style $class>";
				if(!empty($e['legend']))
					$strE .= "<legend>{$e['legend']}</legend>";
				if(!empty($e['temp'])){
					$sub = $e['temp'];
					$ces = $sub['ces'];
					$editable = isset($sub['editable']) ? $sub['editable'] : true;
					$v = isset($sub['value']) ? $sub['value'] : null;
					$cols = isset($sub['cols']) ? $sub['cols'] : 3;
					$strE .= $this->_cf($ces, $editable, $v, $cols);
				}
				$strE .= "</fieldset>";
				break;
			case 'multi_row_edit':
				$strE .= $this->multiRowEdit($e);//$e['temp'], $e['prefix'], $e['legend'], $e['value'], true);//$e['editable']);
				break;
			case 'embed_table':
				$strE .= $this->generateEmbed_table($e);//$e['temp'], $e['prefix'], $e['legend'], $e['value'], true);//$e['editable']);
				break;
			case 'iframe':
				$strE .= "<iframe $id $name />";
				break;
		}
		$strE .= "</td>";
		if (!empty($e['post'])){
			$strE .= "<td width='1'>".$this->generatePost($e, $strParams, $oper)."</td></tr></table></td>";
		}
		return $strE;
	}
	
	function ces($es, $cols, $params = array(), $oper = 'view'){
		$str = $this->_ces($es, $cols, $params, $oper);
		print_r($str);
	}
	
	function _ces($es, $cols, $params = array(), $oper = 'view'){
		$currentCol = 0;
		$str = array();
		$str[] = "<table class='ces' width='100%'><tr>";
		$w1 = 25 / $cols;
		$w2 = 75 / $cols;
		if($cols == 1){
			$w1 = 10;
			$w2 = 90;
		}
		for($i = 0; $i < $cols; $i ++){
			$str[] = "<th class='ces' style='width:$w1%' /><th class='ces' style='width:$w2%' />";
		}
		$str[] = "</tr>";
		$tr = true;
		$strParams = json_encode($params);
		foreach($es as $e){
			if ($tr){
				$str[] = "<tr id='ces_tr_{$e['id']}' class='ces'>";
				$tr = false;
			}
			if ($e['type'] == 'hidden')
				continue;
            $str[] = $this->ce($e, $strParams, $oper);
			$colSpan = empty($e['colspan']) ? 1 : $e['colspan'];
			$currentCol ++;
			if($colSpan > 1)
				$currentCol += ceil(($colSpan - 1)/2);
			if ($currentCol >= $cols){
				$str[] = "</tr>";
				$currentCol = 0;
				$tr = true;
			}
		}
		if (!$tr)
			$str[] = "</tr>";
		$str[] = "</table>";	
// print_r($str);
		return implode('', $str);
	}

	function cf2($colModels, $v = null, $column = 1, $display_status = DISPLAY_STATUS_VIEW){
		$form = new kf_form($colModels, $v, $display_status);
		$str = $form->display($column);
		print_r($str);
	}
	
	function cf($colModels, $editable = false, $v = null, $columns = 1, $query = false, $params = array(), $new = false){
		$display_status = DISPLAY_STATUS_VIEW;
		if($new)
			$display_status = DISPLAY_STATUS_NEW;
		elseif($query)
			$display_status = DISPLAY_STATUS_QUERY;
		
		// $display_status = DISPLAY_STATUS_EDIT;
		$form = new kf_form($colModels, $v, $display_status);
		$str = $form->display($columns);
	
		// $str = $this->_cf($colModels, $editable, $v, $columns, $query, $params, $new);
		print_r($str);
	}
	
	function _cf($colModels, $editable = false, $v = null, $columns = 1, $query = false, $params = array(), $new = false){
		if (empty($colModels))
			return;
		$oper = 'view';
		if ($query)
			$oper = 'query';
		else if ($new)
			$oper = 'new';
		$es = array();
		$hidden = array();
//print_r($colModels);		
		foreach($colModels as $key=>$model){
			$e = $this->model2e($model, $v, $oper, $editable);
			if($e['type'] == 'hidden'){
				$hidden[] = $e;
			}
			else
				$es[] = $e;
		}
// print_r($hidden);		
		$str = array();
		$str[] = "<table><tr>";
		foreach($hidden as $e){
			$str[] = "<td><input type='hidden' name='{$e['name']}' id={$e['id']} value='{$e['value']}'></td>";
			// print_r($this->generateInput($e, '', $oper));
		}
		$str[] = "</tr></table>";
// print_r($es);
		$str[] = $this->_ces($es, $columns, $params, $oper);
		return implode('', $str);
	}

	function model2e2($model, $value, $display_status){
// if($model['name'] == 'prj_id'){
// print_r($model);
// print_r($value);
// }
		$p = array('required'=>false, 'cols'=>4, 'rows'=>3, 'colspan'=>1, 'init_type'=>'single', 
			'limit'=>'', 'displayField'=>'', 
			// 'addoptions'=>array(), 'editoptions'=>array(), 'queryoptions'=>array(), 'searchoptions'=>array(),
			'name'=>'', 'id'=>'', 'label'=>'', 'editable'=>true, 'type'=>'', 'unique'=>false,
			'post'=>array(), 'class'=>array(), 'placeholder'=>'', 'DATA_TYPE'=>'varchar', 'invalidChar'=>'', 'email'=>0,
			'force_readonly'=>false, 'ignored'=>false, 
			'temp'=>array(), 'legend'=>'', 'prefix'=>'', 'data_source_db'=>'', 'data_source_table'=>'',
			'cart_db'=>'', 'cart_table'=>'', 'cart_data'=>array(),
			);
		$e = $this->array_extends($p, $model);
// if($e['name'] == 'hw_ae_id')		
	// print_r($e);
		if(empty($e['id']) && !empty($e['name']))
			$e['id'] = $e['name'];
		if(empty($e['name']) && !empty($e['id']))
			$e['name'] = $e['id'];
		if(empty($e['label']))
			$e['label'] = ucfirst($e['name']);
		if(empty($e['required']))
			$e['required'] = isset($model['editrules']['required']) ? $model['editrules']['required'] : false;
		if(empty($e['prefix']) && !empty($e['id']))
			$e['prefix'] = $e['id'];
			
// $l = 0;
		if($display_status == DISPLAY_STATUS_NEW && !empty($model['addoptions'])){
// $l = 1;			
			$e['editoptions'] = $model['addoptions'];
		}
		elseif($display_status == DISPLAY_STATUS_QUERY && !empty($model['searchoptions'])){
// $l = 2;			
			$e['editoptions'] = $model['searchoptions'];
		}
		else{
// $l = 3;			
			$e['editoptions'] = isset($model['editoptions']) ? $model['editoptions'] : 
			(isset($model['addoptions']) ? $model['addoptions'] : 
				(isset($model['formatoptions']) ? $model['formatoptions'] : array())
			);
		}
// if($e['name'] == 'prj_id'){
	// print_r($l);
	// print_r($model);
	// print_r($e);
// }

		if(!empty($e['editoptions']['value']) && is_string($e['editoptions']['value'])){
			$e['editoptions']['value'] = $this->str2Array($e['editoptions']['value']);
		}
		if (empty($e['type'])){
			if($display_status == DISPLAY_STATUS_QUERY && !empty($model['queryoptions']['querytype']))
				$e['type'] = $model['queryoptions']['querytype'];
			else if (!empty($model['edittype']))
				$e['type'] = $model['edittype'];
			else if (!empty($model['stype']))
				$e['type'] = $model['stype'];
			else
				$e['type'] = 'text';
		}
		switch($e['type']){
			case 'textarea':
				if($display_status == DISPLAY_STATUS_QUERY)
					$e['type'] = 'text';
				break;
			case 'select':
				if($display_status != DISPLAY_STATUS_QUERY){
					$cc = 0;
					if(!empty($e['editoptions']['value']))
						$cc = count($e['editoptions']['value']);
					if(!empty($e['editoptions']['multiple'])){
						if($cc < 10 || empty($e['cart_db']) || empty($e['cart_table']))
							$e['type'] = 'checkbox';
						else
							$e['type'] = 'cart';
					}
					// elseif($cc < 4){
						// $e['type'] = 'radio';
					// }
				}
				break;
			case 'single_multi':
				if($e['init_type'] == 'single'){
					// $e['type'] = 'select';
					$e['editoptions']['multiple'] = false;
					$e['editoptions']['size'] = 1;
					$e['post'] = array('type'=>'button', 'value'=>'+', 'id'=>'single_to_multi', 'title'=>'Change to multe-selction', 
						'event'=>array('onclick'=>'XT.single_or_multi(this)'));
				}
				else{
					// $e['type'] = 'cart';
					$e['editoptions']['multiple'] = false;
					$e['editoptions']['size'] = 1;
					$e['post'] = array('type'=>'button', 'value'=>'-', 'id'=>'multi_to_single', 'title'=>'Change to single selection',
						'event'=>array('onclick'=>'XT.single_or_multi(this)'));
				}
				$e['single_multi'] = array('db'=>$e['cart_db'], 'table'=>$e['cart_table'], 'options'=>$e['editoptions']);
				break;
			case 'multi_row_edit':
			case 'embed_table':
				$e['editable'] = true;
				break;
			
		}

		if (!$e['editable'] || !empty($model['readonly']))
			$e['readonly'] = 'readonly';
			
		if (isset($value[$e['name']])){
			$e['value'] = $value[$e['name']];
		}
		elseif(isset($value[$e['id']])){
			$e['value'] = $value[$e['id']];
		}
		elseif(isset($model['value']))
			$e['value'] = $model['value'];
		elseif(isset($e['defval']))
			$e['value'] = $e['defval'];

		if(!isset($e['value']))
			$e['value'] = '';
		if(!is_array($e['value']))
			$e['value'] = htmlentities($e['value'], ENT_QUOTES);
			
		$e['original_value'] = $e['value'];
		if ($display_status == DISPLAY_STATUS_QUERY){
			$e['unique'] = false;
			$e['required'] = false;
			if (!empty($e['force_readonly'])){
				$e['editable'] = false;
				$e['readonly'] = true;
			}
			else{
				$e['editable'] = true;
				$e['readonly'] = false;
			}
		}
		if(empty($e['required']))
			unset($e['required']);
		else
			$e['class'][] = 'required';

		if(empty($e['unique']))
			unset($e['unique']);
		else
			$e['class'][] = 'unique_unknown';
			
		// invalidChar
		if(empty($e['invalidChar'])){
			switch($e['DATA_TYPE']){
				case 'int':
					$e['invalidChar'] = '[^\d-]';
					if ($e['name'] == 'progress'){
						$e['min'] = "min='0'";
						$e['max'] = "max='100'";
					}
					break;
				case 'float':
				case 'double':
					$e['invalidChar'] = '[^0-9\.-]';
					break;
				
			}
		}
		if($e['name'] == 'email')
			$e['email'] = 1;
		if(empty($e['placeholder']))
			$e['placeholder'] = "Please input {$e['label']} here";
		if(!in_array($e['type'], array('text', 'textarea')))
			unset($e['placeholder']);
		//处理editoptions里的Value，主要是要将id和name转换名称
// print_r($e['editoptions']);		
		if(!empty($e['editoptions']['value'])){
			foreach($e['editoptions']['value'] as $k=>&$v){
				if(is_array($v)){
					$displayField = $this->getDisplayField($v);
					if($displayField != 'id'){
						$v['label'] = $v[$displayField];
						unset($v[$displayField]);
					}
					if(isset($v['id'])){
						$v['value'] = $v['id'];
						unset($v['id']);
					}
				}
			}
		}
		if(!empty($e['post'])){
			if(!isset($e['post']['type']))
				$e['post']['type'] = 'text';
		}
		if($e['DATA_TYPE'] == 'date' || $e['DATA_TYPE'] == 'date_time'){
			// $e['type'] = 'date';
			// if($display_status == DISPLAY_STATUS_EDIT || $display_status == DISPLAY_STATUS_NEW){
				// if(empty($e['post']['value']))
					// $e['post']['value'] = '(yyyy-mm-dd)';
				// if(empty($e['post']['type']))
					// $e['post']['type'] = 'text';
			// }
			$e['date'] = 'date';
		}
// print_r($e);			
		return $e;
	}
	
	function model2e($model, $v = array(), $oper = 'view', $editable = true){
			$e = array(
				'name'=>isset($model['name']) ? $model['name'] : '', 
				'id'=>isset($model['id']) ? $model['id'] : (isset($model['name']) ? $model['name'] : ''), 
				'label'=>isset($model['label']) ? $model['label'] : '',
				'displayField'=>isset($model['displayField']) ? $model['displayField'] : false,
				'editable'=>isset($model['editable']) ? $model['editable'] : $editable,
				'type'=>isset($model['type']) ? $model['type'] : '',
				'unique'=>isset($model['unique']) ? $model['unique'] : false,
				'required'=>isset($model['editrules']['required']) ? $model['editrules']['required'] : false,
				'editoptions'=>isset($model['editoptions']) ? $model['editoptions'] : array(),
				'post'=>isset($model['post']) ? $model['post'] : array(),
				'class'=>isset($model['class']) ? $model['class'] : '',
				'placeholder'=>isset($model['placeholder']) ? $model['placeholder'] : '',
				'data_type'=>isset($model['DATA_TYPE']) ? $model['DATA_TYPE'] : 'varchar',
				'cart_db'=>isset($model['cart_db']) ? $model['cart_db'] : '',
				'cart_table'=>isset($model['cart_table']) ? $model['cart_table'] : '',
				'cart_data'=>isset($model['cart_data']) ? $model['cart_data'] : '',
				'cols'=>isset($model['cols']) ? $model['cols'] : 4,
				'force_readonly'=>isset($model['force_readonly']) ? $model['force_readonly'] : false,
				'invalidChar'=>isset($model['invalidChar']) ? $model['invalidChar'] : null,
				'rows'=>isset($model['rows']) ? $model['rows'] : 3,
				'init_type'=>isset($model['init_type']) ? $model['init_type'] : 'single',
				'ignored'=>isset($model['ignored']) ? $model['ignored'] : '',
				'temp'=>isset($model['temp']) ? $model['temp'] : array(), // for multirowedit
				'prefix'=>isset($model['prefix']) ? $model['prefix'] : '', // for multirowedit
				'legend'=>isset($model['legend']) ? $model['legend'] : '', // for fieldset
				'data_source_table'=>isset($model['data_source_table']) ? $model['data_source_table'] : '', // for multirowedit
				'data_source_db'=>isset($model['data_source_db']) ? $model['data_source_db'] : '', // for multirowedit
				'limit'=>isset($model['limit']) ? $model['limit'] : '',
			);
			if (isset($model['colspan']))
				$e['colspan'] = $model['colspan'];
			if(empty($e['prefix']))
				$e['prefix'] = $e['id'];
			if (empty($e['type'])){
				if($oper == 'query' && !empty($model['queryoptions']['querytype']))
					$e['type'] = $model['queryoptions']['querytype'];
				else if (!empty($model['edittype']))
					$e['type'] = $model['edittype'];
				else
					$e['type'] = 'text';
			}
			if ($oper != 'query' && $e['type'] == 'select' && !empty($model['editoptions']['multiple'])){
				if (count($model['editoptions']['value']) < 10 || empty($e['cart_db']) || empty($e['cart_table'])){
					$e['type'] = 'checkbox';
				}
				else
					$e['type'] = 'cart';
			}
			if (empty($model['DATA_TYPE']))
				$model['DATA_TYPE'] = 'text';
			if($model['DATA_TYPE'] == 'datetime' || $model['DATA_TYPE'] == 'date')
				$e['type'] = 'date';
				
			if ($oper == 'query' && !empty($model['searchoptions'])){
				$e['editoptions'] = $model['searchoptions'];
//print_r($e);				
			}else if ($oper == 'new' && !empty($model['addoptions']))
				$e['editoptions'] = $model['addoptions'];
			
			// $e['options'] = $this->str2Array($e['editoptions']['value']);
			
			if($e['type'] == 'multi_row_edit' || $e['type'] == 'embed_table')
				$e['editable'] = $editable;
			if ($editable == false || !$e['editable'] || !empty($model['readonly']))
				$e['readonly'] = 'readonly';
			$e['value'] = '';
			if($e['type'] == 'select' || ($e['type'] == 'single_multi' && $e['init_type'] == 'single'))
				$e['value'] = 0;
			if (isset($v[$e['name']])){
				$e['value'] = $v[$e['name']];
			}
			elseif(!empty($v[$e['id']])){
				$e['value'] = $v[$e['id']];
			}
			else{
				if (($oper == 'new' || $oper == 'view')&& isset($model['defval']))
					$e['value'] = $model['defval'];
				else if ($oper == 'query' && isset($model['queryoptions']['value']))
					$e['value'] = $model['queryoptions']['value'];
			}
			if(!is_array($e['value']))
				$e['value'] = htmlentities($e['value'], ENT_QUOTES);
			$e['original_value'] = $e['value'];
			if ($oper == 'query'){
				if($e['type'] == 'textarea') // query里没有textarea的必要
					$e['type'] = 'text';
				$e['unique'] = false;
				$e['required'] = false;
				if (!empty($e['force_readonly'])){
					$e['editable'] = false;
					$e['readonly'] = true;
				}
				else{
					$e['editable'] = true;
					$e['readonly'] = false;
				}
			}
			if($e['type'] == 'single_multi'){
				if($e['init_type'] == 'single'){
					$e['type'] = 'select';
					$e['editoptions']['multiple'] = false;
					$e['editoptions']['size'] = 1;
					$e['post'] = array('type'=>'button', 'value'=>'+', 'id'=>'single_to_multi', 'title'=>'Change to multe-selction');
				}
				else{
					$e['type'] = 'cart';
					$e['editoptions']['multiple'] = false;
					$e['editoptions']['size'] = 1;
					$e['post'] = array('type'=>'button', 'value'=>'-', 'id'=>'multi_to_single', 'title'=>'Change to single selection');
				}
				$e['single_multi'] = $model['single_multi'];
				$e['single_multi']['options'] = $e['editoptions'];
			}
				
			return $e;
		}
	
	function multiRowEdit($comp){//}$temp, $prefix, $legend, $values = array(), $edit = true){ //用模板生成多行编辑界面
		if(empty($comp['temp']))
			return 'No Detail Yet';
// print_r($comp);
		$temp = $comp['temp'];
		$prefix = $comp['prefix'];
		$legend = $comp['legend'];
		$values = $comp['value'];
		$editable = $comp['editable'];
		$db = $comp['data_source_db'];
		$table=$comp['data_source_table'];
		
		// $onMouseOut = "XT.hide(\"#{$prefix}_temp\")";
		// $onMouseOver = "onmouseover='XT.show(\"#{$prefix}_temp\")'";
		$str = array();
		$cols = count($temp);
		foreach($temp as $k=>$e){
			$temp[$k]['ignored'] = 'ignored';
		}
		
		$onMouseOut = "XT.hideMultiRowTemp(\"$prefix\")";
		$onMouseOver = "onmouseover='XT.showMultiRowTemp(\"$prefix\")'";
		
		$str[] = "<fieldset onmouseout='$onMouseOut' $onMouseOver><legend>$legend</legend>";
		$str[] = "<div multirowedit='multirowedit' id='{$prefix}' >";
		$str[] = "<div id='{$prefix}_temp' style='display:none;'>";
			$str[] = "<div ignored='ignored' style='float:left;width:90%;'>";
			$str[] = $this->_cf($temp, $editable, null, $cols, false, array('db'=>$db, 'table'=>$table), true);
			$onclick = "javascript:XT.addNewRowForMulti(\"$prefix\")";
			$str[] = "</div>";
			$str[] = "<div ignored='ignored' style='float:right;'><button style='vertical-align:bottom;' onclick='$onclick' type='button' id='{$prefix}_add'>Add</button></div>";
		$str[] = "</div>";
		$str[] = "<div style='clear:both;'><table id='{$prefix}_values' border='1' cellspacing='1' style='width:100%;background-color:#a0c6e5;'><tbody>";
		$str[] = "<tr id='{$prefix}_header' >";
		$str[] = "<th id='del' width='20px'>X</th>";
		foreach($temp as $e){
			if(empty($e['id'])) $e['id'] = $e['name'];
			$label = $e['label'];
			if(!empty($e['post']))
				$label .= "({$e['post']})";
			$str[] = "<th id='{$e['id']}'>$label</th>";
		}
		$str[] = "</tr>";
// print_r($temp);
		// values
		if(!empty($values)){
			$p = array();
			$strP = json_encode($p);
			foreach($values as $vp){
				$str[] = "<tr><td id='del'><a editable='1' disabled='true' prop_edit='disabled' onclick='javascript:XT.deleteSelfRow(this)' href='javascript:void(0)'>X</a></td>";
				foreach($temp as $k=>$model){
					$e = $this->model2e($model, $vp, 'view', false);
					$e['editable'] = 0;
					$str[] = $this->generateInput($e, $strP, 'view');
				}
				$str[] = "</tr>";
			}
		}
		$str[] = "</tbody></table></div>";
		$str[] = "</div>";
		$str[] = "</fieldset>";
		return implode('', $str);
	}
	
	function generateEmbed_table($comp){//}$temp, $prefix, $legend, $values = array(), $edit = true){ //用模板生成多行编辑界面
		if(empty($comp['temp']))
			return 'No Detail Yet';
// print_r($comp);
		$temp = $comp['temp'];
		$prefix = $comp['prefix'];
		$legend = $comp['legend'];
		$values = $comp['value'];
		$editable = $comp['editable'];
		$db = $comp['data_source_db'];
		$table=$comp['data_source_table'];
		
		$str = array();
		foreach($temp as $k=>$e){
			// $temp[$k]['ignored'] = 'ignored';
			$name = $temp[$k]['name'];
			$temp[$k]['name'] = $prefix.'['.$name.']';
			$temp[$k]['id'] = $name;
// print_r($temp[$k]);
		}
// print_r($values);
		$str[] = "<fieldset><legend>$legend</legend>";
		$str[] = "<div embed_table='embed_table' id='{$prefix}' >";//" onmouseout='$onMouseOut' $onMouseOver>";
		$str[] = $this->_cf($temp, $editable, $values, 1, false, array('db'=>$db, 'table'=>$table), true);
		$str[] = "</div>";
		$str[] = "</fieldset>";
		return implode('', $str);
	}
	
	//view 主要以下几种类型：span, img, email
	function createView($models, $value, $cols = 2, $options = array('label'=>true, 'type'=>'view', 'editable'=>true)){
		$es = array();
		$fields = array('name', 'label'=>'', 'type'=>'span', 'editoptions'=>array(), 'cols'=>4);
		foreach($models as $model){
			$e = $this->extractItems($fields, $model);
			$e['id'] = isset($model['id']) ? $model['id'] : $model['name'];
			if (isset($value[$e['name']]))
				$e['value'] = $value[$e['name']];
			if (!$options['label'])
				$e['label'] = '';
			if($e['type'] != 'img' && $e['type'] != 'email')
				$e['type'] = 'span';
			$es[] = $e;
		}
		$this->ces($es, $cols);
	}
	
    function createElement($e, $table = false){
		$prop = array("style='width:95%'");
		$optionProp = array();
		$postFix = '';
		foreach($e as $key=>$p){
			switch($key){
				case 'type':
				case 'label':
					break;
				case 'postFix':
					$postFix = $e['postFix'];
					break;
				case 'options':
					foreach($p as $id=>$v){
						if (is_array($v)){
							if (!isset($v['value'])){
								if (isset($v['id']))
									$v['value'] = $v['id'];
								else
									$v['value'] = $id;
							}
						}
						else{
							$v = array('value'=>$id, 'name'=>$v);
						}
						if(isset($e['value']) && $e['value'] == $v['value'])
							$v['selected'] = 'selected';
						$options = array();
						foreach($v as $itemId=>$item){
							$options[] = "$itemId='$item'";
						}
						$optionProp[$id] = implode(' ', $options);
					}
					break;
					
				case 'data_type':
					if ($p == 'date'){
						$prop['date'] = "date='date'";
					}
					break;
					
				default:
					$prop[$key] = "$key='$p'";
					break;
			}
		}
		
		$strProp = implode(' ', $prop);
		$str = '';
		switch($e['type']){
			case 'date':
            case 'text':
				if ($table)
					$str = "<td style='text-align:right'>{$e['label']}:</td><td><input type='text' $strProp/>$postFix</td>";
				else
					$str = "<label for='{$e['id']}'>{$e['label']}<input type='text' $strProp/>$postFix</label>";
                break;
			case 'hidden':
				if (!$table)
					$str = "<input type='hidden' $strProp/>";
				break;
					
            case 'textarea':
				$e['value'] = isset($e['value']) ? $e['value'] : '';
				if ($table)
					$str = "<td style='text-align:right'>{$e['label']}:</td><td><textarea $strProp>{$e['value']}</textarea>$postFix</td>";
				else
					$str = "<label for='{$e['id']}'>{$e['label']}<textarea $strProp>{$e['value']}</textarea>$postFix</label>";
                break;
			
            case 'checkbox':
				if ($table){
					$str = "<td style='text-align:right'>{$e['label']}:</td><td>";
					if (!empty($e['options'])){
						foreach($e['options'] as $key=>$v){
							$str .= "<label><input type='checkbox' {$optionProp[$key]} />{$v['label']}</label>";
						}
					}
					$str .= "</td>";
				}
				else{
					$str = "<label for='{$e['id']}'>{$e['label']}<input type='checkbox' $strProp /></label>";
				}
                break;
            case 'select':
				if ($table){
					$str = "<td style='text-align:right'>{$e['label']}:</td><td><select $strProp>";
					if (!empty($e['options'])){
						foreach($e['options'] as $key=>$v){
							if (is_array($v)){
								if (!isset($v['id']))
									$v['id'] = $key;
							}
							else{
								$v = array('id'=>$key, 'name'=>$v);
							}
							$str .= "<option {$optionProp[$key]} >{$v['name']}</option>";
						}
					}
					$str .= "</select></td>";
				}
				else{
					$selected = '';
					$str = "<label for='{$e['id']}'>{$e['label']}<select $strProp >";
					if (!empty($e['options'])){
						foreach($e['options'] as $key=>$v){
							$str .= "<option {$optionProp[$key]}>{$v['name']}</option>";
						}
					}
					$str .= "</select></label>";
				}
                break;
            case 'button':
                $str = "<input type='button' $strProp />";
//				if ($table)
//					$str = "<td>" + $str + "</td>";
                break;
            case 'fieldset':
                $str = sprintf("<fieldset id='%e'><legend>%s</legend>", $e['id'], $e['legend']);
                break;
            case '/fieldset':
                $str = "</fieldset>";
                break;
            case 'div':
                $str = sprintf("<div id='%s'>", $e['id']);
                break;
            case '/div':
                $str = '</div>';
                break;
        }
        print_r($str);
    }
    
    function createElements($es, $table = true, $cols = 1){
		$tr = false;
		$currentCol = 0;
		if ($table){
			print_r("<table width='100%'><tr>");
			$w1 = 20 / $cols;
			$w2 = 80 / $cols;
			for($i = 0; $i < $cols; $i ++){
				print_r("<th style='width:$w1%' /><th style='width:$w2%' />");
			}
			print_r("</tr>");
			$tr = true;
		}
		foreach($es as $e){
			if ($tr){
				print_r("<tr>");
				$tr = false;
			}
            $this->createElement($e, $table);
			if ($table){
				if ($e['type'] == 'hidden')
					continue;
				$currentCol ++;
				if ($currentCol == $cols){
					print_r("</tr>");
					$currentCol = 0;
					$tr = true;
				}
			}
		}
		if (!$tr)
			print_r("</tr>");
		if ($table)
			print_r("</table>");
    }
	
	function createForm($colModels, $editable = false, $v = null, $columns = 2){
//print_r($v);
		$es = array();
		foreach($colModels as $key=>$model){
			if(isset($model['view']) && $model['view'] == false)
				continue;
			$e = array('label'=>$model['label'], 'name'=>$model['name'], 'type'=>'text', 'id'=>$model['name']);
			if (!empty($model['edittype']))
				$e['type'] = $model['edittype'];
			if (!empty($model['edittype']) && $model['edittype'] == 'select'){
				$e['type'] = 'select';
				if(!empty($model['editoptions']['multiple']))
					$e['type'] = 'checkbox';
				$e['options'] = $model['editoptions']['value'];
			}
			if ($editable == false || !$model['editable'])
				$e['disabled'] = 'disabled';
			if ($model['editable'] == true)
				$e['editable'] = 'editable';
			$e['original_value'] = '';
			if (isset($v[$model['name']])){
				$e['value'] = $e['original_value'] = $v[$model['name']];
			}
			$e['data_type'] = $model['DATA_TYPE'];
			$es[] = $e;
		}
//print_r($es);		
		$this->createElements($es, true, $columns);
	}
	
	function showInfo($fields, $v, $cols = 2){
		$es = array();
		foreach($fields as $k=>$l){
			if (is_int($k))
				$k = $l;
			$l = ucwords($l);
			$es[] = array('type'=>'text', 'label'=>$l, 'value'=>$v[$k], 'disabled'=>'disabled');
		}
		$this->createElements($es, true, $cols);
	}
	
	function showTable($fields, $vs){
//print_r($fields);		
		print_r("<table class='alt_table_border' style='width:100%'>");
		print_r("<tr class='ui-jqgrid-htable'>");
		$fs = array();
		foreach($fields as $k=>&$l){
			if (is_int($k))
				$k = $l;
			if (!is_array($l))
				$l = array('label'=>ucwords($l));
			if (empty($l['label']))$l['label'] = ucwords($k);
			if (empty($l['field']))
				$l['field'] = $k;
			if (empty($l['type']))
				$l['type'] = 'text';
			print_r("<th>{$l['label']}</th>");
			$fs[$k] = $l;
		}
		print_r("</tr>");
//print_r($fields);		
		$i = 0;
		foreach($vs as $v){
			$alt_row = '';
			if ($i ++ % 2)
				$alt_row = " ui-priority-secondary td_grey";
			print_r("<tr class='ui-widget-content jqgrow ui-row-ltr $alt_row'>");
			foreach($fs as $l){
				if (empty($v[$l['field']]))
					print_r("<td></td>");
				else if ($l['type'] == 'checkbox'){
					print_r("<td><input type='checkbox' class='cbox' value='{$v[$l['field']]}' name='{$l['field']}' ></td>");
				}
				else if ($l['type'] == 'link'){
					$href = $this->replaceParam($l['href'], $v);
					print_r("<td><a href='$href'>{$v[$l['field']]}</a></td>");
				}
				else
					print_r("<td name='{$l['field']}'>{$v[$l['field']]}</td>");
			}
			print_r("</tr>");
		}
		print_r("</table>");
	}
	
	//style:default(row for property)/json( merge all properties in one line)
	function showTree($tree, $levels, $style = 'default'){//levels存放节点间父子关系，如levels = array('peripheral'=>array('sub'=>'register', 'label'=>'name'))
		//表头
		$lc = count($levels) + 1;
		$header = $levels['root']['header'];
// print_r($header);	
		$str = "<table class='alt_table_border' style='width:100%'>";
		$str .= $this->treeHeader($lc, $header, $style);
		$node = $tree['root'][0];
		$diff = false;
		$str .= $this->genLevel(0, $node, $levels, $style, $header, $lc, 'root', 0, $tree, $diff, false);
		$str .= "</table>";
		print_r($str);
	}
	
	function genLevelToArray($id, $node, $levels, $style, $header, $lc, $currentLevel, $deep, $tree, &$diff = false){
		//先处理子节点
		$subNodes = array();
		$sub_levels = array();
		if(!empty($levels[$currentLevel]['sub'])){
			$sub_levels = explode(',', $levels[$currentLevel]['sub']);
			foreach($sub_levels as $sub_level){
				if(isset($tree[$sub_level][$id]))
					$subNodes[$sub_level] = $tree[$sub_level][$id];
			}
		}
		$sub_lines = array();
		$sub_diff = false;
		$i = 0;
		if(!empty($subNodes)){
			foreach($subNodes as $type=>$subNode){
				$sub_lines[$i] = $this->staffTDToArray($type."(s)", $lc, $deep, true, $toArray);
				$rem_line = $i ++;
				foreach($subNode as $sub_id=>$n){
					$tmp = $this->genLevelToArray($sub_id, $n, $levels, $style, $header, $lc, $type, $deep + 1, $tree, $sub_diff);
					foreach($tmp as $line){
						$sub_lines[$i ++] = $line;
					}
				}
				$sub_lines[$rem_line]['is_same'] = !$sub_diff;
			}
		}
		
		//再处理自身属性
		$node_name = $currentLevel;
		$getName = false;
		$ignore = isset($levels[$currentLevel]['ignore']) ? $levels[$currentLevel]['ignore'] : array();
		$lines = array();
		$i = 0;
		foreach($node as $p=>$v){
			if(in_array($p, $ignore))
				continue;
			$orig = null;
			$p_diff = false;
			$line = '';
			foreach($header as $k=>$name){
				$value = isset($v[$k]) ? $v[$k] : '';
				if(is_null($orig))
					$orig = $value;
				else{
					if($orig != $value)
						$p_diff = true;
				}
				if($p == 'name'){
					if(!$getName && !empty($value)){
						$node_name .= '#'.$value;
						$getName = true;
					}
				}
				$line .= $value;
			}
			$lines[$i] = $this->staffTDToArray($currentLevel, $lc, $deep, false);
			$lines[$i]['is_same'] = !$p_diff;
			// $lines[$i][]
			// ."<td>$p</td>$line</tr>";
			
			$i ++;
		}
		$class = 'ui-widget-content jqgrow ui-row-ltr';
		if($p_diff || $sub_diff)
			$diff = true;
// if($diff)		
// print_r(">>node_name = $node_name, diff = $diff<<");		
		if($p_diff || $sub_diff)
			$class .= ' hilight';
		else
			$class .= ' thesame';
		$str = '';
		if($currentLevel != 'root')
			$str .= "<tr class='$class'>".$this->staffTDToArray($node_name, $lc, $deep, true)."</tr>";
		// array_($str, $lines, $sub_lines);
		
		$str .= implode('', $lines).
			implode('', $sub_lines); 
		return $str;
	}
	
	function genLevel($id, $node, $levels, $style, $header, $lc, $currentLevel, $deep, $tree, &$diff = false, $toArray = false){
		//先处理子节点
		$subNodes = array();
		$sub_levels = array();
		if(!empty($levels[$currentLevel]['sub'])){
			$sub_levels = explode(',', $levels[$currentLevel]['sub']);
			foreach($sub_levels as $sub_level){
				if(isset($tree[$sub_level][$id]))
					$subNodes[$sub_level] = $tree[$sub_level][$id];
			}
		}
		$sub_lines = array();
		$sub_diff = false;
		$i = 0;
		if(!empty($subNodes)){
			foreach($subNodes as $type=>$subNode){
				$sub_lines[$i] = "<tr>".$this->staffTD($type."(s)", $lc, $deep, true, $toArray)."</tr>";
				$rem_line = $i ++;
				//应该将subnode放到一个Div里，便于收缩展开管理
				// $sub_lines[$i ++ ] = "<tr><td><table>";
				foreach($subNode as $sub_id=>$n){
					$sub_lines[$i ++] = $this->genLevel($sub_id, $n, $levels, $style, $header, $lc, $type, $deep + 1, $tree, $sub_diff, $toArray);
				}
				$class = 'ui-widget-content jqgrow ui-row-ltr';
				if ($sub_diff)
					$class .= ' hilight';
				else
					$class .= ' thesame';
				$sub_lines[$rem_line] =  "<tr class='$class'>".$this->staffTD($type."(s)", $lc, $deep, true, $toArray)."</tr>";
				// $sub_lines[$i ++ ] = "</table></td></tr>";
			}
		}
		
		//再处理自身属性
		$node_name = $currentLevel;
		$getName = false;
		$ignore = isset($levels[$currentLevel]['ignore']) ? $levels[$currentLevel]['ignore'] : array();
		$lines = array();
		foreach($node as $p=>$v){
			if(in_array($p, $ignore))
				continue;
			$orig = null;
			$p_diff = false;
			$line = '';
			foreach($header as $k=>$name){
				$value = isset($v[$k]) ? $v[$k] : '';
				if(is_null($orig))
					$orig = $value;
				else{
					if($orig != $value)
						$p_diff = true;
				}
				if($p == 'name'){
					if(!$getName && !empty($value)){
						$node_name .= '#'.$value;
						$getName = true;
					}
				}
				$line .= "<td>$value</td>";
			}
			$class = 'ui-widget-content jqgrow ui-row-ltr';
			if ($p_diff)
				$class .= ' hilight';
			else
				$class .= ' thesame';
			$lines[] = "<tr class='$class'>".$this->staffTD($currentLevel, $lc, $deep, false, $toArray)."<td>$p</td>$line</tr>";
		}
		$class = 'ui-widget-content jqgrow ui-row-ltr';
		if($p_diff || $sub_diff)
			$diff = true;
// if($diff)		
// print_r(">>node_name = $node_name, diff = $diff<<");		
		if($p_diff || $sub_diff)
			$class .= ' hilight';
		else
			$class .= ' thesame';
		$str = '';
		if($currentLevel != 'root')
			$str .= "<tr class='$class'>".$this->staffTD($node_name, $lc, $deep, true, $toArray)."</tr>";
		// array_($str, $lines, $sub_lines);
		
		$str .= implode('', $lines).
			implode('', $sub_lines); 
		return $str;
	}
	
	function treeHeader($lc, $header, $style){
		$class = 'ui-widget-content jqgrow ui-row-ltr';
		$colspan = $lc + 2;
		$str = "<tr class='ui-jqgrid-htable'>".
			"<th colspan='$colspan'><input type='checkbox' name='op' id='hide_same' value='hide' class='cbox'><label for='hide_same'>Hide the same</label></th>";
		foreach($header as $id=>$name){
			$str .= "<th class='$class'>$name</th>";
		}
		$str .= "</tr>";
		return $str;
	}
	
	function staffTD($node_name, $lc, $deep, $summary = false, $toArray = false){
		$class = 'ui-widget-content jqgrow ui-row-ltr';
	
		$str = '';
			
		for($i = 0; $i < $deep; $i ++){
			$str .= "<td></td>";
		}
		if($summary){
			$str .= "<td>-</td><td>$node_name</td>";
		}
		else{
			$str .= "<td /><td />";
		}
		for($i = $deep; $i < $lc - 1; $i ++)
			$str .= "<td />";
		return $str;
	}
	
	function showDiff($fields, $vs, $field_options = array()){
//print_r($fields);		
		print_r("<table class='alt_table_border' style='width:100%'>");
		print_r("<tr class='ui-jqgrid-htable'>");
		print_r("<th><input type='checkbox' name='op' id='hide_same' value='hide' class='cbox'><label for='hide_same'>Hide the same</label></th>");
		foreach($vs as $k=>$v){
			print_r("<th>$k</th>");
		}
		print_r("</tr>");

		$i = 0;
		foreach($fields as $field=>$label){
			if(is_int($field)) {
				$field = $label;
			}
			if(!empty($field_options) && empty($field_options[$field]))
				continue;
				
			$option = isset($field_options[$field]) ? $field_options[$field] : array();
			
			if(isset($option['label']))
				$label = $option['label'];
			
			$lastV = null;
			$start = false;
			$same = true;
			$str = '';
			foreach($vs as $k=>$v){
				if (!isset($v[$field]))
					$v[$field] = '';
				else{
					if(is_array($v[$field])){
						$str .= "<table>";
						foreach($v as $v_k=>$v_v){
							if (!$start){
								$lastV = array($v_k=>$v_v);
							}
							else{
								if (!isset($lastV[$v_k]) || $lastV[$v_k] != $v_v)
									$same = false;
								$lastV[$v_k] = $v_v;
							}
							$class = 'ui-widget-content jqgrow ui-row-ltr';
							if (!$same)
								$class .= ' hilight';
							else
								$class .= ' thesame';
							if ($i ++ % 2)
								$class .= ' ui-priority-secondary td_grey';
							$str .= "<tr><td>$v_k</td><td class='$class'>$v_v</td></tr>";
						}
						$start = true;
						$str .= "</table>";
					}
					else{
					// check if the value the same
						if (!$start){
							$lastV = $v[$field];
							$start = true;
						}
						else{
							if ($lastV != $v[$field])
								$same = false;
							$lastV = $v[$field];
						}
						//可能需要转换
						$value = $v[$field];
						if(!empty($option))
							$value = $this->translate($v[$field], $option);
						
						$str .= "<td name='{$k}'>".nl2br($value)."</td>";
					}
				}
			}
			$class = 'ui-widget-content jqgrow ui-row-ltr';
			if (!$same)
				$class .= ' hilight';
			else
				$class .= ' thesame';
			if ($i ++ % 2)
				$class .= ' ui-priority-secondary td_grey';
			print_r("<tr class='$class'>");
			print_r("<td name='{$field}'>$label</td>");
			print_r($str);
			print_r("</tr>");
		}
		print_r("</table>");
	}
	
	function translate($v, $option){
// print_r($option);	
		$value = $v;
		if(in_array($option['edittype'], array('select', 'ids'))){
			$value = array();
			if(is_string($v)){
				$v = explode(',', $v);
			}
			elseif(is_int($v))
				$v = array($v);
			foreach($v as $item){
				if(empty($item))
					continue;
				if(!empty($option['editoptions']['value'][$item])){
					$e = $option['editoptions']['value'][$item];
					if(is_array($e)){
						$name_field = $this->getDisplayField($e);
						$value[] = $e[$name_field];
					}
					else
						$value[] = $e;
				}
				else{
					$value[] = $item;
				}
			}
// print_r($value);			
			$value = implode(',', $value);
		}
		return $value;
	}
}

?>
