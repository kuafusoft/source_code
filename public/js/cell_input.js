var tool = new kf_tool();
tool.loadFile('/js/cell.js', 'js');

function cell_input($type, $params){
	$params['tag'] = 'input';
	cell_input.supr.call(this, $type, $params);
}

tool.extend(cell_input, cell);

cell_input.prototype._edit = function(){
	var $str = "<input " + this.propStr + ">";
	return $str;
};
