var tool = new kf_tool();

function cell_button($type, $params){
	tool.loadFile('/js/cell.js', 'js');
	cell_button.supr.call(this, $type, $params);
}

tool.extend(cell_button, cell);

cell_button.prototype.specialProps = function(){
	var pp = this.supr.prototype.specialProps.call(this);
	pp.push('onclick');
	return pp;
}
