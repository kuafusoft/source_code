// JavaScript Document
(function(){
	var DB = KF_GRID_ACTIONS.doc;
	var tool = new kf_tool();
	
	DB.doc = function(grid){
		$table.supr.call(this, grid);
	};

	var $table = DB.doc;
	tool.extend($table, gc_grid_action);
	
	$table.prototype.getParamsForDefaultAction = function(action, data){
tool.debug(action);
tool.debug(data);
		var params = $table.supr.call(this, action, data);
tool.debug(params);		
		return params;
	};
	
	$table.prototype.view_edit_edit = function(p){
		var dialog = $table.supr.prototype.view_edit_edit.call(this, p);
		dialog.find('#view_edit_ask2review,#view_edit_publish,#view_edit_coversrs,#view_edit_abort').hide();
		return dialog;
	};
	
}());
