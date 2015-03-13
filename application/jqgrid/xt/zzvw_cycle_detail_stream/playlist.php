<?php
class cycle_playlist{
	private $infoArray = array();
    private $db;
	private $tkArray;
    private $tkArrayNum = 0;
    private $android_keyword_cmd_map = array(
        'open'=>'OpenMedia',
        'play'=>'StartPlayback',
        'waitPlayEnd'=>'waitPlayEnd',
        'pause'=>'PausePlayback',
        'resume'=>'StartPlayback',
        'stop'=>'StopPlayback',
        'release'=>'Release',
        'seek'=>'SeekToPercentage',
    );
	public function __construct($dbName){
		$resource = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getResource('multidb');
		try{
			$this->db = $resource->getDb($dbName);
		}catch(Exception $e){
			// $this->db = $this->tool->getDb($dbName);
		}
		$this->db ->setFetchMode(Zend_Db::FETCH_ASSOC);
	}

	
	public function genCmdFileByDetailid($params){
		//分为是否有codec_stream_id两种
		$ret = '';
		$c_element = '';
		$element = '';
		$params['element'] = json_decode($params['element']);
		$params['c_f'] = json_decode($params['c_f']);
 		foreach($params['element'] as $k=>$v){
			if($params['c_f'][$k] == 1){
				$c_element[] = $v;
			}
			else{
				$element[] = $v;
			}
		}
		if(!empty($c_element)){
			// //??????????????
			// if ($row['testcase_type_id'] == 2){ // codec
				// if ($row['testcase_module_id'] != $lastModuleId && $lastModuleId != -1){
					// $str .= "\n";
				// }
				// $lastModuleId = $row['testcase_module_id']; 
				// $str .= "{$row['code']}\n";
				// $changeResLink = str_replace("\\", "/", $row['resource_link']);
				// $str .= "$prePath$changeResLink\n";
			// }
		}
		else{
			if(!empty($element)){
				$cycle = '';
				if(isset($element[0])){
					$res = $this->db->query("SELECT cycle.* FROM cycle_detail detail LEFT JOIN cycle ON detail.cycle_id=cycle.id WHERE detail.id=".$element[0]);
					$cycle = $res->fetch();
				}
				$rename = $cycle['name'].($params['isXML']?'_xml':'').'_cmd';
				$realFileName = EXPORT_ROOT.$rename.'_'.rand();
				$download = array("rename"=>$rename, "filename"=>$realFileName, "remove"=>1);

				$prePath = "FAKEPATH";
				$elementlist = implode(",", $element);
				$sql = "SELECT tc.testcase_module_id as testcase_module_id, module.name as testcase_module, tc.code as code, tc.id as testcase_id,". 
					" tc.testcase_type_id as testcase_type_id, ver.command as cmd, ver.resource_link as resource_link".
					" FROM cycle_detail detail LEFT JOIN testcase_ver ver ON detail.testcase_ver_id=ver.id".
					" LEFT JOIN testcase tc ON tc.id=ver.testcase_id LEFT JOIN testcase_module module ON tc.testcase_module_id=module.id".
					" WHERE detail.id in (".$elementlist.") ORDER BY tc.testcase_module_id";
				$res = $this->db->query($sql);
				$cnt = 0;
				$str = '';
				while($row = $res->fetch()){
					if ($params['isXML']){
						if ($row['testcase_type_id'] == 1){ // linux BSP
							$str .= $row["testcase_id"] . " " . $row["cmd"] . "\n";
						}
						else if ($row['testcase_type_id'] == 4){ // android application
							$str .= $row['cmd']."\n";
						}
					}
					else{ //XML
						$str .=   "\t<testcase>\n".
								  "\t\t<title>".trim($row['code'])."</title>\n".
								  "\t\t<module>".$row['testcase_module']."</module>\n".
								  "\t\t<cmdline>".$row['cmd']."</cmdline>\n".
								  "\t\t<location>".$row['resource_link']."</location>\n".
								  "\t</testcase>\n";
					}
				}
				if ($str != ''){
					if ($isXML)
						$str = "<playlist>\n".$str."</playlist>";
					$handle = fopen($realFileName, 'wb');
					if ($handle){
						if (fwrite($handle, $str)){
							fclose($handle);
							$ret = json_encode($download);
						}
					}
				}
			}
		}
		return $ret;
	}
	
