// JavaScript Document
(function(){
	var DB = KF_GRID_ACTIONS.workflow;
	var tool = new kf_tool();

	tool.loadFile('/js/gc_node_detail_grid_action.js', 'js');
	DB.prj = function(grid){
		$table.supr.call(this, grid);
	};
	var $table = DB.prj;
	tool.extend($table, gc_node_detail_grid_action);
	
	var _export = function(prj_id){
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
		var url = '/jqgrid/jqgrid/db/workflow/table/prj/oper/export/element/' + prj_id;
		tool.actionDialog(dialog_params, url, undefined, function(data){
			location.href = "/download.php?filename=" + encodeURIComponent(data) + "&remove=1";
		});
	};
		
	$table.prototype.getGridsForInfo = function(divId){
		return [{container:'prj_daily_note', table:'daily_note', rules:[{field:'daily_note_type_id', op:'eq', data:2}]}];
	}
	
	$table.prototype.contextActions = function(action, el){
		switch(action){
			case 'menu_export':
				var prj_id = el.attr('id');
				_export(prj_id);
				break;

				case 'write_note':
				var prj_id = el.attr('id');
				var grid = grid_factory.get('workflow', 'daily_note');
				var grid_action = grid.getAction();
				grid_action.setParams({postData:{daily_note_type_id:2, prj_id:prj_id}});
				grid_action.information(0);
//this.tool.debug(grid_action.getParams());				
				grid_action.delParams(['postData']);
				break;
				
			default:
				$table.supr.prototype.contextActions.call(this, action, el);
		}	
	};
	
	$table.prototype.buttonActions = function(action, p){
		var $this = this;
		var db = this.getParams('db'), table = this.getParams('table');
		var gridId = this.getParams('gridSelector');
		var selectedRows = $(gridId).getGridParam('selarrrow');

		switch(action){
			case 'export':
				if (tool.checkSelectedRows(selectedRows, {min:1, max:1})){
					_export(selectedRows);
				};
				break;
			case 'collect_note':
			/*
			应选择报表周期，并询问其他信息
			报表名称：prj_name + '- ' + 周期 + '-' + creater
			*/
				var prj_id = p.attr('id');
				var url = '/jqgrid/jqgrid/db/workflow/table/prj/oper/collect_note/element/' + prj_id;
				tool.actionDialog({div_id:'collect_note_div', width:600, height:400, title:'Collect Notes'}, url, undefined, function(data){
				alert(data);
//							location.href = "/download.php?filename=" + encodeURIComponent(data) + "&remove=1";
				});
				break;
			default:
				$table.supr.prototype.buttonActions.call(this, action, p);
		}
	};
	
	$table.prototype.information_open = function(divId, element_id, pageName){
		pageName = pageName || 'all';
		$table.supr.prototype.information_open.call(this, divId, element_id, pageName);
		var divSelector = '#' + divId;
		//设置联动关系
		var target = [
			{selector:divSelector + ' select#prj_phase_id', type:'select', field:'prj_type_id', url:'/jqgrid/jqgrid/oper/linkage/db/workflow/table/prj_phase'},
		];
		tool.linkage({selector:divSelector + ' select#prj_type_id'}, target);
		target = [
			{selector:divSelector + ' select#part_id', type:'select', field:'family_id', url:'/jqgrid/jqgrid/oper/linkage/db/workflow/table/part'},
		];
		tool.linkage({selector:divSelector + ' select#family_id'}, target);
		return true;	
		
	};
}());
