// JavaScript Document
(function(){
	var DB = KF_GRID_ACTIONS.workflow;
	var tool = new kf_tool();

	tool.loadFile('/js/gc_node_detail_grid_action.js', 'js');
	DB.reference_design_ticket = function(grid){
		$table.supr.call(this, grid);
	};
	var $table = DB.reference_design_ticket;
	tool.extend($table, gc_node_detail_grid_action);
	
	$table.prototype.information_open = function(divId, element_id, pageName){
		pageName = pageName || 'all';
		$table.supr.prototype.information_open.call(this, divId, element_id, pageName);
		var divSelector = '#' + divId;
		//设置联动关系
		var target = [
			{selector:divSelector + ' select#prj_id', type:'select', field:'customer_id', url:'/jqgrid/jqgrid/oper/linkage/db/workflow/table/prj'},
		];
		tool.linkage({selector:divSelector + ' select#customer_id'}, target);
		
		return true;
	};
}());
