// JavaScript Document
(function(){
	var DB = KF_GRID_ACTIONS.workflow;
	var tool = new kf_tool();

//	tool.loadFile('/js/gc_summary_detail_grid_action.js', 'js');
	DB.work_summary = function(grid){
		$table.supr.call(this, grid);
	};

	var $table = DB.work_summary;
	tool.extend($table, gc_grid_action);
	
	$table.prototype.getGridsForInfo = function(divId){
		return [{container:'detail', table:'work_summary_detail'}];
	}
	
	$table.prototype.information_open = function(divId, rowId, pageName){	
		$table.supr.prototype.information_open.call(this, divId, rowId, pageName);
		$('#' + divId + " #accordion").accordion({
			collapsible:true,
			header: "> div > h3",
		})
		.sortable({
			axis: "y",
			handle: "h3",
			stop: function( event, ui ) {
			// IE doesn't register the blur when sorting
			// so trigger focusout handlers to remove .ui-state-focus
				ui.item.children( "h3" ).triggerHandler( "focusout" );
			}
		});
	}

	$table.prototype.view_edit_cloneit = function(p){
tool.debug(p);	
		var divId = p.divId;
		$('#' + divId + " #accordion").accordion('destroy');
	}
	
	$table.prototype.buttonActions = function(action, p){
		switch(action){
			case 'import_note':
				var prj_id = p.attr('id');
				var url = '/jqgrid/jqgrid/db/workflow/table/prj/oper/import_note/element/' + prj_id;
				this.tool.actionDialog({div_id:'import_note_div', width:600, height:400, title:'Import Notes'}, url, undefined, function(data){
				alert(data);
//							location.href = "/download.php?filename=" + encodeURIComponent(data) + "&remove=1";
				});
				
				break;
			default:
				$table.supr.prototype.buttonActions.call(this, action, p);
		}
	};
	
}());
