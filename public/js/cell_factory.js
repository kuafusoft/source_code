var cell_factory = {
	get: function($type, $params){
		var tool = new kf_tool(), o, className;
		var jsFile = '/js/cell_' + $type + '.js';
		$params = $params || {};
		className = 'cell_' + $type;
// tool.debug(className);
		try{
			tool.loadFile(jsFile, 'js');
			var constructor = "new " + className + "($type, $params)";
			o = eval(constructor);
		}catch(e){
// tool.debug(e);
			jsFile = '/js/cell.js';
			tool.loadFile(jsFile, 'js');
			o = new cell($type, $params);
		}
		return o;
	},
	
	cell:function(dom){
		var props = JSON.parse(dom.hiddenProp);
		return this.get(props.type, props);
	}
};