	public function genCmd2File($params){
		$res = $this->db->query("SELECT *, prj.os_id as os_id FROM cycle LEFT JOIN prj ON prj.id=cycle.prj_id WHERE cycle.id=".$params['cycle_id']);
		$cycleInfo =  $res->fetch();
		$caseType = 1;
		switch($cycleInfo['group_id']){
			case 1:
				$caseType = 1;
				break;
			case 2:
				$caseType = 3;
				break;
			case 3:
				$caseType = 2;
				break;
		}
		// $sql = "SELECT DISTINCT testcase_id as element FROM cycle_detail WHERE id in (".implode(",", $params['element']).")";
		if($cycleInfo['group_id'] == 3)//codec
			$sql = "SELECT DISTINCT codec_stream_id as element FROM cycle_detail WHERE id in (".implode(",", json_decode($params['element'])).")";
		$res = $this->db->query($sql);
		$element_list = array();
		while($row = $res->fetch())
			$element_list[] = $row['element'];      
        $data = array(
            'db'=>$this->db,
            'element_list'=>implode(",", $element_list),
            'hideprj'=>$cycleInfo['prj_id'],
			'hideos'=>$cycleInfo['os_id'],
            'g_autolevel'=>array(1=>"Auto", 2=>"Manual", 3=>"Partially auto"),
            'casetype'=>$caseType
        );
        $api_arr = $this->playlistProcess("genxmlcmd", $data);
		if (isset($api_arr['error']))
		{
			return $api_arr['error'];
			break;
		}

		$rename = str_replace('/', '_', $cycleInfo['name']).'_xml_cmd_'.rand().'.xml';//str_replace('/', '_', $cycleInfo['name']).'_xml_cmd.xml';
		$realFileName = EXPORT_ROOT.'\\'.$rename;//.'_'.rand();
		$download = array("rename"=>$rename, "filename"=>$realFileName, "remove"=>1);

		$handle = fopen($realFileName, 'wb');
		if ($handle){
			if (fwrite($handle, $api_arr['content'])){
				fclose($handle);
				$ret = json_encode($download);
			}
		}
		return $ret;
	}
	
	function playlistProcess($listtype, $listpara){
		$str = array();
		switch ($listtype){
			case 'genxmlcmd':
				// parse para
				$db = isset($listpara['db']) ? $listpara['db'] : null;
				$element_list = isset($listpara['element_list']) ? $listpara['element_list'] : null;
				$hideprj = isset($listpara['hideprj']) ? $listpara['hideprj'] : null;
				$hideos = isset($listpara['hideos']) ? $listpara['hideos'] : null;
				$g_autolevel = isset($listpara['g_autolevel']) ? $listpara['g_autolevel'] : null;
				$casetype = isset($listpara['casetype']) ? $listpara['casetype'] : null;
				if ((!isset($element_list)) || (!isset($hideprj)) || (!isset($g_autolevel)) || (!isset($casetype))){
					$str['error'] = 'incomplete para';
					return $str;
				}
				switch ($casetype){
					case 1: // Linux BSP
						$str['error'] = 'Codec case only.';
						return $str;
						break;
					case 2: // CODEC
						$info = $this->getXMLInfoForCodec($element_list);
						ob_start();
						$this->render($info, 100, $hideos);
						$str['content'] = ob_get_contents();
						ob_end_clean();
						return $str;
						break;
					case 3: // WinCE BSP
						$str['error'] = 'Codec case only.';
						return $str;
						break;
					case 4: // Android Application
						$str['error'] = 'Codec case only.';
						return $str;
						break;
					default:
						$str['error'] = 'Unknown case type, please contact with maintainer.';
						return $str;
						break;
				}
					break;
			default:
				return $str;
				break;
		}
	}

