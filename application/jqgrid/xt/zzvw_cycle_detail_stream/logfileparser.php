<?php
require_once('common.php');

function availableFileName($fileName, $ext = '.log'){
    $base = dirname($fileName).'/'.basename($fileName, $ext);
    $i = 1;
    $ret = $base;    
    do{
        $ret = $base.'_'.$i++;
    }while(file_exists($ret.$ext));
    return $ret;
}

function parseRule($rule){
    $ret = array();
    $pat = "/>>>\((.*?)@(.*)\)(.*)<<</";
    if (preg_match_all($pat, $rule, $matches)){
//print_r($matches);    
        foreach($matches[1] as $i=>$soc){
            $socs = explode('&', $soc);
            $os = $matches[2][$i];
            $exes = explode(';', $matches[3][$i]);
            foreach($exes as $j=>$v){
                if(empty($v))
                    break;
                $result = explode(':', $v);
                $result[0] = strtoupper($result[0]);
                $ids = explode(',', $result[1]);
                foreach($socs as $e){
                    foreach($ids as $id){
                        if (preg_match("/(\d*)-(\d*)/", $id, $mat)){
                            for($n = $mat[1]; $n <= $mat[2]; $n ++){
                                $ret[$e][$os][$result[0]][] = $n;
                            }
                        }
                        else
                            $ret[$e][$os][$result[0]][] = (int)$id;
                    }
                }    
            }
        }
    }
    return $ret;
}

class logFileParser{
//    var $db;
//    var $cycleInfo;
    var $handle;
    var $params;
    var $startTime, $endTime;
    var $getdetail_idField;
    var $updateResultField;
    var $updateResultFieldValue;
    function __construct($handle, $params = array()){
        $this->handle = $handle;
        $this->params = $params;
        $this->getdetail_idField = "testcaseid";
        $this->updateResultField = "finished";
		$this->updateResultFieldValue = false;
//print_r($this->params);
        if (empty($this->params['cycleInfo']) || empty($this->params['db']))
            die("Invalid cycle info or db");
    }
    
    function parse(){
        rewind($this->handle);
        $this->startTime = microtime(true);
        $result = $this->_parse();
//print_r($result);        
        if (!$result['code']){
            foreach($result['test_result'] as $v){
                $detail_id = $this->updateTestResult($v);
                //updateLastResult($this->params['db'], $result['result'], $detail_id);
            }
        }
        $this->endTime = microtime(true);
        return array('start'=>$this->startTime, 'end'=>$this->endTime, 'result'=>$result);
    }
    
    function _parse(){
        $ret = array('code'=>2, 'msg'=>"Invalid log file");
        return $ret;
    }
    
    function translateResult($result){
        $ret = $result;
        if (is_string($result)){
            $sql = "select id FROM result_type_id WHERE name=".$this->params['db']->AddSingleQuotes($result);
            $result = $this->params['db']->Query($sql);
            if ($row = $result->fetchRow())
                $ret = $row['id'];
            else
                $ret = 0;
        }
        return $ret;
    }
    
    function getDetailId($result){
    	$detailId = 0;
    	$sql = "SELECT detail.id FROM cycle_detail detail left join testcase tc on detail.testcase_id=tc.id ".
			" WHERE detail.cycle_id=".$this->params['cycleInfo']['id'].
			" and trim(tc.".$this->getDetailIdField.")=".$this->param['db']->AddSingleQuotes(trim($result['name'])).
			" AND (IsNull(detail.result_type_id) OR detail.result_type_id in (0,1))";
    	$res = $this->params['db']->query($sql);
    	if ($row = $res->fetch())
    		$detailId = $row['id'];
    	return $detailId;
	}
	
    function updateTestResult($result){
    	$detail_id = 0;
        if ($result[$this->updateResultField] != $this->updateResultFieldValue){
        	// get the id 
        	$detail_id = $this->getDetailId($result);
        	if (!empty($detail_id)){
	            $result['result'] = $this->translateResult($result['result']);
	            if (!empty($result['result'])){
					$sql = "UPDATE cycle_detail SET cycle_detail.result_type_id=".$result['result'].
		                ", cycle_detail.finish_time=NOW()";
		            if (isset($result['comment']))
		                $sql .= ", cycle_detail.comment=CONCAT_WS('', comment, '".$this->params['db']->Escape($result['comment'])."')";
		            $sql .= " WHERE cycle_detail.id=$detail_id";
	/*            
				$sql = "UPDATE cycle_detail LEFT JOIN tms_tc_testcase ON ".
	                " cycle_detail.testcaseid=tms_tc_testcase.id".
	                " SET cycle_detail.result_type_id=".$result['result'].
	                ", cycle_detail.finish_time=NOW()";
	            if (isset($result['comment']))
	                $sql .= ", cycle_detail.comment=CONCAT_WS('', comment, '".$this->params['db']->Escape($result['comment'])."')";
	            $sql .= " WHERE cycle_detail.summaryid=".$this->params['cycleInfo']['id'].
	                " AND trim(tms_tc_testcase.testcaseid)=".$this->params['db']->AddSingleQuotes(trim($result['name'])).
	                " AND (IsNull(cycle_detail.result_type_id) OR cycle_detail.result_type_id in (0,1))";
	*/
	            	$this->params['db']->Execute($sql);
	            	updateLastResult($this->params['db'], $result['result'], $detail_id);
	            }
	        }
        }
        return $detail_id;
    }

