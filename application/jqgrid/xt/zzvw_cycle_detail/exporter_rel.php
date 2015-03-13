<?php
require_once('dbfactory.php');
require_once('exporter_txt.php');

class xt_zzvw_cycle_detail_exporter_rel extends exporter_txt{
	protected function init($params){
		parent::init($params);
		$this->fileName = $this->params['db'].'_'.$this->params['table'].'.html';
	}
	
	public function _export(){
		$this->str = $this->generateHTMLHeader("Case Release");
		$this->str .= "<body>";
		$this->str .= $this->generateIntro($this->params);
		$this->str .= $this->generateSetup($this->params);
		$this->str .= $this->generateTestCase($this->params);
		$this->str .= "</body></html>";
	}
	
	private function generateHTMLHeader($title){
		$str = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Frameset//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd\">";
		$str .= "<html xmlns=\"http://www.w3.org/1999/xhtml\">";
		$str .= "<head>";
		$str .= "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />";
		$str .= "<title>$title</title>";
		$str .= "<script type='text/javascript'>
			function displayDiv(id){
				var div = document.getElementById(id);
				if (div){
					var display = div.style.display;
					if (display == 'none')
						display = 'block';
					else
						display = 'none';
					div.style.display = display;
				}
			}
			</script>";
		$str .= "<style type='text/css'>
		<!--
		a:link {
			text-decoration: none;
		}
		a:visited {
			text-decoration: none;
		}
		a:hover {
			text-decoration: none;
		}
		a:active {
			text-decoration: none;
		}
		div{
			position:relative; left:30px;
		}
		.module{
			background-color: #DDDDDD;
			
		}
		.moduledesc{
			background-color: #EEEEEE;
		}
		.case{
			background-color: #DDDDDD;
		}
		-->
		</style>
		";
		
		$str .= "</head>";
		return $str;
	} 

	private function generateIntro($params){
		if(empty($params['intro']))
			$params['intro'] = "Intro";
		$str = "<a href='javascript:displayDiv(\"div_intro\")'><strong>Introduction</strong></a><BR />";
		$str .= "<div id='div_intro' style='display:none'>";
		$str .= $params['intro'];
		$str .= "</div>";
		return $str;
	}
	
	private function generateSetup($params){
		if (empty($params['setup']))
			$params['setup'] = "Setup";
		$str = "<a href='javascript:displayDiv(\"div_setup\")'><strong>Setup Test Environment</strong></a><BR />";
		$str .= "<div id='div_setup' style='display:none'>";
		$str .= $params['setup'];
		$str .= "</div>";
		return $str;
	}
	
	private function generateTestCase($params){
		$db = dbFactory::get($params['db']);
		$ids = implode(',', $params['id']);
		$module = "";
		$str = "";
		$sql = "SELECT tc.id, tc.code, tc.summary, module.id as module_id, module.name as module, category.name as category, source.name as source, ".
			" auto_level.name as auto_level, ver.manual_run_minutes, ver.auto_run_minutes, ver.objective, ver.precondition, ver.steps, ver.expected_result, ver.command ".
			" FROM cycle_detail left join testcase_ver ver on cycle_detail.testcase_ver_id=ver.id".
			" left join testcase tc on tc.id=ver.testcase_id ".
			" left join testcase_module module on tc.testcase_module_id=module.id".
			" left join testcase_category category on tc.testcase_category_id=category.id".
			" left join testcase_source source on tc.testcase_source_id=source.id".
			" left join auto_level on ver.auto_level_id=auto_level.id".
			" WHERE cycle_detail.id in ($ids) ".
			" ORDER BY module ASC";
		$res = $db->query($sql);
		$change = false;
		while($row = $res->fetch()){
			if ($row['module'] != $module){//module«–ªª
				if (!empty($module))
					$str .= "</div>";
				$module = $row['module'];
				$str .= "<BR /><a href='javascript:displayDiv(\"div_module_{$row['module_id']}\")'><strong>{$row['module']}</strong></a><BR />";
				$str .= "<div id='div_module_{$row['module_id']}' style='display:none'>";
			}
			$str .= $this->generateTCHTML($row);
		}
		$str .= "</div>";
		return $str;
	}
	
	private function generateTCHTML($row){
		$fields = array('summary'=>'Name',
						'category'=>'Category',
						'auto_level'=>'Auto Level',
						'manual_run_minutes'=>'Manual Run Time',
						'auto_run_minutes'=>'Auto Run Time',
						'command'=>'Command',
						'objective'=>'Objective',
						'precondition'=>'Environment',
						'steps'=>'Steps',
						'expected_result'=>'Expected Result',
		);
		$str = "<p /><a href='javascript:displayDiv(\"div_case_{$row['id']}\")'>".$row['code'].":".$row['summary']."</a>";
		$str .= "<div id='div_case_{$row['id']}' style='display:none'><table border='1' bgcolor='#EEEEEE' width='100%'>";
		
		$currentRow = 0;
		foreach($fields as $key=>$caption){
			$content = $row[$key];
			if ($key == 'command' || $key == 'objective' || $key == 'steps' || $key == 'precondition' || $key == 'expected_result')
				$content = str_replace("\n", '<br />', $content);
			if (empty($content))
				$content = "&nbsp";
			$bgColor = '#CCCCCC';
			if ($currentRow % 2)
				$bgColor = '#DDDDDD';
			$str .= sprintf("<tr style='background-color:%s; color:blue'>
				<td width='15%%'>%s:</td>
				<td width='85%%'>%s</td>
				</tr>\n", $bgColor, $caption, $content);
			$currentRow ++;        
		}
		$str .= "</table></div>\n";
		return $str;
	}
	
};

?>