    public function getXMLInfoForCodec($element_list){
        $infoArray = array();
		$sql = "SELECT stream.id as id, stream.name as res_name, container.name AS res_container, v4cc.name AS res_v4cc, a_codec.name AS res_a_codec,
			stream.v_width AS res_v_width, stream.v_height AS res_v_height, stream.v_framerate AS res_v_framerate,
			stream.v_bitrate AS res_v_bitrate, stream.v_track AS res_v_track, stream.a_samplerate AS res_a_samplerate, 
			stream.a_bitrate AS res_a_bitrate, stream.a_channel AS res_a_channel, stream.a_track AS res_a_track,
			stream.subtitle AS res_subtitle, stream.duration AS res_duration, stream.location as res_location,
			priority.name AS res_priority, stream.chromasubsampling AS res_chromasubsampling, type.name as res_type
			FROM codec_stream stream
			LEFT JOIN codec_stream_a_codec a_codec ON stream.codec_stream_a_codec_id = a_codec.id
			LEFT JOIN codec_stream_container container ON stream.codec_stream_container_id = container.id
			LEFT JOIN codec_stream_v4cc v4cc ON stream.codec_stream_v4cc_id = v4cc.id
			LEFT JOIN codec_stream_type type ON stream.codec_stream_type_id = type.id
			LEFT JOIN testcase_priority priority ON priority.id = stream.testcase_priority_id
			WHERE stream.id IN ($element_list)";
		$res = $this->db->query($sql);
		while ($row = $res->fetch()){
			$infoArray[$row['id']] = array('caseid'=>'', 'title'=>$row['res_name'], 'type'=>$row['res_type'], 
				'modulename'=>'', 'srsname'=>'', 'tpname'=>'', 'category'=>'', 'priority'=>$row['res_priority'], 'source'=>'', 
				'autolevel'=>'', 'objective'=>'', 'environment'=>'', 'steps'=>'', 'expected'=>'', 'cmdline'=>'', 
				'location'=>$row['res_location'], 'res_container'=>$row['res_container'], 'res_v4cc'=>$row['res_v4cc'], 
				'res_v_width'=>$row['res_v_width'], 'res_v_height'=>$row['res_v_height'], 'res_v_framerate'=>$row['res_v_framerate'],
				'res_v_bitrate'=>$row['res_v_bitrate'], 'res_v_track'=>$row['res_v_track'], 'res_a_codec'=>$row['res_a_codec'], 
				'res_a_samplerate'=>$row['res_a_samplerate'], 'res_a_bitrate'=>$row['res_a_bitrate'], 'res_a_channel'=>$row['res_a_channel'], 
				'res_a_track'=>$row['res_a_track'], 'res_chromasubsampling'=>$row['res_chromasubsampling'], 'res_subtitle'=>$row['res_subtitle'], 
				'res_duration'=>$row['res_duration']);
		}
        return $infoArray;
    }