    function getTag($pattern, $lines = 3){
        $buffer = '';
        $i = 0;
        $matches = array();
        $handledBuffer = '';
        while(!feof($this->handle)){
            $line = fgets($this->handle);
            $i ++;
            if ($i == 1){
                $fpos = ftell($this->handle);
                $handledBuffer = $line;
            }
            if (empty($line))
                continue;
            $buffer .= $line;
            if (preg_match($pattern, $buffer, $matches)){
                $handledBuffer = $buffer;
                break;
            }
            if ($i >= $lines){
                fseek($this->handle, $fpos);
                break;
            }
        }
        return compact('matches', 'handledBuffer');
    }
};

class CTSLogFileParser extends logFileParser{
    var $cts_test_count;
    var $depth;
    var $cts_level;
    var $cts_case;
    var $cts_test_result;
    
    var $cts_level_name;
    var $cts_cycle;
    var $cts_cycle_map;

    var $currentStackTrace;
    var $caseTime;
    var $testResultTime;
    var $insertTime;
    
    function __construct($handle, $params = array()){
        parent::__construct($handle, $params);
        $this->updateResultField = 'result';
        $this->updateResultFieldValue = 'notExecuted';
        if (empty($this->params['commandLine_prefix']))
            $this->params['commandLine_prefix'] = '';
        if (empty($this->params['commandLine_plan']))
            $this->params['commandLine_plan'] = 'CTS';
        $this->cts_test_count = 0;
        $this->depth = array();
        $this->cts_level = array();
        $this->cts_case = array();
        $this->cts_test_result = array();
        
        $this->cts_level_name = array('TESTSUITE', 'TESTCASE', 'TEST');
        $this->cts_cycle = array();
        $this->cts_cycle_map = array(
            'TESTRESULT'=>array(
                'plan_name'=>'TESTPLAN',
                'start_time'=>'STARTTIME',
                'end_time'=>'ENDTIME',
            ), 
            'TESTRESULT.DEVICEINFO.BUILDINFO'=>array(
                'build_model'=>'BUILD_MODEL',
                'build_name'=>'BUILDNAME',
                'device_id'=>'DEVICEID',
                'firmware_version'=>'BUILDVERSION',
                'firmware_build_number'=>'BUILDID',
                'build_fingerprint'=>'BUILD_FINGERPRINT',
                'android_platform_version'=>'ANDROIDPLATFORMVERSION',
                'supported_locales'=>'LOCALES',
                'x_dpi'=>'XDPI',
                'y_dpi'=>'YDPI',
                'touch'=>'TOUCH',
                'navigation'=>'NAVIGATION',
                'keypad'=>'KEYPAD',
                'network'=>'NETWORK',
                'imei'=>'IMEI',
                'imsi'=>'IMSI',
            ), 
            'TESTRESULT.DEVICEINFO.SCREEN'=>array(
                'screen_size'=>'RESOLUTION',
            ), 
            'TESTRESULT.DEVICEINFO.PHONESUBINFO'=>array(
                'phone_number'=>'SUBSCRIBERID',
            ), 
            'TESTRESULT.SUMMARY'=>array(
                'tests_passed'=>'PASS',
                'tests_failed'=>'FAILED',
                'tests_timeout'=>'TIMEOUT',
                'tests_not_executed'=>'NOTEXECUTED',
            )
        );
    }
    
