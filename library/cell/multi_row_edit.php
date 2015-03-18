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
		$this->params['onlyshowchecked'] = true;
	}
	
	protected function getProps(){
		$props = parent::getProps();
		$props[] = 'temp';
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
			$ret[] = $this->displayTemp($temp, $prefix);
		}
		if(!empty($params['value'])){
			$ret[] = $this->displayData($params);
		}
		$ret[] = "</div>";
		$ret[] = "</fieldset>";
		return implode("\n", $ret);
	};
	
	protected function displayTemp($temp, $prefix){
		$ret = array();
		foreach($temp as $k=>$e){ //将模板置上ignored属性
			$temp[$k]['ignored'] = 'ignored';
		}
		$temp_form = new kf_form($temp, $params['value'], $display_status);

		$ret[] = "<div id='{$prefix}_temp' style='display:none;'>";
			$ret[] = "<div ignored='ignored' style='float:left;width:90%;'>";
				$ret[] = $temp_form->display(count($temp));
			$ret[] = "</div>";
			
			$onclick = "javascript:XT.addNewRowForMulti(\"$prefix\")";
			$ret[] = "<div ignored='ignored' style='float:right;'><button style='vertical-align:bottom;' onclick='$onclick' type='button' id='{$prefix}_add'>Add</button></div>";
		$ret[] = "</div>";
		return implode("\n", $ret);
	}
	
	protected function displayData($params){
		
			$ret[] = "<div style='clear:both;'><table id='{$prefix}_values' border='1' cellspacing='1' style='width:100%;background-color:#a0c6e5;'><tbody>";
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
			
			
		}
		//如果有数据，则显示数据
		
		
		$params = $this->params;
		if($this->params['init_type'] == 'single'){
			unset($params['single_multi']);
			$ret[] = "<div id='cart_div_{$this->params['name']}' current_state='{$this->params['init_type']}'".
				" single_multi='".json_encode($this->params['single_multi'])."'";
			if($display_status ==  DISPLAY_STATUS_EDIT){
				$ret[] = " onmouseout='XT.hideCartButton(\"cart_div_{$this->params['name']}\")' onmouseover='XT.showCartButton(\"cart_div_{$this->params['name']}\")'";
			}
			$ret[] = ">";
			$params['type'] = 'select';
		}
		else{
			$params['type'] = 'cart';
		}
		$select = cellFactory::get($params);
		$ret[] = $select->display($display_status);

		if($this->params['init_type'] == 'single'){
			$ret[] = "</div>";
		}
		$ret[] = "</fieldset>";
		return implode("\n", $ret);
	}
	
	protected function displayTemp(){
		
		
	}
	
	protected function displayData(){
		
		
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
		
}
?>