    private function genTestConfig($os){
        // may different parameter even for one trickmode for different os, so could not use one template to unique the interface
        switch ($os){
			case 1: // Linux
				echo '  <testconfig desc="default config">'."\n";

				// generate play trickmode
				$this->tkArray = array(
					array('action'=>'play', 'after'=>'to_end'),
					array('action'=>'release', 'after'=>'0')
				);
				$this->tkArrayNum = count($this->tkArray);
				$this->genTmList(1, $os, "play");

				// generate pause trickmode
				$this->tkArray = array(
					array('action'=>'play', 'after'=>'10'),
					array('action'=>'pause', 'after'=>'1'),
					array('action'=>'resume', 'after'=>'2'),
					array('action'=>'pause', 'after'=>'5'),
					array('action'=>'resume', 'after'=>'2'),
					array('action'=>'stop', 'after'=>'2'),
					array('action'=>'release', 'after'=>'1')
				);
				$this->tkArrayNum = count($this->tkArray);
				$this->genTmList(1, $os, "pause");

				// generate seek trickmode
				$this->tkArray = array(
					array('action'=>'play', 'after'=>'10'),
					array('action'=>'seek', 'pos'=>'0.2', 'after'=>'2'),
					array('action'=>'seek', 'pos'=>'0.1', 'after'=>'2'),
					array('action'=>'seek', 'pos'=>'0.5', 'after'=>'2'),
					array('action'=>'seek', 'pos'=>'0.8', 'after'=>'2'),
					array('action'=>'seek', 'pos'=>'0.98', 'after'=>'to_end'),
					array('action'=>'release', 'after'=>'0')
				);
				$this->tkArrayNum = count($this->tkArray);
				$this->genTmList(1, $os, "seek");

				// generate fullscreen trickmode
				$this->tkArray = array(
					array('action'=>'play', 'after'=>'5'),
					array('action'=>'fullscreen', 'after'=>'10'),
					array('action'=>'restore', 'after'=>'3'),
					array('action'=>'fullscreen', 'after'=>'10'),
					array('action'=>'restore', 'after'=>'3'),
					array('action'=>'stop', 'after'=>'2'),
					array('action'=>'release', 'after'=>'1')
				);
				$this->tkArrayNum = count($this->tkArray);
				$this->genTmList(1, $os, "fullscreen");

				// generate thumbnail trickmode
				$this->tkArray = array(
					array('action'=>'thumbnail')
				);
				$this->tkArrayNum = count($this->tkArray);
				$this->genTmList(1, $os, "thumbnail");

				// generate metadata trickmode
				$this->tkArray = array(
					array('action'=>'play', 'after'=>'10'),
					array('action'=>'metadata', 'after'=>'0')
				);
				$this->tkArrayNum = count($this->tkArray);
				$this->genTmList(1, $os, "metadata");

				// generate misc trickmode
				$this->tkArray = array(
					array('action'=>'play', 'after'=>'10'),
					array('action'=>'seek', 'pos'=>'0.2', 'after'=>'2'),
					array('action'=>'pause', 'after'=>'1'),
					array('action'=>'seek', 'pos'=>'0.1', 'after'=>'2'),
					array('action'=>'seek', 'pos'=>'0.5', 'after'=>'2'),
					array('action'=>'resume', 'after'=>'2'),
					array('action'=>'seek', 'pos'=>'0.8', 'after'=>'2'),
					array('action'=>'seek', 'pos'=>'0.9', 'after'=>'2'),
					array('action'=>'stop', 'after'=>'2'),
				);
				$this->tkArrayNum = count($this->tkArray);
				$this->genTmList(1, $os, "misc_simple");
				$this->genTmList(0, $os, "misc_complex");

				echo "  </testconfig>\n";
				break;
			case 3: // Wince6
				break;
			case 5: // Wince7
				break;
			case 4: // Android
				/*
				echo '  <testconfig desc="default config">'."\n";

				// generate play trickmode
				$this->tkArray = array(
					array('action'=>'open', 'after'=>'1'),
					array('action'=>'play', 'after'=>'1'),
					array('action'=>'waitPlayEnd', 'after'=>'1'),
					array('action'=>'release', 'after'=>'0')
				);
				$this->tkArrayNum = count($this->tkArray);
				$this->genTmList(1, $os, "play");

				// generate pause trickmode
				$this->tkArray = array(
					array('action'=>'open', 'after'=>'1'),
					array('action'=>'play', 'after'=>'10'),
					array('action'=>'pause', 'after'=>'1'),
					array('action'=>'resume', 'after'=>'2'),
					array('action'=>'pause', 'after'=>'5'),
					array('action'=>'resume', 'after'=>'2'),
					array('action'=>'stop', 'after'=>'2'),
					array('action'=>'release', 'after'=>'1')
				);
				$this->tkArrayNum = count($this->tkArray);
				$this->genTmList(1, $os, "pause");

				// generate seek trickmode
				$this->tkArray = array(
					array('action'=>'open', 'after'=>'1'),
					array('action'=>'play', 'after'=>'10'),
					array('action'=>'seek', 'pos'=>'0.2', 'after'=>'2'),
					array('action'=>'seek', 'pos'=>'0.1', 'after'=>'2'),
					array('action'=>'seek', 'pos'=>'0.5', 'after'=>'2'),
					array('action'=>'seek', 'pos'=>'0.8', 'after'=>'2'),
					array('action'=>'seek', 'pos'=>'0.98', 'after'=>'2'),
					array('action'=>'release', 'after'=>'0')
				);
				$this->tkArrayNum = count($this->tkArray);
				$this->genTmList(1, $os, "seek");

				// generate misc trickmode
				$this->tkArray = array(
					array('action'=>'open', 'after'=>'1'),
					array('action'=>'play', 'after'=>'10'),
					array('action'=>'seek', 'pos'=>'0.2', 'after'=>'2'),
					array('action'=>'pause', 'after'=>'1'),
					array('action'=>'seek', 'pos'=>'0.1', 'after'=>'2'),
					array('action'=>'seek', 'pos'=>'0.5', 'after'=>'2'),
					array('action'=>'resume', 'after'=>'2'),
					array('action'=>'seek', 'pos'=>'0.8', 'after'=>'2'),
					array('action'=>'seek', 'pos'=>'0.9', 'after'=>'2'),
					array('action'=>'stop', 'after'=>'2'),
					array('action'=>'release', 'after'=>'2')
				);
				$this->tkArrayNum = count($this->tkArray);
				$this->genTmList(1, $os, "misc_simple");
				$this->genTmList(0, $os, "misc_complex");

				echo "  </testconfig>\n";
				 */
				break;
        }
    }