    function startElement($parser, $name, $attrs){
        $this->currentStackTrace = false;
        $this->depth[] = $name;
        $fullType = implode('.', $this->depth);
        if (in_array($fullType, array_keys($this->cts_cycle_map))){
            foreach($this->cts_cycle_map[$fullType] as $key=>$v){
                if ($key == 'start_time' || $key == 'end_time')
                    $this->cts_cycle[$key] = date('Y-m-d H:i:s', strtotime($attrs[$v]));
                else
                    $this->cts_cycle[$key] = $attrs[$v];
            }
        }
        else if ($fullType == 'TESTRESULT.HOSTINFO'){
            $this->cts_cycle['host_info'] = $attrs['NAME'];
        }
        else if ($fullType == 'TESTRESULT.HOSTINFO.OS'){
            $this->cts_cycle['host_info'] .= $attrs['NAME'].'-'.$attrs['VERSION'];
        }
        else if ($fullType == 'TESTRESULT.HOSTINFO.CTS'){
            $this->cts_cycle['cts_version'] = $attrs['VERSION'];
        }
        else if ($fullType == 'TESTRESULT.HOSTINFO.CTS.INTVALUE'){
            if ($attrs['NAME'] == 'testStatusTimeoutMs')
                $this->cts_cycle['test_timeout'] = $attrs['VALUE'];
        }
        else if (in_array($name, $this->cts_level_name)){
            if ($name == 'TEST'){
                $commandLine = $this->params['commandLine_prefix'].' start --plan '.$this->params['commandLine_plan'].' -t '.
                    implode('.', $this->cts_level).'#'.$attrs['NAME'];
            }
            $this->cts_level[] = $attrs['NAME'];
            $fullName = implode('.', $this->cts_level);
            $this->cts_case[] = array('type'=>$name, 'fullName'=>$fullName);
            if ($name == 'TEST'){
                $this->cts_test_result[$this->cts_test_count] = array('cts_level'=>$fullName, 'command_line'=>$commandLine, 
                    'result'=>$attrs['RESULT'], 'comment'=>'',
                    'start_time'=>date('Y-m-d H:i:s', strtotime($attrs['STARTTIME'])), 
                    'end_time'=>date('Y-m-d H:i:s', strtotime($attrs['ENDTIME'])));
                if ($attrs['RESULT'] == 'fail'){
                    if (!empty($attrs['KNOWNFAILURE'])){
                        $this->cts_test_result[$this->cts_test_count]['comment'] = "A test that was a known failure actually passed. Please check.\r\n[".$attrs['KNOWNFAILURE']."]";
                    }
                }
            }
        }
        else if ($name == 'FAILEDSCENE'){
            $this->cts_test_result[$this->cts_test_count]['comment'] = $attrs['MESSAGE'];
            if (isset($attrs['STACKTRACE']))
                $this->cts_test_result[$this->cts_test_count]['comment'] .= $attrs['STACKTRACE'];
        }
        else if ($name == 'STACKTRACE'){
//print_r("\nIt is in stacktrace\n");        
            $this->currentStackTrace = true;
        }
    }
    
    function endElement($parser, $name){
//        global $depth, $cts_level_name, $cts_level;
        array_pop($this->depth);
        if (in_array($name, $this->cts_level_name))
            array_pop($this->cts_level);
        if (strtolower($name) == 'test')
            $this->cts_test_count ++;
        $this->currentStackTrace = false;
    }
    
    function handleData($parser, $data){
//print_r("data = $data\n");    
        if ($this->currentStackTrace){
            $this->cts_test_result[$this->cts_test_count]['comment'] .= $data;
//            $this->currentStackTrace = false;
        }
//        else
//            $this->cts_test_result[$this->cts_test_count]['message'] .= $data;
    }

    function _parse(){
        $xml_parser = xml_parser_create();
        xml_set_element_handler($xml_parser, array('CTSLogFileParser', "startElement"), array('CTSLogFileParser', "endElement"));
        xml_set_character_data_handler($xml_parser, array('CTSLogFileParser', "handleData"));
        xml_set_default_handler($xml_parser, array('CTSLogFileParser', "handleData"));
        $ret = array('code'=>0, 'msg'=>"Success to parse the log file");
        while ($data = fread($this->handle, 4096*2)) {
            if (!xml_parse($xml_parser, $data, feof($this->handle))) {
                $ret['code'] = 2;
                $ret['msg'] = sprintf("XML error: %s at line %d, data=$data",
                            xml_error_string(xml_get_error_code($xml_parser)),
                            xml_get_current_line_number($xml_parser));
                return json_encode($ret);
            }
        }
        xml_parser_free($xml_parser);
        $ret['cycle'] = $this->cts_cycle;
        $ret['case'] = $this->cts_case;
        $ret['test_result'] = $this->cts_test_result;
        return $ret;
    }
    
    function translateResult($result){
        $ret = 2;
        switch($result){
            case 'pass':
                $ret = 1;
                break;
            case 'timeout':
                $ret = 7;
                break;
            default:
                break;
        }
        return $ret;
    }
    
    function getDetailId($result){
    	$detail_id = 0;
        $sql = "SELECT cycle_detail.id FROM cycle_detail".
		" LEFT JOIN testcase_ver ON cycle_detail.testcase_ver_id=testcase_ver.id AND cycle_detail.testcase_id=testcase_ver.testcase_id".
        " WHERE cycle_detail.cycle_id=".$this->params['cycleInfo']['id'].
        " AND testcase_ver.command=".$this->params['db']->AddSingleQuotes($result['command_line']);
		$res = $this->params['db']->query($sql);
		if($row = $res->fetch())
			$detail_id = $row['id'];
		return $detail_id;
	}
};

