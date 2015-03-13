// JavaScript Document
(function(){
	var DB = KF_GRID_ACTIONS.mcu;
	var tool = new kf_tool();
	
	DB.zzvw_device_ver = function(grid){
		$table.supr.call(this, grid);
	};

	var $table = DB.zzvw_device_ver;
	tool.extend($table, gc_grid_action);

	var db = 'mcu', table = 'zzvw_device_ver';
	$table.prototype.diff = function(vers){
		var wait_dialog = tool.waitingDialog();
		$('<div id="div_diff" />').load('/jqgrid/jqgrid/oper/diff/db/mcu/table/zzvw_device_ver/vers/' + JSON.stringify(vers), function(data){
			if(tool.handleRequestFail(data))return;

	//debug(data);
			var hideTheSame = function(event){
				tool.hideTheSame(this, event)
			};
			var dialog = $(this).html(data).dialog({
				width:1400,
				height:800,
				autoOpen: false,
				title: 'Compare the versions',
				modal: true,
				buttons: {
					'Export To Excel': function(){
						$.post('/jqgrid/jqgrid/db/mcu', {oper:'export_diff', table:table, vers:JSON.stringify(vers)}, function(data){
							location.href = "/download.php?filename=" + encodeURIComponent(data) + "&remove=1";
							// dialog.dialog( "close" );
							// $(gridId).trigger('reloadGrid');
						});
					},
					Close:function(){
						dialog.dialog('close');
					}
				},
				open:function(){
					$('#hide_same').unbind().bind('click', {selector:'#div_diff tr.jqgrow.thesame'}, function(event){
						// var $this = this;
						var checked = $(this).attr('checked');
// tool.debug("checked = " + checked + "<<<<");		
tool.timeStart('oper');
						if(checked == 'checked'){
// tool.debug("checked");						
// tool.debug($(event.data.selector));						
							$(event.data.selector).hide();
						}
						else
							$(event.data.selector).show();
tool.timeEnd('oper');
						// $(event.data.selector).each(function(){
							// if (checked && $this.sameValue($(this)))
								// $(this).hide();
							// else
								// $(this).show();
						// });

						// tool.hideTheSame(this, event);
					});
					wait_dialog.dialog('close');
					// $('#hide_same').unbind().bind('click', {selector:'#div_ver_diff tr.jqgrow'}, hideTheSame);
				},
				close:function(event, ui){
					dialog.html('');
					dialog.remove();
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
			case 'diff':
				if (tool.checkSelectedRows(selectedRows, 1)){
					this.diff(selectedRows);
				}
				break;
			case 'upload_doc':
				break;
					
			default:
				$table.supr.prototype.buttonActions.call(this, key, options);
		}
	};
})();