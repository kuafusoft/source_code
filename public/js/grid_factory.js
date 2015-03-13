var KF_GRIDS;
KF_GRIDS = KF_GRIDS || {};

var grid_factory = {
	objs: {},
	get: function($db, $table, $params){
		var tool = new kf_tool();
		if (this._init == undefined){
			this._init = 1;
			tool.loadFile('/js/gc_kf.js', 'js');
			tool.loadFile('/js/gc_db_table.js', 'js');
			tool.loadFile('/js/gc_grid.js', 'js');
		}
		$params = $params || {};
		var container = $params['container'] || 'mainContent';
		this.objs[$db] = this.objs[$db] || {};
		this.objs[$db][$table] = this.objs[$db][$table] || {};
		if (this.objs[$db][$table][container] == undefined){
			$params.db = $db;
			$params.table = $table;
// tool.debug($params);			
			try{
//				tool.loadFile('js/' + $db + '.js', 'js');
				KF_GRIDS[$db] = KF_GRIDS[$db] || {};
				var jsFile = this.getJS($db, $table);
				tool.loadFile(jsFile, 'js');
				
				this.objs[$db][$table][container] = new KF_GRIDS[$db][$table]($params);
			}catch(e){
				this.objs[$db][$table][container] = new gc_grid($params);
			}
		}
		var o = this.objs[$db][$table][container];
		
		o.ready();
		return this.objs[$db][$table][container];
	},
	getJS: function($db, $table){
		var jsFile = '/js/' + $db + '/' + $table + '.js';
		return jsFile;
	}
};