class CCETKLogFileParser extends logFileParser{
    var $patterns;
    function __construct($handle, $params = array()){
        parent::__construct($handle, $params);
        $this->patterns = array(
        'start'=>"/(?<!REM )(call) .*\[(.*)\].*?(-.*?)?\.bat.*$/si", 
        'startORtestgroup'=>"/(?<!REM )(call) .*\[(.*)\].*?(-.*?)?\.bat.*|<TESTGROUP>/sm",
        'startORsubcase'=>"/(?<!REM )(call) .*\[(.*)\].*?(-.*?)?\.bat.*|<TESTCASE ID=([0-9]*)>/s",
        "startORendsubcase"=>'/(?<!REM )(call) .*\[(.*)\].*?(-.*?)?\.bat.*|<\/TESTCASE RESULT="(.*)">/s',
//        'subcase'=>"/^\s*\W\W\W TEST COMPLETED/",
//            'testname'=>"/^\s*\W{3} Test Name: *(.*)$/",
//            'testid'=>"/^\s*\W{3} Test ID: *(.*)$/",
//            'testresult'=>"/^\s*\W{3} Result: *(.*)$/",
//        'failed'=>"/^\s*\W\W\W Failed: *([0-9]*)/",
//        'skipped'=>"/^\s*\W\W\W Skipped: *([0-9]*)/",
//        'aborted'=>"/^\s*\W\W\W Aborted: *([0-9]*)/",
        'startORfinished'=>"/(?<!REM )(call) .*\[(.*)\].*?(-.*?)?\.bat.*|<\/TESTGROUP>/",
        'startORsubcaseORfinished'=>"/(?<!REM )(call) .*\[(.*)\].*?(-.*?)?\.bat.*|<TESTCASE ID=([0-9]*)>|<\/TESTGROUP>/s"
        );
    }
    
    function endCase($tagResult, &$caseStart, &$caseInfo, &$ret){
        if ($caseStart){
            $this->writeLogFile($caseInfo);
            unset($caseInfo['lines']);
            $ret['test_result'][] = $caseInfo;
        }
        $caseStart = true;
        $tagResult['matches'][2] = str_replace(array("\r", "\n"), array(''), $tagResult['matches'][2]);
        $caseInfo = array('finished'=>false, 'name'=>$tagResult['matches'][2], 'keepsection'=>'', 
            'lines'=>'', 'result'=>1);
        if (!empty($tagResult['matches'][3]))
            $caseInfo['keepsection'] = $tagResult['matches'][3];
        return 'startORtestgroup';
    }
    
    function _parse(){
        $ret = array('code'=>2, 'msg'=>"Invalid log file");
        $caseStart = false;
        $caseInfo = array('finished'=>false, 'result'=>1);
        $subcaseId = 0;
        $currentStatus = 'start';
        
        while(!feof($this->handle)){
            $tagResult = $this->getTag($this->patterns[$currentStatus]);
            if (!empty($tagResult['matches'])){
//print_r("currentStatus = $currentStatus\n");
//print_r($tagResult['handledBuffer']);            
//print_r("\n\nmatched\n\n");
                switch($currentStatus){
                    case 'start':
                        $currentStatus = $this->endCase($tagResult, $caseStart, $caseInfo, $ret);
                        break;
                    case 'startORtestgroup':
                        if (count($tagResult['matches']) >= 3){ // start tag
                            $currentStatus = $this->endCase($tagResult, $caseStart, $caseInfo, $ret);
                        }
                        else{
                            $caseInfo['testgroup'] = true;
                            $currentStatus = 'startORsubcase';
                        }
                        break;
                    case 'startORsubcase':
//print_r($tagResult['matches']);                    
                        if (count($tagResult['matches']) == 5){ // subcase
                            $isSubCase = true;
                            $subcaseId = $tagResult['matches'][4];
                            $currentStatus = 'startORendsubcase';
                        }
                        else{ // start tag
                            $currentStatus = $this->endCase($tagResult, $caseStart, $caseInfo, $ret);
                        }
                        break;
                    case 'startORendsubcase':
//print_r($tagResult['matches']);                    
                        if (count($tagResult['matches']) == 5){ // endsubcase
                            $caseInfo['subcase'][strtoupper($tagResult['matches'][4])][] = $subcaseId;
                            $currentStatus = 'startORsubcaseORfinished';
                        }
                        else{ // start tag
                            $currentStatus = $this->endCase($tagResult, $caseStart, $caseInfo, $ret);
                        }
                        break;
                    case 'startORsubcaseORfinished':
//print_r($tagResult['matches']);         
                        if (count($tagResult['matches']) == 5){ //subcase
                            $isSubCase = true;
                            $subcaseId = $tagResult['matches'][4];
                            $currentStatus = 'startORendsubcase';
                        }
                        else if (count($tagResult['matches']) >= 3){ // start tag
                            $currentStatus = $this->endCase($tagResult, $caseStart, $caseInfo, $ret);
                        }
                        else{
                            $caseInfo['finished'] = true;
                            $currentStatus = 'start';
                        }
                        break;
                    case 'startORfinished':
//print_r($tagResult['matches']);                    
                        if (count($tagResult['matches']) >= 3){ // start tag
                            $currentStatus = $this->endCase($tagResult, $caseStart, $caseInfo, $ret);
                        }
                        else{
                            $caseInfo['finished'] = true;
                            $currentStatus = 'start';
                        }
                        break;
                }    
//print_r("currentStatus = $currentStatus\n");
                    
            }
            if($caseStart)
                $caseInfo['lines'] .= $tagResult['handledBuffer'];
//            if ($caseInfo['finished'])
//                $caseStart = false;
        }
        if ($caseStart){
            $this->writeLogFile($caseInfo);
            unset($caseInfo['lines']);
            $ret['test_result'][] = $caseInfo;
        }
//print_r($ret['test_result']);     
        if (!empty($ret['test_result'])){   
            $ret['code'] = 0;
            $ret['msg'] = "Success to parse the log file";
        }
        return $ret;
    }

