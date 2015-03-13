// JavaScript Document
(function(){
	var DB = KF_GRID_ACTIONS.workflow;
	var tool = new kf_tool();
//	tool.loadFile('/js/gc_summary_detail_grid_action.js', 'js');
	DB.period = function(grid){
		$table.supr.call(this, grid);
	};
	var $table = DB.period;
	tool.extend($table, gc_grid_action);

	$table.prototype.getGridsForInfo = function(divId){
		return [{container:'detail', table:'daily_note', rules:[{field:'levels', op:'eq', data:'son-level'}]}];
	}
	
	$table.prototype.buttonActions = function(action, p){
		var $this = this;
		var db = this.getParams('db'), table = this.getParams('table');
		var gridId = this.getParams('gridSelector');
		var selectedRows = $(gridId).getGridParam('selarrrow');
		switch(action){
			case 'batch_gen':
				var url = '/jqgrid/jqgrid/db/workflow/table/period/oper/batch_gen';
				tool.actionDialog({div_id:'batch_gen', width:600, height:300, title:'Batch Generate'}, url, undefined, function(data){
					if (data != ''){
						var data = JSON.parse(data);
						var str = 'The following periods existed:\n';
						for(var i in data){
							str += " [name:" + data[i]['name'] + ", from:" + data[i]['from'] + ", end:" + data[i]['end'] + "]\n";
						}
						alert(str);
					}
				});
				break;
			case 'export':
				if (this.tool.checkSelectedRows(selectedRows, {min:1, max:1})){
					var dialog_params = {
						div_id:'export_div', 
						width:400, 
						height:300, 
						title:'Export',
						open:function(){
							$('#export_div input:radio[name="export_type"]').change(function(event){
								if ($(this).is(':checked')){
									switch($(this).val()){
										case 'summary_report':
											$('#export_div #div_for_summary_report_options').show();
											break;
										default:
											$('#export_div #div_for_summary_report_options').hide();
											break;
									}
								}
							});
						}
					};
					var url = '/jqgrid/jqgrid/db/' + db + '/table/' + table + '/oper/export/element/' + selectedRows;
					$this.tool.actionDialog(dialog_params, url, undefined, function(data){
//						location.href = "/download.php?filename=" + encodeURIComponent(data) + "&remove=1";
					});
				};
				break;
			default:
				$table.supr.prototype.buttonActions.call(this, action, p);
		}
	}
}());
