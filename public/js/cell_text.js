var tool = new kf_tool();
tool.loadFile('/js/cell_input.js', 'js');

function cell_text($type, $params){
	cell_text.supr.call(this, $type, $params);
}

tool.extend(cell_text, cell_input);

cell_text.prototype.specialProps = function(){
	this.params['placeholder'] = this.params['placeholder'] || 'Please Input ' + this.params['name'];
	var pp = this.supr.prototype.specialProps.call(this);
	return pp.concat(['placeholder', 'invalidChar']);
};