    public function render($info, $caseType, $os=0)
    {
        $numVersions = sizeof($info);
        switch ($caseType) {
        case 1: // Linux BSP
            foreach($info as $key=>$value){
                if ($value['cmd'] != "")
					echo "{$value['caseid']} {$value['cmd']}\n"; 

            }
            break;
        case 2: // Codec
            echo "<playlist>\n";
            foreach ($info as $key=>$value){
                echo "  <testcase>\n";
                echo "      <title>{$value['title']}</title>\n";
                echo "      <module>{$value['modulename']}</module>\n";
                echo "      <cmdline>{$value['cmd']}</cmdline>\n";
                echo "      <location>{$value['location']}</location>\n";
                echo "  </testcase>\n";
            }
            echo "</playlist>";
            break;
        case 3: // Wince BSP
            break;
        case 100: // Codec XML
            echo "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n";
            echo "<!--\n";
            echo "XML playlist used for Freescale codec test, based on XiaoTian test system.\n";
            switch ($os){
				case 1:
					echo "Format: Linux.\n";
					break;
				case 3:
					echo "Format: Wince.\n";
					break;
				case 5:
					echo "Format: Wince.\n";
					break;
				case 4:
					echo "Format: Android.\n";
					break;
            }
            echo "-->\n";
            echo "<playlist>\n";

            // generate testconfig
            $this->genTestConfig($os);

            // generate stream info
            foreach ($info as $key=>$value){
                echo "  <teststream id=\"". $value['caseid'] . "\">\n";
                echo "      <caseinfo>\n";
                echo "          <type>" . $value['type'] . "</type>\n";
                echo "          <module>" . $value['modulename'] . "</module>\n";
                echo "          <srs>" . $value['srsname'] . "</srs>\n";
                echo "          <testpoint>" . $value['tpname'] . "</testpoint>\n";
                echo "          <category>" . $value['category'] . "</category>\n";
                echo "          <priority>" . $value['priority'] . "</priority>\n";
                echo "          <source>" . $value['source'] . "</source>\n";
                echo "          <autolevel>" . $value['autolevel'] . "</autolevel>\n";
                echo "          <objective><![CDATA[" . $value['objective'] . "]]></objective>\n";
                echo "          <environment><![CDATA[" . $value['environment'] . "]]></environment>\n";
                echo "          <steps><![CDATA[" . $value['steps'] . "]]></steps>\n";
                echo "          <expected><![CDATA[" . $value['expected'] . "]]></expected>\n";
                echo "          <cmdline><![CDATA[" . $value['cmdline'] . "]]></cmdline>\n";
                echo "          <location><![CDATA[" . $value['location'] . "]]></location>\n";
                echo "      </caseinfo>\n";
                echo "      <streaminfo>\n";
                echo "          <clipname><![CDATA[" . $value['title'] . "]]></clipname>\n";
                echo "          <container>" . $value['res_container'] . "</container>\n";
                echo "          <video>\n";
                echo "              <v4cc>" . $value['res_v4cc'] . "</v4cc>\n";
                echo "              <v_width>" . $value['res_v_width'] . "</v_width>\n";
                echo "              <v_height>" . $value['res_v_height'] . "</v_height>\n";
                echo "              <v_framerate>" . $value['res_v_framerate'] . "</v_framerate>\n";
                echo "              <v_bitrate>" . $value['res_v_bitrate'] . "</v_bitrate>\n";
                echo "              <chromasubsampling>" . $value['res_chromasubsampling'] . "</chromasubsampling>\n";
                echo "              <v_track>" . $value['res_v_track'] . "</v_track>\n";
                echo "          </video>\n";
                echo "          <audio>\n";
                echo "              <a_codec>" . $value['res_a_codec'] . "</a_codec>\n";
                echo "              <a_samplerate>" . $value['res_a_samplerate'] . "</a_samplerate>\n";
                echo "              <a_bitrate>" . $value['res_a_bitrate'] . "</a_bitrate>\n";
                echo "              <a_channel>" . $value['res_a_channel'] . "</a_channel>\n";
                echo "              <a_track>" . $value['res_a_track'] . "</a_track>\n";
                echo "          </audio>\n";
                echo "          <duration>" . $value['res_duration'] . "</duration>\n";
                echo "          <others>\n";
                echo "              <subtitle>" . $value['res_subtitle'] . "</subtitle>\n";
                echo "          </others>\n";
                echo "      </streaminfo>\n";
                echo "  </teststream>\n";
            }
            echo "</playlist>";
            break;
        default:
            echo "Unknown error.";
            break;
        }
    }