    function writeLogFile(&$caseInfo){
        $sql = "SELECT detail.id as detail_id, detail.result_type_id, ver.id as ver_id, ver.ruleid, ver.rule_content".
            " FROM cycle_detail detail left join testcase tc".
            " ON detail.testcase_id=tc.id".
            " LEFT JOIN testcase_ver ver ON detail.testcase_ver_id=ver.id". 
            " WHERE tc.testcase_id=".$this->params['db']->AddSingleQuotes($caseInfo['name'])." AND detail.cycle_id=".$this->params['cycleInfo']['id'];
        $result = $this->params['db']->query($sql);
        $detail_id = 0;
        if ($row = $result->fetch()){
            $detail_id = $row['detail_id'];
            $caseInfo['detail_id'] = $detail_id;
            $caseInfo['ver_id'] = $row['ver_id'];
            $caseInfo['rule_content'] = $row['rule_content'];
            $caseInfo['ruleid'] = $row['ruleid'];
        }
        else{
//            print_r("sql = $sql, caseName = {$caseInfo['name']}\n");    
        }
        
        $caseLogFileName = GetLogRoot().$this->params['cycleInfo']['name'].'_'.$this->params['cycleInfo']['id'].
            GetFilePathSeparate().$caseInfo['name'].'_'.$detailid.GetFilePathSeparate();
        CreateDirectory($caseLogFileName);
        $caseLogFileName .= $caseInfo['name'].'_['.$this->params['cycleInfo']['prj'].']';
        if (empty($caseInfo['finished'])){
            if (empty($caseInfo['testgroup']))
                $caseLogFileName .= '_HUNG';
            else
                $caseLogFileName .= '_UNKNOWN';
        }
        else{
            $caseInfo['result'] = 2;
            if (!empty($caseInfo['subcase']['FAILED']))
                $caseLogFileName .= '_Failed'.count($caseInfo['subcase']['FAILED']);
            if (!empty($caseInfo['subcase']['SKIPPED']))
                $caseLogFileName .= '_Skipped'.count($caseInfo['subcase']['SKIPPED']);
            if (!empty($caseInfo['abort']))
                $caseLogFileName .= '_Aborted'.count($caseInfo['subcase']['ABORTED']);
            if (empty($caseInfo['subcase']['FAILED']) && empty($caseInfo['subcase']['SKIPPED']) && empty($caseInfo['subcase']['ABORTED'])){
                $caseLogFileName .= '_Passed';
                $caseInfo['result'] = 1;
            }
        }
        $caseLogFileName .= '.log';
        if (file_exists($caseLogFileName))
            $caseLogFileName = availableFileName($caseLogFileName).'.log';
        $handle = fopen($caseLogFileName, 'w');
        fwrite($handle, $caseInfo['lines']);
        fclose($handle);
    }

    function updateTestResult($result){
        if (!empty($result['ruleid']) && $result['ruleid'] == 2 && !empty($result['rule_content'])){
            //analyze the rule content
            $rule = parseRule($result['rule_content']);
//print_r($rule);
            $matched = true;
            $soc = $this->params['cycleInfo']['wincesocname'];
            $os = $this->params['cycleInfo']['osname'];
            if (isset($rule[$soc][$os])){
                foreach($rule[$soc][$os] as $r=>$ids){
                    if(empty($result['subcase'][$r])){
                        $matched = false;
                        break;
                    }
                    $diff = array_diff($ids, $result['subcase'][$r]);
    /*
    print_r("rule[$r] =");
    print_r($ids);
    print_r("result[$r] =");
    print_r($result['subcase'][$r]);
    print_r("diff =");
    print_r($diff);
    */
                    if (!empty($diff)){
                        $matched = false;
                        break;
                    }
                }
                $result['result'] = $matched ? 1 : 2;
            }
            else{ // no suitable rule for the case
                if(count($result['subcase']['FAILED']) || count($result['subcase']['SKIPPED']))
                    $result['result'] = 2; // fail
            }
        }
        return parent::updateTestResult($result);
    }
    
};

