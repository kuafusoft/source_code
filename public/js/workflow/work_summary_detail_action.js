// JavaScript Document
(function(){
	var DB = KF_GRID_ACTIONS.workflow;
	var tool = new kf_tool();

	DB.work_summary_detail = function(grid){
		$table.supr.call(this, grid);
	};

	var $table = DB.work_summary_detail;
	tool.extend($table, gc_grid_action);

	$table.prototype.buttonActions = function(action, p){
		var $this = this;
		switch(action){
			case 'import_note':
				/*
				从自己的daily note里导入
				*/
				var db = 'workflow', table ='daily_note', id = $this.getParams('p_id');
				var rules = [{field:'creater_id', op:'eq', data:'48'}];
				var postData = {who:'myself', work_summary_id:id};
				var dialog_params = {
					height:600, 
					buttons:{
						OK:function(){
							var gridId = '#dialog_grid_' + db + '_' + table + '_list';
							var selectedRows = $(gridId).getGridParam('selarrrow');
							// 将selected内容插入work_summary_detail
							if ($this.tool.checkSelectedRows(selectedRows, 1)){
								var url = '/jqgrid/jqgrid/db/' + db + '/table/' + $this.getParams('table') + '/oper/import_note/parent/' + id;
								var dialog = this;
								$.post(url, {element:selectedRows}, function(data){
									if (data != 0)
										alert(data);
									else{
										$(dialog).dialog('close');
										var gridId = $this.getParams('gridSelector');
										$(gridId).trigger('reloadGrid');
									}
								});
							}
						},
						Cancel:function(){
							$(this).dialog('close');
						}
					}
				};
//				postData.filters = JSON.stringify({groupOp:'AND', rules:rules});
				this.tool.dialogLoadGrid('workflow', 'daily_note', dialog_params, postData);
				break;
			default:
				$table.supr.prototype.buttonActions.call(this, action, p);
		}
	}
}());