    protected function genTmList($mode, $os, $tmLabel) // Format for trickmode, different for different os
    {
        echo "      <trickmode desc=\"$tmLabel\">\n";

        if ($mode == 0){
            switch ($os)
            {
            case 1:
                $counter = 1;
                while ($counter <= $this->tkArrayNum)
                {
                    $tmpArray = $this->genTmModeArray($counter);

                    foreach ($tmpArray as $elem)
                    {
                        $perTmLn = "            <tm";
                        foreach ($elem as $key=>$value)
                        {
                            $perTmLn .= " $key=\"$value\"";
                        }
                        $perTmLn .= ">1</tm>";
                        echo "$perTmLn\n";
                    }
                    ++$counter;
                    unset($tmpArray);
                }
                break;
            case 3:
                break;
            case 5:
                break;
            case 4:
                $counter = 1;
                while ($counter <= $this->tkArrayNum)
                {
                    $tmpArray = $this->genTmModeArray($counter);

                    foreach ($tmpArray as $elem)
                    {
                        $action = '';
                        $pos = '';
                        $after = '';
                        foreach ($elem as $key=>$value)
                        {
                            if ($key == "action")
                            {
                                $action = $value; 
                            }
                            if ($key == "pos")
                            {
                                $pos = $value * 100;
                            }
                            if ($key == "after")
                            {
                                $after = $value;
                            }
                        }
                        echo "            <operation tag=\"$action\">\n";
                        if ($pos == '')
                        {
                            echo "                  <cmd action=\"{$this->android_keyword_cmd_map[$action]}\"/>\n";
                        }
                        else
                        {
                            echo "                  <cmd action=\"{$this->android_keyword_cmd_map[$action]}\" pos=\"$pos\"/>\n";
                        }
                        echo "                  <cmd action=\"Sleep\" time=\"$after\"/>\n";
                        echo "                  <cmd action=\"GetAllErrors\"/>\n";
                        echo "                  <cmd action=\"CheckLog\"/>\n";
                        echo "            </operation>\n";
                    }
                    ++$counter;
                    unset($tmpArray);
                }
                break;
            }
        }
        else
        {
            switch ($os)
            {
            case 1:
                $tmpArray = $this->genTmModeArray($mode);

                foreach ($tmpArray as $elem)
                {
                    $perTmLn = "            <tm";
                    foreach ($elem as $key=>$value)
                    {
                        $perTmLn .= " $key=\"$value\"";
                    }
                    $perTmLn .= ">1</tm>";
                    echo "$perTmLn\n";
                }
                break;
            case 3:
                break;
            case 5:
                break;
            case 4:
                $tmpArray = $this->genTmModeArray($mode);

                foreach ($tmpArray as $elem)
                {
                    $action = '';
                    $pos = '';
                    $after = '';
                    foreach ($elem as $key=>$value)
                    {
                        if ($key == "action")
                        {
                            $action = $value; 
                        }
                        if ($key == "pos")
                        {
                            $pos = $value * 100;
                        }
                        if ($key == "after")
                        {
                            $after = $value;
                        }
                    }
                    echo "            <operation tag=\"$action\">\n";
                    if ($pos == '')
                    {
                        echo "                  <cmd action=\"{$this->android_keyword_cmd_map[$action]}\"/>\n";
                    }
                    else
                    {
                        echo "                  <cmd action=\"{$this->android_keyword_cmd_map[$action]}\" pos=\"$pos\"/>\n";
                    }
                    echo "                  <cmd action=\"Sleep\" time=\"$after\"/>\n";
                    echo "                  <cmd action=\"GetAllErrors\"/>\n";
                    echo "                  <cmd action=\"CheckLog\"/>\n";
                    echo "            </operation>\n";
                }
                break;
            }
        }

        echo "      </trickmode>\n";
    }

    private function genTmModeArray($mode)
    {
        $tmpTkArray = $this->tkArray;
        $tmpHeadElem = $tmpTkArray[$mode-1];
        array_splice($tmpTkArray, $mode-1, 1);
        array_unshift($tmpTkArray, $tmpHeadElem);

        return $tmpTkArray;
    }

    private function genRandomArray($fixCountCaseArray, $randomCountCaseArray)
    {
        $randomArray = $fixCountCaseArray;
        foreach ($randomCountCaseArray as $oneRandom)
        {
            $n = rand(1, 5);
            for ($i=0; $i<$n; $i++)
            {
                array_push($randomArray, $oneRandom);
            }
        }
        shuffle($randomArray);
        return $randomArray;
    }
}
?>