class CCETK7LogFileParser extends CCETKLogFileParser{};

class CCodecLogFileParser extends logFileParser{
    var $pattern = array();
    function __construct($handle, $params){
        parent::__construct($handle, $params);
        $this->getDetailIdField = "name";
        $this->pattern = array(
            'case'=>'/^.*.exe ".*\\\\(.*?)"/',
            'result'=>"/^\s*Test Result : (\S*)\s$/"
        );
    }
    
    function _parse(){
        $ret = array('code'=>2, 'msg'=>"Invalid log file");
        $caseInfo = array();
        $caseStart = false;
        while(!feof($this->handle)){
            $line = fgets($this->handle);
            if (count($line) == 0)
                continue;
            if (preg_match($this->pattern['case'], $line, $matches)){
/*
                if ($caseStart){
                    $ret['test_result'][] = $caseInfo;
                }
*/
                $caseStart = true;
                $caseInfo = array('finished'=>false, 'name'=>$matches[1], 'result'=>1);
                continue;
            }
            if(!$caseStart){
                continue;            
            }
            if (preg_match($this->pattern['result'], $line, $matches)){
                $caseInfo['result'] = $matches[1];
                $caseInfo['finished'] = true;
                $ret['test_result'][] = $caseInfo;
                $caseStart = false;
            }
        }
        if ($caseStart){
            $ret['test_result'][] = $caseInfo;
        }
        $ret['code'] = 0;
        $ret['msg'] = "Success to parse the log file";
        return $ret;
    }
}


class CXlsLogFileParser extends logFileParser{
    var $pattern = array();
    function __construct($handle, $params){
        parent::__construct($handle, $params);
        $this->getDetailIdField = "name";
        $this->pattern = array(
            'start'=>"/^BitStreams\sFinal Result\s.*\tRemark/",
            'case'=>"/^.*?([^\/\\\]*?)\t(.*?)\t.*\t(.*?)$|^.*?([^\/]*?)\t(.*?)$/"
        );
    }

    function _parse(){
        $ret = array('code'=>2, 'msg'=>"Invalid log file");
        $caseInfo = array();
        $caseStart = false;
        if (!feof($this->handle)){
            $line =fgets($this->handle);
            if (count($line) == 0)
                return $handled;
            if (!preg_match($this->pattern['start'], $line, $matches)){ // Not Codec Linux Logfile
                print_r("NOT Linux Codec Log File\n");            
                return $ret;
            }
        }
        while(!feof($this->handle)){
            $line =fgets($this->handle);
            if (count($line) == 0)
                continue;
            if (preg_match($this->pattern['case'], $line, $matches)){
                $caseInfo = array('finished'=>true);
                if (count($matches) == 4){
                    $caseInfo['name'] = trim($matches[1]);
                    $caseInfo['result'] = strtoupper(trim($matches[2]));
                    $caseInfo['comment'] = trim($matches[3]);
                }else if (count($matches) == 6){
                    $caseInfo['name'] = trim($matches[4]);
                    $caseInfo['result'] = trim($matches[5]);
                    $caseInfo['comment'] = '';
                }
                $ret['test_result'][] = $caseInfo;
            }
        }
        $ret['code'] = 0;
        $ret['msg'] = "Success to parse the log file";
        return $ret;
    }
    
    function translateResult($result){
		if ($result == 'PASS')
			return 1;
		return 0;
	}
}

class aRobotLogFileParser extends logFileParser{
    var $pattern = array();
    function __construct($handle, $params){
        parent::__construct($handle, $params);
        $this->updateResultField = "result";
        $this->updateResultFieldValue = false;
        
        $this->pattern = array(
            'case'=>"/^Case\d+:\s*(.+)$/",
            'package'=>"/^Package name:\s*(.*)$/",
//            'running_sequence'=>"/^\s*-- Running sequence 1:",
            'environment'=>"/^\s*-- (Environment: .*)$/",
            'result'=>"/^\s*-- Result: (.*)$/",
            'starttime'=>"/^\s*-- (starttime: .*)$/",
            'endtime'=>"/^\s*-- (endtime: .*)$/",
        );
    }

