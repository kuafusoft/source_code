// JavaScript Document
(function(){
	var DB = KF_GRID_ACTIONS.workflow;
	var tool = new kf_tool();

	tool.loadFile('/js/gc_node_detail_grid_action.js', 'js');
	DB.daily_note = function(grid){
		$table.supr.call(this, grid);
	};

	var $table = DB.daily_note;
	tool.extend($table, gc_node_detail_grid_action);

	$table.showReference = function(dialog, display){
// tool.debug(dialog);	
		var fieldset = dialog.find('#fieldset_reference');
		var reference = dialog.find('#div_reference');
		if (reference.html().trim().length == 0){
			var rules = [];
			rules.push({field:'levels', op:'eq', data:'son-level'});
			var filters = JSON.stringify({groupOp:'AND', rules:rules});
			var grid = grid_factory.get('workflow', 'daily_note', {container:'div_reference', filters:filters});
			grid.indexInDiv({filters:filters});
		}
		if (display)
			fieldset.show();
		else
			fieldset.hide();
	};
	
	$table.prototype.information_open = function(divId, rowId, pageName){	
		$table.supr.prototype.information_open.call(this, divId, rowId, pageName);
		if (rowId == 0){
			$table.showReference($('#' + divId), true);
		}
	};
	$table.prototype.buttonActions = function(action, p){
		switch(action){
			case 'import_content':
				//从哪儿导入数据？Daily Note？
				var dialog_params = {
					div_id:'import_content_from_daily_note',
					title:'Import Content',
					open:function(){
						var container = 'import_content_from_daily_note';
						var grid = grid_factory.get('workflow', 'daily_note', {container:container});
						grid.indexInDiv({});
					},
					buttons:{
						import:function(){
							var gridId = "#import_content_from_daily_note #import_content_from_daily_note_workflow_daily_note_list";
							var selectedRows = $(gridId).getGridParam('selarrrow');
							if ($this.tool.checkSelectedRows(selectedRows, 1)){
								
							}
						},
						cancel:function(){
							$(this).dialog('close');
						}
					}
				};
				this.tool.popDialog(dialog_params);
				break;
				
			default:
				$table.supr.prototype.buttonActions.call(this, action, p);
		}
	};
	
	$table.prototype.view_edit_edit = function(p){
		var dialog = $table.supr.prototype.view_edit_edit.call(this, p);
		$table.showReference(dialog, true);
		return dialog;
	};
	
	$table.prototype.view_edit_cancel = function(p){
		var dialog = $table.supr.prototype.view_edit_cancel.call(this, p);
		$table.showReference(dialog, false);
		return dialog;
	};

	$table.prototype.view_edit_save = function(p, newNext){
		var dialog = $table.supr.prototype.view_edit_save.call(this, p, newNext);
		if (newNext){
			$('#' + p.divId + ' #daily_note_type_id').attr('disabled', false);
			$('#' + p.divId + ' #daily_note_type_id').attr('editable', false);
		}
		else{
			$('#' + p.divId + ' #daily_note_type_id').attr('disabled', true);
			$('#' + p.divId + ' #daily_note_type_id').attr('editable', true);
		}
		$table.showReference(dialog, false);
		return dialog;
	};

	$table.prototype.view_edit_cloneit = function(p){
		var dialog = $table.supr.prototype.view_edit_cloneit.call(this, p);
		$table.showReference(dialog, true);
		return dialog;
	};

}());
