// JavaScript Document
(function(){
	var DB = KF_GRID_ACTIONS.workflow;
	var tool = new kf_tool();

	tool.loadFile('/js/gc_node_detail_grid_action.js', 'js');
	DB.cqi_ticket = function(grid){
		$table.supr.call(this, grid);
	};
	var $table = DB.cqi_ticket;
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
		
		$(divSelector + ' #community_thread_number').unbind('keyup').bind('keyup', function(){
			var num = $(this).val();
			var link = $(divSelector + ' #community_link').val();
// tool.debug([num, link]);			
			$(divSelector + ' #community_link').val("https://community.freescale.com/thread/" + num);
		});
		return true;
	};
}());
