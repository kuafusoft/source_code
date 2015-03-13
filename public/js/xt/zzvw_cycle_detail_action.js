// JavaScript Document
(function(){
	var DB = KF_GRID_ACTIONS.xt;
	
	DB.zzvw_cycle_detail = function(grid){
		$table.supr.call(this, grid);
	};

	
	var $table = DB.zzvw_cycle_detail;
	var tool = new kf_tool();
	tool.extend($table, gc_grid_action);
tool.debug(DB);
	// var $cycle = DB.zzvw_cycle;
	// tool.extend($table, $cycle);
	
	$table.prototype.ready = function(base){
		$table.supr.prototype.ready.call(this, base);
		var conditionSelector = this.getParams('conditionSelector');
		this.setLinkage('div' + conditionSelector, ['select#testcase_module_id']);
	};
	
	$table.prototype.getAutoCompleteField = function(){ // used by gc_grid_action.ready
		return [{input:'key', field:'d_code'}];
	};
	
	$table.prototype.buttonActions = function(action, options){
		var $this = this;
		var ret = true;
		
		var params = $this.getParams();
		var db = params.db, table = params.table, container = params.container;
		var gridSelector = params.gridSelector;
		var conditionSelector = params.conditionSelector;
		
		var selectedRows = $(gridSelector).getGridParam('selarrrow');
		var cycle_id = $(conditionSelector).parent().parent().children().find("#id").attr('value');
tool.debug(container + "xxxxxx");

		var c_f = '';
		var element = '';
		var cell = new Array();
		if( typeof selectedRows != 'undefined' ){
			$.each(selectedRows, function(i, item){
				cell[i] = $(gridSelector).getCell(item, 'c_f');
			});
			c_f = JSON.stringify(cell);
			element = JSON.stringify(selectedRows);
		}
		//从前台传还是在后台取
		var oper = action;
		switch(action){
			case 'query_add':
				var params = { 
					db: db, 
					table: table, 
					refeshTab: container, 
					gridid: gridSelector.substr(1),
					parent: cycle_id,
					tabSelector: '#' + $('#' + $this.getParams('container')).parent().attr('id')
				};
				return $this.addcase(params);
				break;
			case 'query_update':
				// if (tool.checkSelectedRows(selectedRows, 1)){
					var postData = {db: db, table: table, oper: oper, element: element, c_f: c_f, parent: cycle_id};
					$.post('/jqgrid/jqgrid', postData, function(data){
						if(tool.handleRequestFail(data))return;
						if(data == 'success')
							alert("Update Successfully!");
						else
							alert("Update Wrong!");
					})
				// }
				break;
			case 'update_ver':
tool.debug($(conditionSelector + " #ver").val());
				if($(conditionSelector + " #ver").val() == 0){
					alert("Pls Select Ver In FilterCondition!");
					break;
				}
				if (tool.checkSelectedRows(selectedRows, 1)){
					var postData = {db: db, table: table, oper: oper, element: element, c_f: c_f, parent: cycle_id};
					$.post('/jqgrid/jqgrid', postData, function(data){
						if(tool.handleRequestFail(data))return;
						if(data == 'success')
							alert("Update Successfully!");
						else
							alert("Update Wrong!");
					})
				}
				break;
			case 'set_result':
				if (tool.checkSelectedRows(selectedRows, 1)){
					var url = '/jqgrid/jqgrid/db/' + db + '/table/' + table + '/oper/' + oper + '/parent/' + cycle_id;	
					var postData = {element: element, c_f: c_f};
					var dialogParams = {div_id: 'div_' + oper, width: 900, height: 400, title: 'Set Result', postData: postData};
					tool.actionDialog(dialogParams, url, undefined, function(){
						$(gridSelector).trigger('reloadGrid');
					});
				}
				break;
			case 'set_build_result':
				if (tool.checkSelectedRows(selectedRows, 1)){
					var url = '/jqgrid/jqgrid/db/' + db + '/table/' + table + '/oper/' + oper + '/parent/' + cycle_id;	
					var postData = {element: element, c_f: c_f};
					var dialogParams = {div_id: 'div_' + oper, width: 400, height: 200, title: 'Set Build Result', postData: postData};
					tool.actionDialog(dialogParams, url, undefined, function(){
						$(gridSelector).trigger('reloadGrid');
					});
				}
				break;
			case 'set_tester':
				if (tool.checkSelectedRows(selectedRows, 1)){
					var url = '/jqgrid/jqgrid/db/' + db + '/table/' + table + '/oper/' + oper + '/parent/' + cycle_id;	
					var postData = {element: element, c_f: c_f};
					var dialogParams = {div_id: 'div_' + oper, width: 400, height: 200, title: 'Set Testor', postData: postData};
					tool.actionDialog(dialogParams, url, undefined, function(){
						$(gridSelector).trigger('reloadGrid');
					});
				}
				break;
			case 'query_remove':
				oper = 'removecase';
			case 'removecase':
				if (tool.checkSelectedRows(selectedRows, 1)){
					var postData = {db: db, table: table, oper: oper, flag: '0', element: element, c_f: c_f, parent: cycle_id};
					var removecase = function(){
						$this.removecase(gridSelector, conditionSelector, postData);
					}
					removecase();
				}
				break;
			case 'add_del_env'://显示所有的case_env？
				if (tool.checkSelectedRows(selectedRows, 1)){
					var postData = {db: db, table: table, oper: oper, element: element, c_f: c_f, parent: cycle_id};
					$this.addDelEnv(gridSelector, container, postData);
				}
				break;	
			case 'add_del_trickmode':
				if (tool.checkSelectedRows(selectedRows, 1)){
					var postData = {db: db, table: table, element: element, oper: 'add_del_trickmode', parent: cycle_id, c_f: c_f};
					var addDelTrickMode = function(){
						return $this.addDelTrickMode(gridSelector, container, postData);
					}
					addDelTrickMode();
				}
				break;
			// case 'set_comment':
				// if (tool.checkSelectedRows(selectedRows, 1)){
					// var url = '/jqgrid/jqgrid/db/' + db + '/table/' + table + '/oper/' + oper + '/parent/' + cycle_id;	
					// var postData = {element: element, c_f: c_f};
					// var dialogParams = {div_id: 'div_' + oper, width: 600, height: 400, title: 'Set Comment', postData: postData};
					// tool.actionDialog(dialogParams, url, undefined, function(){
						// $(gridSelector).trigger('reloadGrid');
					// });
				// }
				// break;
			case 'set_crid':
				if (tool.checkSelectedRows(selectedRows, 1)){
					var url = '/jqgrid/jqgrid/db/' + db + '/table/' + table + '/oper/' + oper + '/parent/' + cycle_id;	
					var postData = {element: element, c_f: c_f};
					var dialogParams = {div_id: 'div_' + oper, width: 400, height: 200, title: 'Set CRID', postData: postData};
					tool.actionDialog(dialogParams, url, undefined, function(){
						$(gridSelector).trigger('reloadGrid');
					});
				}
				break;
			case 'export':
				if (tool.checkSelectedRows(selectedRows, 1)){
					var dialog_params = {
						div_id:'export_options',
						width:600,
						height:300,
						title:'Export',
						postData:{element: element},
						open:function(){
							$('#export_options #div_for_playlist_cte, #div_for_playlist_gvb, #div_for_playlist_android').hide();
							$('#export_options input:radio[name="export_type"]').change(function(event){
								if ($(this).is(':checked')){
									switch($(this).val()){
										case 'codec_playlist_cte':
											$('#export_options #div_for_playlist_cte').show();
											$('#export_options #div_for_playlist_android').hide();
											$('#export_options #div_for_playlist_gvb').hide();
											break;
										case 'codec_playlist_gvb':
											$('#export_options #div_for_playlist_cte').hide();
											$('#export_options #div_for_playlist_android').hide();
											$('#export_options #div_for_playlist_gvb').show();
											break;
										case 'codec_playlist_android':
											$('#export_options #div_for_playlist_cte').hide();
											$('#export_options #div_for_playlist_android').show();
											$('#export_options #div_for_playlist_gvb').hide();
											break;
										default:
											$('#export_options #div_for_playlist_cte').hide();
											$('#export_options #div_for_playlist_gvb').hide();
											$('#export_options #div_for_playlist_android').hide();
											break;
									}
								}
							});
						}
					};
					var url = '/jqgrid/jqgrid/db/' + db + '/table/' + table + '/oper/export/parent/' + cycle_id;
					tool.actionDialog(dialog_params, url, undefined, function(data){
						location.href = "/download.php?filename=" + encodeURIComponent(data) + "&remove=1";
					});
				}
				break;
			default:
				ret = $table.supr.prototype.buttonActions.call(this, action, options);							
		}
		return ret;					
	};
	
	$table.prototype.removecase = function(gridSelector, conditionSelector, postData){
		var $this = this;
		var div_id = 'div_removecase';
		
		var dialog_params = {
			height:150,
			width: 600,
			modal: true,
			autoOpen:false,
			close: function(){$(this).remove();},
			buttons: {
				"Delete Selected items": function() {
					var item = tool.getAllInput(conditionSelector)['data'];
					$.post('/jqgrid/jqgrid', $.extend(postData, item), function(revData){
tool.debug('datas:' + revData);
						if(tool.handleRequestFail(revData))return;
						//还要仔细考虑一下吧
						if(revData){
							var params = $.extend(true, postData, {gridSelector: gridSelector, conditionSelector: conditionSelector});
							var remove_rows = function(){
								return $this.remove_rows(revData, params);
							}
							remove_rows();
						}
						$(gridSelector).trigger('reloadGrid');
					});
					$(this).dialog( "close" );
				},
				Cancel: function() {
					$(this).dialog( "close" );
				}
			}
		};
		var html_params = '<p><span class="ui-icon ui-icon-alert" style="float: left; margin: 0 7px 20px 0;"></span>These items will be permanently deleted and cannot be recovered. Are you sure?</p>';
		var mydialog = $('<div id="' + div_id + '" title="Remove the cases from cycle?"></div"').html(html_params).dialog(dialog_params);
		mydialog.dialog('open');
	}
	
	$table.prototype.remove_rows = function(data, params){
		var $this = this;
		var db = params.db;
		var table = params.table;
		var conditionSelector = params.conditionSelector;
		var gridSelector = params.gridSelector;
		var div_id = 'div_remove_rows';
		
		var dialog_params = {
			height:150,
			width: 600,
			modal: true,
			autoOpen:false,
			close: function(){$(this).remove();},
			buttons: {
				"Still Delete": function() {
					var item = tool.getAllInput(conditionSelector)['data'];
					var postData = {db: db, table: table, oper: params.oper, flag: '1', parent: params.parent, element: params.element, c_f: params.c_f};
					$.post('/jqgrid/jqgrid', $.extend(postData, item), function(revData){
tool.debug(revData);
						if(tool.handleRequestFail(revData))return;
						$(gridSelector).trigger('reloadGrid');
					});
					$(this).dialog( "close" );
				},
				Cancel: function() {
					$(this).dialog( "close" );
				}
			}
		};
		var html_params = '<p><span class="ui-icon ui-icon-alert" style="float: left; margin: 0 7px 20px 0;"></span>' 
			+ data + ' !\r These items has been set result And will be permanently deleted and cannot be recovered. Are you sure?</p>';
		var mydialog = $('<div id="' + div_id + '" title="Still remove the cases from cycle?"></div"').html(html_params).dialog(dialog_params);
		mydialog.dialog('open');
	}
	
	$table.prototype.addDelEnv = function(gridSelector, refeshTab, postData){
		// var $this = this;
		var target_table = 'test_env';
		var div_id = "div_select_env";
		var parent = postData.parent;
		//用来干什么？
		var target_grid = grid_factory.get(postData.db, target_table, {container: div_id});
		target_grid.ready();
		var target_gridSelector = target_grid.getParams('gridSelector');	
		
		var fun_addenv = function(isDel){
			var selectedRec = $(target_gridSelector).getGridParam('selarrrow');
			var test_env_id = JSON.stringify(selectedRec);
			postData = $.extend(true, postData, {test_env_id: test_env_id, isDel: isDel});
			
			$.post('/jqgrid/jqgrid', postData, function(data){
				if(tool.handleRequestFail(data))return;
				var grid = grid_factory.get(postData.db, postData.table, {container: refeshTab, parent: parent});
				var filters = JSON.stringify({groupOp:'AND', rules:[{"field":"cycle_id","op":"eq","data":parent}]});
				//grid.setParams($.extend(true, params, {p_id:parent, parent:parent, filters:filters}));
				grid.indexInDiv({filters:filters});
			});
		};
		
		var defaultParams = tool.defaultDialogParams();
		var dialog_params = {
			div_id: div_id,
			title: 'Select Resource',
			width: 1024,
			height: 600,
			close: function(event, ui){
				$(this).remove();
			},
			open:function(){
				return target_grid.load();
			}
		};
		var dialogParams = $.extend(true, defaultParams, dialog_params);
		var url = '/jqgrid/index/db/' + postData.db + '/table/' + target_table + '/container/' + div_id + '/parent/' + parent;
		var buttons = {
			'Add': function(){
				//需要得到id和name
				fun_addenv(0);
				$(this).dialog('close');
			},
			'Del': function(){
				//需要得到id和name
				fun_addenv(1);
				$(this).dialog('close');
			},
			'Modify & Add': function(){
				//需要得到id和name
				fun_addenv(2);
				$(this).dialog('close');
			},
			Close: function(){
				$(this).dialog('close');
			}
		};

		dialogParams['buttons'] = buttons;
		tool.actionDialog(dialogParams, url);	
	};
	
	$table.prototype.addDelTrickMode = function(gridSelector, refeshTab, postData){
		var $this = this;
		var target_table = 'testcase';
		var div_id = 'div_stream_action';
		var parent = postData.parent;

		var target_grid = grid_factory.get(postData.db, target_table, {container: div_id});
		target_grid.ready();
		var target_gridSelector = target_grid.getParams('gridSelector');
		
		var fun_stream_action = function(isDel){
			var actions = $(target_gridSelector).getGridParam('selarrrow');
			postData = $.extend(true, postData, {actions: JSON.stringify(actions), isDel: isDel});
			
			$.post('/jqgrid/jqgrid', postData, 
				function(data, status){
					if(tool.handleRequestFail(data))return;
					if(isDel == 1 && data == 'success')
						alert("Delete Actions Successfully!!!");
					var grid = grid_factory.get(postData.db, postData.table, {container: refeshTab, parent: parent});
					var filters = JSON.stringify({groupOp:'AND', rules:[{"field":"cycle_id","op":"eq","data":parent}]});
					//grid.setParams($.extend(true, params, {p_id:parent, parent:parent, filters:filters}));
					grid.indexInDiv({filters:filters});
				}
			);
		};
		
		var url = '/jqgrid/index/db/'+ postData.db + "/table/" + target_table + "/container/" + div_id + '/parent/' + parent;
		var dialog_params = {
			div_id: "div_stream_action",
			height:600,
			width: 1024,
			modal: true,
			autoOpen:false,
			close: function(){$(this).remove();},
			open: function(){
				var grid = grid_factory.get(postData.db, target_table, {container: div_id});
				grid.ready();
				var view_edit_selector = "#view_edit_" + parent + " #div_view_edit"
				var conditionSelector = grid.getParams('conditionSelector');
				var advancedButtonSelector = grid.getParams('advancedButtonSelector');
				var advancedCheckbox = 'div' + conditionSelector + ' ' + advancedButtonSelector;
				var advancedDiv = 'div' + conditionSelector + ' ' + conditionSelector + '_advanced';
				
				var prj_id = $(view_edit_selector + " #prj_id").attr('value');
				var testcase_type_id = $(view_edit_selector + " #testcase_type_id").attr('value');
				
				$(conditionSelector + " #os_id").attr('disabled', true);
				$(conditionSelector + " #board_type_id").attr('disabled', true);
				$(conditionSelector + " #chip_id").attr('disabled', true);
				$(conditionSelector + " #prj_id").attr('value', prj_id).attr('disabled', true);
				$(conditionSelector + " #testcase_type_id").attr('value', testcase_type_id).attr('disabled', true);
				$(conditionSelector + " #isactive").attr('value', '1').attr('disabled',true);
				$(conditionSelector + " #query_new").remove();
				$(conditionSelector + " #query_import").remove();
				
				//var url = '/jqgrid/jqgrid/oper/getCaseModule/db/' + postData.db + '/table/' + postData.table;
				$this.setLinkage('div' + conditionSelector, ['select#testcase_type_id'], {testcase_type_id: testcase_type_id, field: testcase_type_id});
			},
			buttons: {
				"Add": function() {
					fun_stream_action(0);
					$(this).dialog( "close" );
				},
				"Del": function() {
					fun_stream_action(1);
					$(this).dialog( "close" );
				},
				Cancel: function() {
					$(this).dialog( "close" );
				}
			}
		};
		$.get(url, function(data){
			var mydialog = $('<div id="' + div_id + '" title="add or delete action for stream?"></div"').html(data).dialog(dialog_params);
			mydialog.dialog('open');
		})
	};
	
	// $table.prototype.cyclelink = function(url, selector, value, field){
		// var newParams = {value:value, cond:'REGEXP', field:field};
		// $.post(url, newParams, function(data){
			// var currentVal = $(selector).val();
			// $(selector).find('option').remove();
			// tool.generateOptions($(selector), data, 'id', 'name', true);
			// $(selector).val(currentVal);
		// }, 'json');
	// };
	
	$table.prototype.gridComplete = function(){
		var $this = this;
		var gridSelector = $this.getParams('gridSelector');
		var db = $this.getParams('db'), table = $this.getParams('table');
		var ids = $(gridSelector).getDataIDs(); 
		var oper = {getlatestresult: true, getcrossresult: true, getlogfile: true};
//var s_time, m_time, e_time;
		for(var i=0; i<ids.length; i++){
//s_time = new Date().getTime();		
			ce = "<input id=getlatestresult style='height:22px;width:90px;' type='button' value='latest result' title='case latest result'>"; 
			pe = "<input id=getcrossresult style='height:22px;width:90px;' type='button' value='cross result' title='cross project result'>"; 
			//ae = "<input id=getstreamaction style='height:10px;width:50px;' type='button' value='T' title='logfile'>";
			le = "<input id=getlogfile style='height:22px;width:50px;' type='button' value='logfile' title='logfile'>"; 
			//$(gridSelector).setRowData(ids[i],{act:ce+pe+ae+le});
			$(gridSelector).setRowData(ids[i],{act:ce+pe+le});
//m_time = new Date().getTime();		
			$this.gridCompletes(gridSelector, oper, ids[i], db, table);
//e_time = new Date().getTime();		
//tool.debug([s_time, m_time, e_time]);
		}
	};	
		
	$table.prototype.gridCompletes = function(gridSelector, oper, id, db, table){ 
		var element = id;
		var td_selector = gridSelector + ' #' + id;// + ' input[id="'+ oper + '"]';
		var td = $(td_selector);
		$.each(oper, function(key, value){
			td.find('input[id="'+ key + '"]').qtip("destroy").qtip({
				content: {
					// Set the text to an image HTML string with the correct src URL to the loading image you want to use
					prerender: false,
					text: function(event, api){
						$.ajax({ 
							url: "/jqgrid/jqgrid",
							type: 'POST',
							data: {db:db, table:table, oper:key, element:element}
						}).done(function(content) {
							// Set the tooltip content upon successful retrieval
							api.set('content.text', content);
							if(oper == 'getlogfile'){
								var qtip_id = api._id;
								var tabId = '#' + qtip_id + ' #' + qtip_id + '-content #logfiles_' + element;
								tool.defaultActionForTab(tabId);	
							}
						});
						return 'Loading...';
					},
					title: {
						button: true
					}
				},
				position: {
					viewport: $(window)
				},
				show: {
					event: 'click',
					solo: true,
					ready: false,
					modal: false
				},
				hide: {
					event: 'click' 
				}
			});
		});
	};
	
	$table.prototype.inputResult = function(id, gridSelector, divSelector, parent){
tool.debug(divSelector);
		var $this = this;
		var db = $this.getParams('db'), table = $this.getParams('table');
		var selectedValue = $(gridSelector + " " + divSelector).val();
		if (selectedValue > 1){
			$this.resultInfo(id, gridSelector, divSelector, selectedValue, parent);
		}
		else{
			var ids = new Array();
			ids[0] = id;
			var c_f = $(gridSelector).getCell(id, 'c_f');
			var content =$(divSelector + ' option[value="' + selectedValue +'"]').html(); 
			var postData = {db: db, table: table, parent: parent, oper: 'set_result', element: JSON.stringify(ids), select_item: selectedValue, c_f: JSON.stringify(c_f)};
			$.post('/jqgrid/jqgrid', postData, function(data){	
				if(tool.handleRequestFail(data))return;
				if(data){
					var datas = JSON.parse(data);
					$(gridSelector).setRowData(id, datas);
				}
			});
		}
	}
	
	$table.prototype.resultInfo = function(id, gridSelector, divSelector, selectedValue, parent){
tool.debug(divSelector);
tool.debug(parent);
		var $this = this;
		var c_f = $(gridSelector).getCell(id, 'c_f');
		var db = $this.getParams('db'), table = $this.getParams('table'), container = $this.getParams('container');
		var div_id = 'div_' + db + '_' + table + '_res_' + id;
		var resSelector = '#' + div_id;
		var oper = 'saveOneResult';
		
		if(typeof selectedValue == 'undefined')
			var selVal =$(gridSelector + " " + divSelector).val();
		else
			var selVal = selectedValue;	
		var dialog_params = {
			ok: 'close', 
			open: function(){
				var formid = $(resSelector + ' form').attr('id');
				var data = "<input id='id' type='hidden' name='id' value='" + id + "'>" +
					"<input id='purpose' type='hidden' name='purpose' value='upload'>" +
					"<input id='cellName' type='hidden' name='cellName' value='logfile'>" ;
				$(resSelector + ' #' + formid).append(data);
				var eventData = {gridSelector: gridSelector, resSelector: resSelector, parent: parent, id: id};
				$(resSelector + ' input[name="submit_a_cr[]"]').unbind('change').bind('change', eventData, function(event){
					return $this.crInfo(event);
				});
				$(resSelector + " #save").unbind('click', save).bind('click', {dialog:this}, save);
				$(resSelector + " #cancel").unbind('click', cancel).bind('click', {dialog:this}, cancel);
			},
			close: function(){
				if(typeof selectedValue == 'undefined')
					$(gridSelector).setRowData(id, {result_type_id:0, id:id});
				$(this).remove();
			},
			gridId: gridSelector.substr(1),
			rowId: id,
			title: 'Result Information for Case', 
			height: 600,
			width: 900
		};
		var save = function(event) {
			var inputs = tool.getAllInput(resSelector);
			selVal = inputs['data'].result_type_id;
			var ids = new Array();
			var c_fs = new Array();
			ids[0] = id;
			c_fs[0] = c_f;
			if (inputs['passed'].length == 0){
				var postData = {db: db, table: table, parent: parent, oper: oper, element: JSON.stringify(ids), c_f: JSON.stringify(c_fs)};
				$.post('/jqgrid/jqgrid', $.extend(postData, inputs['data']), function(data, textStatus){
					if(tool.handleRequestFail(data))return;
					if(data){
						var datas = JSON.parse(data);
tool.debug(datas);
						$(gridSelector).setRowData(id, datas);
						// if($(gridSelector + "_" + id + "_t"))
							// $(gridSelector + "_" + id + "_t").trigger('reloadGrid');
					}
				});
			}
			else{
				alert(inputs['tips'].join('\n'));
			}
			$(event.data.dialog).dialog( "close" );
		};
		var	cancel = function(event) {
			if(typeof selectedValue == 'undefined')
				$(gridSelector).setRowData(id, {result_type_id:0, id:id});
			$(event.data.dialog).dialog( "close" );
		};
		// dialog_params['buttons'] = buttons;
		var postData = {db: db, table: table, parent: parent, oper: 'resultInfo', element: id, result_type_id: selVal, c_f: c_f};
		$.post('/jqgrid/jqgrid', postData, function(data){						
			if(tool.handleRequestFail(data))return;
			var mydialog = $('<div id="' + div_id + '" title="Result Information for Case"></div"').html(data).dialog(dialog_params);
			mydialog.dialog('open');
		});
	};
	
	$table.prototype.buildResult = function(id, gridSelector, selectedValue, parent){
		var $this = this;
		var c_f = $(gridSelector).getCell(id, 'c_f');
		var db = $this.getParams('db'), table = $this.getParams('table');
		var oper = 'set_build_result';
		var ids = new Array();
		ids[0] = id;
		var url = '/jqgrid/jqgrid/db/' + db + '/table/' + table + '/oper/' + oper + '/parent/' + parent;	
		var postData = {element: JSON.stringify(ids), c_f: JSON.stringify(c_f)};
		var dialogParams = {div_id: 'div_' + oper, width: 400, height: 200, title: 'Set Build Result', postData: postData};
		tool.actionDialog(dialogParams, url, undefined, function(){
			$(gridSelector).trigger('reloadGrid');
		});
	};
	
	$table.prototype.crInfo = function(event){
		var $this = this;
		var db = $this.getParams('db'), table = $this.getParams('table');
		var parent = event.data.parent, elemtn = event.data.id;
		var base = '#div_result_cr_summit_' + + event.data.id;
		if(!$(base + ' #div_summit_cr_edit').length){
			var url = '/jqgrid/jqgrid/db/' + db + '/table/' + table + '/oper/crInfo/parent/' + parent + '/element/' + element;
			$.get(url, 
				function(data, status){
					$(base).html(data);
				}
			);
		}
		else{
			if ($(event.data.resSelector + ' input[name="submit_a_cr[]"]').attr('checked') == 'checked'){
				$(base + ' #div_summit_cr_edit').show();
			}
			else{
				$(base + ' #div_summit_cr_edit').hide();
			}
		}
	};
	
	$table.prototype.setOneTester = function(id, gridSelector, divSelector, parent){
		var $this = this;
		var c_f = $(gridSelector).getCell(id, 'c_f');
		var db = $this.getParams('db'), table = $this.getParams('table');
		var selectedValue = $(gridSelector + " " + divSelector).val();
		var ids = new Array();
		ids[0] = id;
		var postData = {db:db, table:table, parent:parent, oper:'set_tester', element:JSON.stringify(ids), select_item:selectedValue, c_f:JSON.stringify(c_f)};
		$.post('/jqgrid/jqgrid', postData, function(data){
			if(tool.handleRequestFail(data) == false){
				//返回链接状态
				if(data == 'success'){
					$(gridSelector).setRowData(id, {tester_id:selectedValue});
				}
			}
		});
	}
	
	$table.prototype.view_edit_afterSave = function(divId, id, p_id, data){
		$('#' + divId).setRowData(id, data);
	}
	
	$table.prototype.addcase = function(params){
		$this = this;
tool.debug(params);
		var url = '/jqgrid/jqgrid/db/' + params.db + '/table/' + params.table + '/oper/addcase/parent/' + params.parent;	
		var div_id = 'div_addcase_normal_req';
		var dialogParams = {
			div_id: div_id, 
			width: 600, 
			height: 200, 
			title: 'Select Case Add Type',
			close: function(){$(this).remove();},
			buttons: {
				Ok: function(){
					var type = tool.getAllInput('div#' + div_id)['data']['case_add_type'];
					switch(type){
						case 'case_list':
							$this.addCaseFromQueryList(params);
							$(this).dialog( "close" );
							break;
						case 'cycle_list':
							$this.addCaseFromOtherCycle(params);
							$(this).dialog( "close" );
							break;
					}
				},
				Cancel:function(event, ui){
					$(this).remove();
				}
			}
		};
		tool.actionDialog(dialogParams, url);
	}
	
	$table.prototype.addCaseFromQueryList = function(params){
		var db = params.db;
		var table = params.table;
		var target_table = 'testcase';
		var div_id = 'div_case_add';
		
		var dialog_params = {
			div_id: div_id,
			title: 'Add Case From Case List To Current Cycle',
			height:600,
			width: 1024,
			modal: true,
			autoOpen:false,
			close: function(){$(this).remove();},
			open: function(){
				var grid = grid_factory.get(db, target_table, {container: div_id});
				grid.ready();
tool.debug(grid.getParams());
				var base = grid.getParams('base');
				var condSelector = grid.getParams('conditionSelector');
				var c_aSelector = base + "_cond_advanced";
				var prj_id = $(params.tabSelector + ' #prj_id').val();
				var testcase_type_id = $(params.tabSelector + ' #testcase_type_id').val();
				//process default params
				$(condSelector + " #prj_id").attr('value', prj_id).attr('disabled',true);
				$(condSelector + " #testcase_type_id").attr('value', testcase_type_id).attr('disabled',true);
				$(condSelector + " " + c_aSelector + ' input[name="edit_status_id"]').attr('disabled',true);
				$(condSelector + " #isactive").attr('value', '1').attr('disabled',true);
				
				var divSelector = 'div' + condSelector;
				//var url = '/jqgrid/jqgrid/oper/linkage/db/' + db + '/table/testcase_module';
				//get case_module
				$this.setLinkage(divSelector, ['select#testcase_type_id'], {testcase_type_id: testcase_type_id});
				
				$("#" + div_id).hide();
				$(base + "_query_add").remove();
				$("#" + div_id).show();
			}
		};
		//enable button
		var buttonParams = {db: params.db, table: table, target_table: target_table, container: dialog_params.div_id, 
				refeshTab: params.refeshTab, parent: params.parent, tabSelector: params.tabSelector, flag: 0};
		dialog_params['buttons'] = $this.addCaseButtons(buttonParams);
		//enable dialog
		var url = '/jqgrid/index/db/' + db + '/table/' + target_table + '/container/' + dialog_params.div_id;
		var dialog_params = $.extend(true, dialog_params, {html_type:'url', text:url});
		return tool.actionDialog(dialog_params, url);	
	};
	
	$table.prototype.addCaseFromOtherCycle = function(params){
tool.debug(params);
		var $this = this;
		var db = params.db;
		var table = params.table;
		var div_id = 'div_new_case_add';
	
		var dialog_params = {
			div_id: div_id,
			title: 'Add Case From Other Cycel To Current Cycle',
			height:600,
			width: 1024,
			modal: true,
			autoOpen:false,
			close: function(){$(this).remove();},
			open: function(){
				var grid = grid_factory.get(db, table, {container: div_id});
				grid.ready();
tool.debug(grid.getParams());
				var divCond = 'div' + grid.getParams('conditionSelector');
				var prj_id = $(params.tabSelector + ' #prj_id').val();
				var os_id = $(params.tabSelector + ' #os_id').val();
				$(divCond + " #os_id").attr('value', os_id).attr('disabled', true);
				$(divCond + " #prj_id").attr('value', prj_id);
				var source = ['select#os_id', 'select#board_type_id', 'select#chip_id', 'select#prj_id', 'select#cycle_id', 'select#testcase_module_id', 'select#creater_id'];
				$this.setLinkage(divCond, source, {os_id: os_id, prj: prj_id});;
				$(divCond + " #query_add").remove();
				$(divCond + " #query_remove").remove();
			}
		};
		// process button
		var buttonParams = {db: db, target_table: table, table: table, container: dialog_params.div_id, 
				refeshTab: params.refeshTab, parent: params.parent, tabSelector: params.tabSelector, flag: 1};
		dialog_params['buttons'] = $this.addCaseButtons(buttonParams);
		// enable dialog
		var url = '/jqgrid/index/db/' + db + '/table/' + table + '/container/' + dialog_params.div_id + "/parent/" + params.parent;
		var dialog_params = $.extend(true, dialog_params, {html_type:'url', text:url});	
		return tool.actionDialog(dialog_params, url);	
	};
	
	$table.prototype.addCaseButtons = function(params){
		var fun_addcase = function(flag, replaced){
			var c_f = '';
			var ver_ids = '';
			var oper = 'newaddcase';
			var cell = new Array();
			var base = "#" + params.container + '_' + params.db + '_' + params.table;
			var d_condId = base + '_cond';
			var d_listSelector = base + '_list';
			var gridSelector =  "#" + params.container + '_' + params.db + '_' + params.target_table + "_list";
			var prj_id = $("#" + params.container + " #prj_id").val();
			var selectedRows = $(gridSelector).getGridParam('selarrrow');
			var data = {element: selectedRows, parent: params.parent, replaced: true};								
			if(flag === 0){
				var ver_id = new Array();
				$.each(selectedRows, function(i, val) {	
					ver_id[i] =  $(gridSelector).getCell(val, 'ver_ids');
				});	
				ver_ids = JSON.stringify(ver_id);
				oper = 'addcase';
tool.debug(ver_ids);
			}
			else if(flag === 1){
				$.each(selectedRows, function(i, item){
					cell[i] = $(gridSelector).getCell(item, 'c_f');
				});
				c_f = JSON.stringify(cell);
tool.debug(c_f);
			}
			var postData = {
				db: params.db, 
				table: params.table, 
				oper: oper, 
				element: JSON.stringify(selectedRows), 
				parent: params.parent, 
				replaced: replaced, 
				c_f: c_f, 
				ver_ids: ver_ids,
			};
tool.debug(postData);
			var item = tool.getAllInput(d_condId)['data'];
tool.debug(item);
			$.post('/jqgrid/jqgrid', $.extend(postData, item), function(data){
				if(tool.handleRequestFail(data))return;
				// if($(d_listSelector).attr('class') == 'scroll'){
					var grid = grid_factory.get(params.db, params.table, {container: params.refeshTab, parent: params.parent});
					var filters = JSON.stringify({groupOp:'AND', rules:[{"field":"cycle_id","op":"eq","data":params.parent}]});				
					//grid.setParams($.extend(true, params, {p_id:params.parent, parent:params.parent, filters:filters}));
					grid.indexInDiv({filters:filters});
				// }
				// else
					// $(d_listSelector).trigger('reloadGrid');
			});
		};
		
		var buttons = {
			"Add&Replace the cases": function(){
				fun_addcase(params.flag, 1);
				$(this).dialog( "close" );
			},
			"Add&NOT-Replace the cases": function() {
				fun_addcase(params.flag, 0);
				$(this).dialog( "close" );
			},
			Cancel: function() {
				$(this).dialog( "close" );
			}
		};
		return buttons;
	};
	
	$table.prototype.linkage = function(source, linked, srcValue, params){
		params = params || {};
		var fun_oneLink = function(target, params, source_val){
			//解析params，如果其中存在selector，则将其值都读出来
			var newParams = {field:target.field, value:source_val};
			newParams.cond = target.cond;
			if(params.selector != undefined){
				for(var i in params.selector){
					newParams[i] = $(params.selector[i]).val();
				}
//				delete params.selector;
			}			
			$.post(target.url, newParams, function(data){
				if(tool.handleRequestFail(data))return;
				if (data.nochange != undefined && data.nochange == 1)
					return;
				if (target.type == undefined)
					target.type = 'select';
				switch(target.type){
					case 'select':
						var currentVal = $(target.selector).val();
						$(target.selector).find('option').remove();
						tool.generateOptions($(target.selector), data, 'id', 'name', true);
						$(target.selector).val(currentVal);
						break;
					case 'checkbox':
						break;
					case 'radio':
						break;
					case 'text':
						break;
				}
			}, 'json');
		};
tool.debug('value:' + srcValue);
		var fun_linkage = function(event){
			for(var i in event.data.linked){
				fun_oneLink(event.data.linked[i], event.data.params, srcValue);
			}
		};
		$(source.selector).each(function(i){
			fun_linkage({data:{linked:linked, params:params}});
		});
	};
	
	$table.prototype.setLinkage = function(divSelector, sources, srcValue){
tool.debug('srcValue');
tool.debug(srcValue);
		var $this = this;
		var db = $this.getParams('db'), table = $this.getParams('table'), parent = $this.getParams('parent');
		sources = sources || ['select#os_id', 'select#board_type_id', 'select#chip_id'];
		var os_chip_board_type = {os_id:divSelector + ' select#os_id', chip_id:divSelector + ' select#chip_id', board_type_id:divSelector + ' select#board_type_id'};
		for(var i in sources){
			switch(sources[i]){
				case 'select#os_id':
					var target = [{selector:divSelector + ' select#testcase_type_id', type:'select', field:'os_id', cond:'REGEXP', url:'/jqgrid/jqgrid/oper/linkage/db/' + db + '/table/testcase_type'},
						{selector:divSelector + ' select#prj_id', type:'select', field:'os_id', url:'/jqgrid/jqgrid/oper/linkage/db/' + db + '/table/zzvw_prj'},
						{selector:divSelector + ' select#compiler_id', type:'select', field:'os_ids', url:'/jqgrid/jqgrid/oper/linkage/db/' + db + '/table/compiler'},
						{selector:divSelector + ' select#chip_id', type:'select', field:'os_id', url:'/jqgrid/jqgrid/oper/getchip/db/' + db + '/table/zzvw_prj'},
						{selector:divSelector + ' select#board_type_id', type:'select', field:'os_id', url:'/jqgrid/jqgrid/oper/getboardtype/db/' + db + '/table/zzvw_prj'}];
					var srcDiv = {selector:divSelector + ' select#os_id'};
					var params = {selector:os_chip_board_type};
					if(srcValue != undefined && srcValue.os_id != undefined)
						var value = srcValue.os_id;
					break;
				case 'select#board_type_id':
					var target = [{selector:divSelector + ' select#prj_id', type:'select', field:'board_type_id', url:'/jqgrid/jqgrid/oper/linkage/db/' + db + '/table/zzvw_prj'},
						{selector:divSelector + ' select#chip_id', type:'select', field:'board_type_id', url:'/jqgrid/jqgrid/oper/getchip/db/' + db + '/table/zzvw_prj'},
						{selector:divSelector + ' select#os_id', type:'select', field:'board_type_id', url:'/jqgrid/jqgrid/oper/getos/db/' + db + '/table/zzvw_prj'}];
					var srcDiv = {selector:divSelector + ' select#board_type_id'};
					var params = {selector:os_chip_board_type};
					if(srcValue != undefined && srcValue.board_type_id != undefined)
						var value = srcValue.board_type_id;
					break;
				case 'select#chip_id':
					var target = [{selector:divSelector + ' select#prj_id', type:'select', field:'chip_id', url:'/jqgrid/jqgrid/oper/linkage/db/' + db + '/table/zzvw_prj'},
						{selector:divSelector + ' select#board_type_id', type:'select', field:'chip_id', url:'/jqgrid/jqgrid/oper/getboardtype/db/' + db + '/table/zzvw_prj'},
						{selector:divSelector + ' select#os_id', type:'select', field:'chip_id', url:'/jqgrid/jqgrid/oper/getos/db/' + db + '/table/zzvw_prj'}];
					var srcDiv = {selector:divSelector + ' select#chip_id'};
					var params = {selector:os_chip_board_type};
					if(srcValue != undefined && srcValue.chip_id != undefined)
						var value = srcValue.chip_id;
					break;
				case 'select#prj_id':	
					var target = [{selector:divSelector + ' select#testcase_type_id', type:'select', url:'/jqgrid/jqgrid/oper/getCycleType/db/' + db + '/table/' + table + '/parent/' + parent},
						{selector:divSelector + ' select#cycle_id', type:'select', url:'/jqgrid/jqgrid/oper/getCycle/db/' + db + '/table/' + table + '/parent/' + parent}];
					var srcDiv = {selector:divSelector + ' select#prj_id'};
					var params = {};
					if(srcValue != undefined && srcValue.prj_id != undefined){
						var value = srcValue.prj_id;
						target = [{selector:divSelector + ' select#testcase_type_id', type:'select', url:'/jqgrid/jqgrid/oper/getCycleType/db/' + db + '/table/' + table + '/parent/' + parent},
							{selector:divSelector + ' select#cycle_id', type:'select', url:'/jqgrid/jqgrid/oper/getCycle/db/' + db + '/table/' + table + '/parent/' + parent},
							{selector:divSelector + ' select#creater_id', type:'select', url:'/jqgrid/jqgrid/oper/getCreater/db/' + db + '/table/' + table + '/parent/' + parent},
							{selector:divSelector + ' select#prj_id', type:'select', url:'/jqgrid/jqgrid/oper/getPrj/db/' + db + '/table/' + table + '/parent/' + parent}
						];
					}
					break;
				case 'select#cycle_id':
					//var module_priority_testor = {testcase_module_id:divSelector + ' select#testcase_module', testcase_priority_id:divSelector + ' select#testcase_priority', tester_id:divSelector + ' select#tester_id'};
					var target = [{selector:divSelector + ' select#testcase_module_id', type:'select', url:'/jqgrid/jqgrid/oper/getModule/db/' + db + '/table/' + table + '/parent/' + parent},
						{selector:divSelector + ' select#tester_id', type:'select', url:'/jqgrid/jqgrid/oper/getTesters/db/' + db + '/table/zzvw_cycle'},
						{selector:divSelector + ' select#testcase_priority_id', type:'select', url:'/jqgrid/jqgrid/oper/getPriority/db/' + db + '/table/' + table + '/parent/' + parent},
						{selector:divSelector + ' select#auto_level_id', type:'select', url:'/jqgrid/jqgrid/oper/getAutoLevel/db/' + db + '/table/' + table + '/parent/' + parent},
					];
					var srcDiv = {selector:divSelector + ' select#cycle_id'};
					var params = {};
					if(srcValue != undefined && srcValue.cycle_id != undefined)
						var value = srcValue.cycle_id;
					break;
				case 'select#testcase_module_id':
					var target = [{selector:divSelector + ' select#testcase_testpoint_id', type:'select', field:'testcase_module_id', url:'/jqgrid/jqgrid/oper/linkage/db/xt/table/testcase_testpoint'}];
					var srcDiv = {selector:divSelector + ' select#testcase_module_id'};
					var params = {};
					if(srcValue != undefined && srcValue.testcase_module_id != undefined)
						var value = srcValue.testcase_module_id;
					break;
				case 'select#creater_id':
					var target = [{selector:divSelector + ' select#cycle_id', type:'select', field:'creater_id', url:'/jqgrid/jqgrid/oper/getCreaterCycle/db/' + db + '/table/' + table + '/parent/' + parent}];
					var srcDiv = {selector:divSelector + ' select#creater_id'};
					var params = {};
					if(srcValue != undefined && srcValue.creater_id != undefined)
						var value = srcValue.creater_id;
					break;
				case 'select#testcase_type_id':
					var target = [{selector:divSelector + ' select#testcase_module_id', type:'select', field:'testcase_type_ids', cond:'REGEXP', url:'/jqgrid/jqgrid/oper/linkage/db/xt/table/testcase_module'}];
					var srcDiv = {selector:divSelector + ' select#testcase_type_id'};
					var params = {};
					if(srcValue != undefined && srcValue.testcase_type_id != undefined){
						var value = srcValue.testcase_type_id;
						if(srcValue.field == 'testcase_type_id')
							target = [{selector:divSelector + ' select#testcase_module_id', type:'select', field:'testcase_type_id', url:'/jqgrid/jqgrid/oper/getCaseModule/db/' + db + '/table/' + table}];
					}
					break;	
			}
			tool.linkage(srcDiv, target, params);
tool.debug('os_id:' + value);
			if(value != undefined){
				$this.linkage(srcDiv, target, value, params);
				value = undefined;
			}
		}
	};
	
	$table.prototype.query = function(){
		$this = this;
		var container = $this.getParams('container');
		if(container == 'div_new_case_add'){
			var cycle_id = tool.getAllInput("#div_new_case_add")['data']['cycle_id'];
			if(cycle_id == ''){
				alert("Pls select a cycle");
				return;
			}
		}
		$table.supr.prototype.query.call(this);
	}
}());
