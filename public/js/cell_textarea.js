var tool = new kf_tool();

function cell_textarea($type, $params){
	tool.loadFile('/js/cell.js', 'js');
	cell_textarea.supr.call(this, $type, $params);
}

tool.extend(cell_textarea, cell);

cell_textarea.prototype.specialProps = function(){
	var pp = this.supr.prototype.specialProps.call(this);
	this.params['placeholder'] = this.params['placeholder'] || 'Please Input ' + this.params['name'];
	this.params['rows'] = this.params['rows'] || 5;
	pp.push('placeholder');
	pp.push('rows');
	pp.push('invalidChar');
	return pp;
}