    function _parse(){
        $ret = array('code'=>2, 'msg'=>"Invalid log file");
        $caseInfo = array('environment'=>'', 'starttime'=>'', 'endtime'=>'');
        $caseStarted = false;
        while(!feof($this->handle)){
            $line =fgets($this->handle);
            if (count($line) == 0)
                continue;
            if (preg_match($this->pattern['case'], $line, $matches)){
                if ($caseStarted){
            		$caseInfo['comment'] = $caseInfo['starttime']."\n".
                		$caseInfo['endtime']."\n".
                		$caseInfo['environment'];
                
                    $ret['test_result'][] = $caseInfo;
                }
                $caseStarted = false;
                $matches[1] = trim($matches[1]);
                if(!empty($matches[1])){
                    $caseInfo = array('environment'=>'', 'starttime'=>'', 'endtime'=>'', 'name' => trim($matches[1]));
                    $caseStarted = true;
                }
            }
            if (!$caseStarted)
                continue;
            foreach($this->pattern as $key=>$v){
                if ($key == 'case')
                    continue;
                if (preg_match($v, $line, $matches)){
                    $caseInfo[$key] = trim($matches[1]);
                    break;
                }
            }
        }
        if ($caseStarted){
            $ret['test_result'][] = $caseInfo;
        }
        $ret['code'] = 0;
        $ret['msg'] = "Success to parse the log file";
        return $ret;
    }
    
    function translateResult($result){
    	$ret = 2;
        switch(strtoupper($result)){
            case 'PASS':
                $ret = 1;
                break;
            case 'FAIL':
                $ret = 2;
                break;
            case 'NA':
                $ret = 4;
                break;
            default:
                $ret = 2;
        }
        return $ret;
	}

}

class apolloLogFileParser{
    var $params = array();
    var $patterns = array();
    var $output = NULL;//"output.txt";
    function __construct($handle, $params = array()){
        $this->params = $params;
        $this->patterns = array(
            'start'=>"/===start===/",
            'end'=>"/===end===/",
            'cpu'=>'/cpu\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)/',
            'file'=>'/^\s+file:\/\/\/(.*)/',
            'fps'=>'/^total time:.*?,Render fps:(\d*)/',
            'cpu_end'=>'/cpu\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)/',
        );
        $outputFileName = "output.txt";
        if (!empty($params['output']))
            $outputFileName = $params['output'];
        if (!$this->output = fopen($outputFileName, 'w'))
            die("Can not open the output file $outputFileName");
    }
    
    function endCase($tagResult, &$caseStart, &$caseInfo, &$ret){
        if ($caseStart){
            if ($caseInfo['finished']){
        		$start['user'] = (int)($caseInfo['cpu'][1]);
        		$start['nice'] = (int)($caseInfo['cpu'][2]);
        		$start['system'] = (int)($caseInfo['cpu'][3]);
        		$start['idle'] = (int)($caseInfo['cpu'][4]);
        		$start['iowait'] = (int)($caseInfo['cpu'][5]);
        		$start['irq'] = (int)($caseInfo['cpu'][6]);
        		$start['softirq'] = (int)($caseInfo['cpu'][7]);
        		$start['stealstolen'] = (int)($caseInfo['cpu'][8]);
        		$start['guest'] = (int)($caseInfo['cpu'][9]);
        		$start['total'] = $start['user'] + $start['nice'] + $start['system'] + $start['idle'] +
        		  $start['iowait'] + $start['irq'] + $start['softirq'] + $start['stealstolen'] + $start['guest'];
        	    $start['busy'] = $start['total'] - $start['idle'];

        		$end['user'] = (int)($caseInfo['cpu_end'][1]);
        		$end['nice'] = (int)($caseInfo['cpu_end'][2]);
        		$end['system'] = (int)($caseInfo['cpu_end'][3]);
        		$end['idle'] = (int)($caseInfo['cpu_end'][4]);
        		$end['iowait'] = (int)($caseInfo['cpu_end'][5]);
        		$end['irq'] = (int)($caseInfo['cpu_end'][6]);
        		$end['softirq'] = (int)($caseInfo['cpu_end'][7]);
        		$end['stealstolen'] = (int)($caseInfo['cpu_end'][8]);
        		$end['guest'] = (int)($caseInfo['cpu_end'][9]);
        		$end['total'] = $end['user'] + $end['nice'] + $end['system'] + $end['idle'] +
        		  $end['iowait'] + $end['irq'] + $end['softirq'] + $end['stealstolen'] + $end['guest'];
        	    $end['busy'] = $end['total'] - $end['idle'];
//print_r($start);
//print_r($end);        	    
        	    $caseInfo['cpu_busy'] = ($end['busy'] - $start['busy']) / ($end['total'] - $start['total']);
        	}
            $ret['test_result'][] = $caseInfo;
        }
        $caseStart = true;
        $caseInfo = array('finished'=>false);
        return 'cpu';
    }
    
