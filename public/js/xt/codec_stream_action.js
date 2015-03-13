// JavaScript Document
(function(){
	var DB = KF_GRID_ACTIONS.xt;
	var tool = new kf_tool();
	
	DB.codec_stream = function(grid){
		$table.supr.call(this, grid);
	};

	var $table = DB.codec_stream;
	tool.extend($table, gc_grid_action);
	
	$table.prototype.getAutoCompleteField = function(){ // used by gc_grid_action.ready
		return [{input:'key', field:'code'}];
	};
	
	$table.prototype.getGridsForInfo = function(divId){
		var grids = [
			{tab:'edit_history', container:'edit_history', table:'codec_stream_ver', params:{real_table:'codec_stream_ver'}},
			// {tab:'test_history', container:'test_history', table:'zzvw_cycle_detail_stream', params:{real_table:'cycle_detail'}},
		];
		return grids;
	};

}());
