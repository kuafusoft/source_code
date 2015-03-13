// JavaScript Document
(function(){
	var DB = KF_GRID_ACTIONS.xt;
	var tool = new kf_tool();
	
	DB.testcase_module = function(grid){
		$table.supr.call(this, grid);
	};

	var $table = DB.testcase_module;
	tool.extend($table, gc_grid_action);
	
	// $table.prototype.getGridsForInfo = function(divId){
		// var ver_id = $('#' + divId + ' #div_view_edit #ver_id').val();
		// var grids = [
			// {tab:'edit_history', container:'edit_history', table:'testcase_module_history'},
		// ];
		// return grids;
	// };
}());
