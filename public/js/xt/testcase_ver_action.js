// JavaScript Document
(function(){
	var DB = KF_GRID_ACTIONS.xt;
	var tool = new kf_tool();
	tool.loadFile('/js/xt/tc_ver_comm_action.js', 'js');
	
	DB.testcase_ver = function(grid){
		$table.supr.call(this, grid);
		this.comm_action = new tc_ver_comm_action(this);
	};

	var $table = DB.testcase_ver;
	tool.extend($table, gc_grid_action);

	$table.prototype.ver_diff = function(vers){
		$('<div id="div_ver_diff" />').load('/jqgrid/jqgrid/oper/diff/db/xt/table/testcase_ver/vers/' + JSON.stringify(vers), function(data){
			if(tool.handleRequestFail(data))return;

	//debug(data);
			var hideTheSame = function(event){
				tool.hideTheSame(this, event)
			};
			var dialog = $(this).html(data).dialog({
				width:800,
				height:500,
				autoOpen: false,
				title: 'Compare the versions',
				modal: true,
				buttons: {
					'Export To Excel': function(){
						$.post('/jqgrid/jqgrid', {oper:'diff', db:db, table:table, vers:vers}, function(data){
							dialog.dialog( "close" );
							$(gridId).trigger('reloadGrid');
						});
					},
					Close:function(){
						dialog.dialog('close');
					}
				},
				open:function(){
					$('#hide_same').unbind().bind('click', {selector:'#div_ver_diff tr.jqgrow'}, hideTheSame);
				},
				close:function(event, ui){
					$(this).remove();
				}
			});
			dialog.dialog('open');
		});
	};

	$table.prototype.buttonActions = function(key, options){
		var gridId = this.getParams('gridSelector');
		var selectedRows = $(gridId).getGridParam('selarrrow');
		var tc_id = $('#div_hidden #id').val();
		var baseId = "information_tabs_xt_testcase_" + tc_id;
		switch(key){
			case 'ver_abort':
				if (tool.checkSelectedRows(selectedRows, 1)){
					this.comm_action._abort(tc_id, selectedRows);
				}
				break;
				
			case 'ver_ask2review':
				if (tool.checkSelectedRows(selectedRows, 1)){
					this.comm_action._ask2review(tc_id, selectedRows);
				}
				break;
				
			case 'ver_diff':
				if (tool.checkSelectedRows(selectedRows, 1)){
					this.ver_diff(selectedRows);
				}
				break;
					
			case 'link2prj':
				if(tool.checkSelectedRows(selectedRows, {min:1, max:1})){
					var dialogParams = {div_id:'ver_link2prj', element:selectedRows, height:300}, url = '/jqgrid/jqgrid/db/xt/table/testcase_ver/oper/link2prj/element/' + selectedRows;
					tool.actionDialog(dialogParams, url, undefined, function(data){
						if(tool.handleRequestFail(data))return;
						if (data == 1004){ // not found the action
							alert("Sorry, this feature " + title + " is not implemented yet");
						}
						else{
							// completeFunction(data, dialogParams);
							$(gridId).trigger('reloadGrid');
						}
					});
				}
				break;
				
			case 'unlinkfromprj':
				if(tool.checkSelectedRows(selectedRows, {min:1, max:1})){
					var dialogParams = {div_id:'ver_unlinkfromprj', element:selectedRows, height:300}, url = '/jqgrid/jqgrid/db/xt/table/testcase_ver/oper/unlinkfromprj/element/' + selectedRows;
					tool.actionDialog(dialogParams, url, undefined, function(data){
						if(tool.handleRequestFail(data))return;
						if (data == 1004){ // not found the action
							alert("Sorry, this feature " + title + " is not implemented yet");
						}
						else{
							// completeFunction(data, dialogParams);
							$(gridId).trigger('reloadGrid');
						}
					});
				}
				break;
				
			default:
				$table.supr.prototype.buttonActions.call(this, key, options);
		}
	};
})();