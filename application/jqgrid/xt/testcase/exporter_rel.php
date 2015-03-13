<?php
require_once('dbfactory.php');
require_once('exporter_txt.php');

class xt_testcase_exporter_rel extends exporter_txt{
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
		$module = "";
		$str = "";
		$change = false;
		$res = $this->getData($params);
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
		$fields = array(
			'summary'=>'Name',
			'category'=>'Category',
			'auto_level'=>'Auto Level',
			'priority'=>'Priority',
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
	
	protected function getData($params){
		$db = dbFactory::get($params['db']);
		$ids = implode(",", $this->params['id']);
		if($params['prj_id'] == -1 || $params['prj_id'] == '-1'){
			$sql = "SELECT tc.id, tc.code, tc.summary, module.id as module_id, module.name as module, category.name as category, source.name as source, priority.name as priority,".
				" auto_level.name as auto_level, ver.manual_run_minutes, ver.auto_run_minutes, ver.objective, ver.precondition, ver.steps, ver.expected_result, ver.command ".
				" FROM testcase_ver ver ".
				" left join (".
					" SELECT testcase_ver.testcase_id, max(ver) as max_ver ".
					" FROM testcase_ver".
					" left join prj_testcase_ver on testcase_ver.id=prj_testcase_ver.testcase_ver_id".
					" WHERE testcase_ver.testcase_id in ($ids) and not isnull(prj_testcase_ver.id) and testcase_ver.edit_status_id in (".EDIT_STATUS_PUBLISHED.",".EDIT_STATUS_GOLDEN.")".
					" group by testcase_id".
				") max_ver on ver.testcase_id=max_ver.testcase_id".
				" left join testcase tc on tc.id=ver.testcase_id ".
				" left join testcase_module module on tc.testcase_module_id=module.id".
				" left join testcase_category category on tc.testcase_category_id=category.id".
				" left join testcase_source source on tc.testcase_source_id=source.id".
				" left join testcase_priority priority on ver.testcase_priority_id=priority.id".
				" left join auto_level on ver.auto_level_id=auto_level.id".
				" WHERE tc.id in ($ids) and ver.ver=max_ver.max_ver and ver.testcase_id=max_ver.testcase_id".
				" and ver.edit_status_id in (".EDIT_STATUS_PUBLISHED.",".EDIT_STATUS_GOLDEN.")".
				" ORDER BY module ASC";
		}
		else{
			$sql = "SELECT tc.id, tc.code, tc.summary, module.id as module_id, module.name as module, category.name as category, source.name as source, priority.name as priority,".
				" auto_level.name as auto_level, ver.manual_run_minutes, ver.auto_run_minutes, ver.objective, ver.precondition, ver.steps, ver.expected_result, ver.command ".
				" FROM testcase_ver ver left join testcase tc on tc.id=ver.testcase_id ".
				" left join prj_testcase_ver link on link.testcase_ver_id=ver.id".
				" left join testcase_module module on tc.testcase_module_id=module.id".
				" left join testcase_category category on tc.testcase_category_id=category.id".
				" left join testcase_source source on tc.testcase_source_id=source.id".
				" left join testcase_priority priority on ver.testcase_priority_id=priority.id".
				" left join auto_level on ver.auto_level_id=auto_level.id".
				" WHERE tc.id in ($ids) and link.prj_id={$params['prj_id']}".
				" and ver.edit_status_id in (".EDIT_STATUS_PUBLISHED.",".EDIT_STATUS_GOLDEN.")".
				" ORDER BY module ASC";
		}
// print_r($sql);		
		$res = $db->query($sql);
		return $res;
	}
	
};

?>
