var tool = new kf_tool();

function cell_date($type, $params){
	tool.loadFile('/js/cell_text.js', 'js');
	cell_date.supr.call(this, $type, $params);
	this.params['date'] = 'date';
}

tool.extend(cell_date, cell_text);

cell_date.prototype.specialProps = function(){
	var pp = this.supr.prototype.specialProps.call(this);
	pp.push('date');
	return pp;
}
