function gc_summary_detail_grid_action(grid){
	gc_summary_detail_grid_action.supr.call(this, grid);
}

var tool = new kf_tool();
tool.extend(gc_summary_detail_grid_action, gc_grid_action);

gc_summary_detail_grid_action.prototype.information_open = function(divId, rowId, pageName){
	var $this = this;
	gc_summary_detail_grid_action.supr.prototype.information_open.call(this, divId, rowId, pageName);
	var db = $this.getParams('db');
	var table = $this.getParams('table');
	var container = 'detail_' + rowId;
	var detail_table = this.getParams('detail_table') || table + '_detail';
	var rules = this.getFilterRules(rowId);
	var filters = JSON.stringify({groupOp:'AND', rules:rules});
	var detail_grid = grid_factory.get(db, detail_table, {container:container});
	detail_grid.setParams({p_id:rowId, filters:filters});
	return detail_grid.indexInDiv({filters:filters});
}

gc_summary_detail_grid_action.prototype.getFilterRules = function(rowId){
	var table = this.getParams('table');
	var rules = [{field:table + '_id', op:'eq', data:rowId}];
	return rules;
}
