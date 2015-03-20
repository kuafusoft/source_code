<?php
require_once('kf_cell.php');
require_once('kf_form.php');
/*
比较复杂的一个组件，用模板生成多行编辑界面
*/
class kf_multi_row_edit extends kf_cell{
	protected function init($params, $values){
		// $params['type'] = 'checkbox';
		parent::init($params, $values);
	}
	
	protected function getProps(){
		$props = parent::getProps();
		$props[] = 'temp';
		$props[] = 'prefix';
		$props[] = 'legend';
		return $props;
	}
	
	public function display($display_status = DISPLAY_STATUS_EDIT){
// print_r($this->params['temp']);
		$params = $this->params;
		if(empty($params['temp']))
			return "No detail yet";
		
		$temp = $params['temp'];
		$prefix = $params['prefix'];
		$ret = array("<fieldset ");
		if($display_status == DISPLAY_STATUS_EDIT){
			$onMouseOut = "onmouseout='XT.hideMultiRowTemp(\"$prefix\")'";
			$onMouseOver = "onmouseover='XT.showMultiRowTemp(\"$prefix\")'";
			$ret[] = " $onmouseout $onmouseover ";
		}
		$ret[] = ">";
		if(!empty($params['legend']))
			$ret .= "<legend>{$params['legend']}</legend>";
		$ret[] = "<div multirowedit='multirowedit' id='{$prefix}' >";
		if($display_status == DISPLAY_STATUS_EDIT){ //如果是编辑状态，则需要显示模板
			$ret[] = $this->displayTemp($params);//$temp, $prefix);
		}
		if(!empty($params['value'])){
			$ret[] = $this->displayData($params);
		}
		$ret[] = "</div>";
		$ret[] = "</fieldset>";
		return implode("\n", $ret);
	};
	
	protected function displayTemp($params){
		$temp = $params['temp'];
		$prefix = $params['prefix'];
		$ret = array();
		foreach($temp as $k=>$e){ //将模板置上ignored属性
			$temp[$k]['ignored'] = 'ignored';
		}
		$temp_form = new kf_form($temp, $params['value'], DISPLAY_STATUS_EDIT);

		$ret[] = "<div id='{$prefix}_temp' style='display:none;'>";
			$ret[] = "<div ignored='ignored' style='float:left;width:90%;'>";
				$ret[] = $temp_form->display(count($temp));
			$ret[] = "</div>";
			
			$onclick = "javascript:XT.addNewRowForMulti(\"$prefix\")";
			$ret[] = "<div ignored='ignored' style='float:right;'>".
					"<button style='vertical-align:bottom;' onclick='$onclick' type='button' id='{$prefix}_add'>Add</button>".
				"</div>";
		$ret[] = "</div>";
		return implode("\n", $ret);
	}
	
	protected function displayData($params){
		$ret = array();
		$ret[] = "<div style='clear:both;'>";
		$ret[] = "<table id='{$prefix}_values' border='1' cellspacing='1' style='width:100%;background-color:#a0c6e5;'><tbody>";
		$ret[] = "<tr id='{$prefix}_header' >";
		$ret[] = "<th id='del' width='20px'>X</th>";
		foreach($temp as $e){
			if(empty($e['id'])) $e['id'] = $e['name'];
			$label = $e['label'];
			if(!empty($e['post']))
				$label .= "({$e['post']})";
			$ret[] = "<th id='{$e['id']}'>$label</th>";
		}
		$ret[] = "</tr>";
		//如果有数据，则显示数据
		$values = $params['value'];
		if(!empty($values)){
			$p = array();
			$strP = json_encode($p);
			foreach($values as $vp){
				$ret[] = "<tr>";
				$ret[] = "<td id='del'>";
					$ret[] = "<a editable='1' disabled='true' prop_edit='disabled' onclick='javascript:XT.deleteSelfRow(this)' href='javascript:void(0)'>X</a>";
				$ret[] = "</td>";
				foreach($temp as $k=>$model){
					$model['type'] = 'text';
					$model['value'] = $vp;
					$e = cellFactory::get($model);
					$ret[] = $e->display(DISPLAY_STATUS_VIEW);
					// $e = $this->model2e($model, $vp, 'view', false);
					// $e['editable'] = 0;
					// $ret[] = $this->generateInput($e, $strP, 'view');
				}
				$ret[] = "</tr>";
			}
		}
		$ret[] = "</tbody></table></div>";			
		return implode("\n", $ret);
	}
}
?>