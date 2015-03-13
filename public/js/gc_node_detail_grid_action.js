function gc_node_detail_grid_action(grid){
	gc_node_detail_grid_action.supr.call(this, grid);
}

var tool = new kf_tool();
tool.extend(gc_node_detail_grid_action, gc_grid_action);

gc_node_detail_grid_action.prototype.bindEventsForInfo = function(divId, rowId){
	var $this = this;
	var table = $this.getParams('table');
	var type_field = table + '_type_id';
	//对于已存在的记录，不允许修改类型，也即类型一旦确定就不可更改
	if ($('#' + divId + ' #' + type_field).attr('editable')){
		$('#' + divId + ' #' + type_field).attr('disabled', rowId > 0);
		$('#' + divId + ' #' + type_field).attr('editable', rowId ? 0 : 1);
	}
	$('#' + divId + ' #' + type_field).unbind('change').bind('change', function(){
		var type_id = $(this).val();
		$('#' + divId + ' #detail_info').load('/jqgrid/jqgrid/db/' + $this.getParams('db') + '/table/' + table + '/oper/update_node_detail_information/element/' + rowId + '/type_id/' + type_id, function(){
			
		});
	});
}