    function parse(){
        $this->startTime = microtime(true);
        if (is_file($this->params['source'])){
            if ($this->handle = fopen($this->params['source'], 'r')){
                $result = $this->_parse();
                fclose($this->handle);
                if (!$result['code']){
                    fprintf($this->output, "Result from the logfile %s\r\n", $this->params['source']);
                    fprintf($this->output, "%80s    %8s    %3s\r\n\r\n", 'Media Stream', 'CPU Used', 'FPS');
                    foreach($result['test_result'] as $r){
                        fprintf($this->output, "%80s    %5.2f%%    %3d\r\n", $r['file'], $r['cpu_busy']*100.0, $r['fps']);
                    }
                }
            }
        }
        else if (is_dir($this->params['source'])){
            if ($handle = opendir($this->params['source'])) {
                while (false !== ($file = readdir($handle))) { 
                    if ($file != "." && $file != ".."){
                        $file = $this->params['source'].'/'.$file;
                        if($this->handle = fopen($file, 'r')){
                            $result = $this->_parse();
                            fclose($this->handle);
                            if (!$result['code']){
                                fprintf($this->output, "Result from the logfile %s\r\n", $file);
                                fprintf($this->output, "%80s    %8s    %3s\r\n\r\n", 'Media Stream', 'CPU Used', 'FPS');
                                foreach($result['test_result'] as $r){
                                    fprintf($this->output, "%80s    %5.2f%%    %3d\r\n", $r['file'], $r['cpu_busy']*100.0, $r['fps']);
                                }
                            }
                            fprintf($this->output, "\r\n");
                        }
                    }
                }
            }
            closedir($handle);
        }
        fclose($this->output);
        $this->endTime = microtime(true);
        return array('start'=>$this->startTime, 'end'=>$this->endTime, 'result'=>$result);
    }
    
    function _parse(){
        $ret = array('code'=>2, 'msg'=>"Invalid log file");
        $caseStart = false;
        $caseInfo = array('finished'=>false);
        $currentStatus = 'start';
        
        while(!feof($this->handle)){
//print_r("currentStatus = $currentStatus\n");
            $line = fgets($this->handle);
            if (empty($line))
                continue;
//print_r($line);                
//print_r($this->patterns[$currentStatus]);
            if (preg_match($this->patterns[$currentStatus], $line, $matches)){
    
    //            $tagResult = $this->getTag($this->patterns[$currentStatus]);
//    print_r($matches);
                if (!empty($matches)){
    //print_r($tagResult['handledBuffer']);            
    //print_r("\n\nmatched\n\n");
                    switch($currentStatus){
                        case 'start':
                            $currentStatus = $this->endCase($matches, $caseStart, $caseInfo, $ret);
                            break;
                        case 'cpu':
                            $caseInfo['cpu'] = $matches;
                            $currentStatus = 'file';
                            break;
                        case 'file':
    //print_r($tagResult['matches']);                    
                            $caseInfo['file'] = $matches[1];
                            $currentStatus = 'fps';
                            break;
                        case 'fps':
    //print_r($tagResult['matches']);                    
                            $caseInfo['fps'] = $matches[1];
                            $currentStatus = 'cpu_end';
                            break;
                        case 'cpu_end':
                            $caseInfo['cpu_end'] = $matches;
                            $caseInfo['finished'] = true;
                            $currentStatus = 'start';
                            break;
    //                    case 'end':
    //                        $currentStatus = $this->endCase($tagResult, $caseStart, $caseInfo, $ret);
    //                        break;
                    }
                 }    
            }
        }
        if ($caseStart)
            $this->endCase($matches, $caseStart, $caseInfo, $ret);
//print_r($ret['test_result']);     
        if (!empty($ret['test_result'])){   
            $ret['code'] = 0;
            $ret['msg'] = "Success to parse the log file";
        }
        return $ret;
    
    }
}
/*
require_once('db.php');
require_once('common.php');

$db = new Dbop();
GetCycleInfo(2192, $cycleInfo);
print_r($cycleInfo);
$fileName = "D:/profiles/b19268/desktop/log/testResult.xml";

$fileName = "D:/profiles/b19268/desktop/log/RTMTrail.log";
$fileName = "D:/profiles/b19268/desktop/log/report.arobot";
$fileName = "D:/profiles/b19268/desktop/log/1080off2.log";

if (!($fp = fopen($fileName, "r"))) {
    $ret['code'] = 1;
    $ret['msg'] = 'FAIL TO OPEN '.$fileName;
    return json_encode($ret);
}

$parser = new CTSLogFileParser($fp, array('cycleInfo'=>$cycleInfo, 'db'=>$db, 'commandLine_prefix'=>'./startcts', 'commandLine_plan'=>'FSLTests'));
//$parser = new apolloLogFileParser(NULL, array('cycleInfo'=>$cycleInfo, 'db'=>$db, 'source'=>'D:/profiles/b19268/desktop/log/cpu', 'output'=>'aaa.txt'));
print_r("Start to parse...\n");
$result = $parser->parse();
print_r("Finished to parse\n");
print_r($result);
return;
*/
?>
