<?php
require_once('kf_cell.php');
/*
比较复杂的一个组件，允许以两种形式出现：select（单选）或cart（多选），用一个+按钮来切换。
如果当前没有或只有一个选中项，则以select来表现，否则以checkbox来表现
*/
class kf_single_multi extends kf_cell{
	protected function init($params, $values){
		// $params['type'] = 'checkbox';
		parent::init($params, $values);
		$this->multi_edit = true;
		$this->multi_value = true;
		$this->params['onlyshowchecked'] = true;
	}
	
	protected function getProps(){
		$props = parent::getProps();
		if(!isset($this->params['cols']))
			$props['cols'] = 4;
		return $props;
	}
	
	public function display($display_status = DISPLAY_STATUS_EDIT){
// print_r($this->params['single_multi']);
		$ret = '';
		$params = $this->params;
		if($this->params['init_type'] == 'single'){
			unset($params['single_multi']);
			$ret = "<div id='cart_div_{$this->params['name']}' current_state='{$this->params['init_type']}'".
				" single_multi='".json_encode($this->params['single_multi'])."'";
			if($display_status ==  DISPLAY_STATUS_EDIT){
				$ret .= " onmouseout='XT.hideCartButton(\"cart_div_{$this->params['name']}\")' onmouseover='XT.showCartButton(\"cart_div_{$this->params['name']}\")'";
			}
			$ret .= ">";
			$params['type'] = 'select';
		}
		else{
			$params['type'] = 'cart';
		}
		$select = cellFactory::get($params);
		$ret .= $select->display($display_status);
		// $ret .= parent::display($display_status);
		// if($display_status == DISPLAY_STATUS_EDIT && $this->params['init_type'] == 'multi'){
			// $display = "style='display: none;'";
			// $ret .= "<div id='cart_button' $display>";
			// if (empty($this->params['cart_data']))
				// $this->params['cart_data'] = '{}';
			// $addParams = array('type'=>'button', 'id'=>'cart_add_'.$this->params['name'], 'value'=>'Add '.$this->params['name'], 'onclick'=>"XT.selectToCart(\"{$this->params['name']}\", \"{$this->params['cart_db']}\", \"{$this->params['cart_table']}\", \"{$this->params['label']}\", {$this->params['cart_data']})");
			// $resetParams = array('type'=>'button', 'id'=>'cart_reset_'.$this->params['name'], 'value'=>'Reset '.$this->params['name'], 'onclick'=>"XT.resetCart(\"{$this->params['name']}\", \"{$this->params['cart_db']}\", \"{$this->params['cart_table']}\", \"{$this->params['label']}\", {$this->params['cart_data']})");
			// $addButton = cellFactory::get($addParams);
			// $resetButton = cellFactory::get($resetParams);
			// $ret .= $addButton->display(DISPLAY_STATUS_EDIT).$resetButton->display(DISPLAY_STATUS_EDIT);
			// $ret .= "</div>";
		// }
		if($this->params['init_type'] == 'single'){
			$ret .= "</div>";
		}
		return $ret;
	}
}
?>