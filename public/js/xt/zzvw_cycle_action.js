// JavaScript Document
(function(){
	var DB = KF_GRID_ACTIONS.xt;
	
	const OS_MQX = '9', OS_PSDK = '11', OS_USB = '12', OS_KINETICS = '14';
	
	DB.zzvw_cycle = function(grid){
		$table.supr.call(this, grid);
	};

	var $table = DB.zzvw_cycle;
	var tool = new kf_tool();
	tool.extend($table, gc_grid_action);
	
	$table.prototype.ready = function(base){
		var conditionSelector = this.getParams('conditionSelector');
		$table.supr.prototype.ready.call(this, base);
		this.setLinkage('div' + conditionSelector);
	};

	$table.prototype.getAutoCompleteField = function(){
		return [{input:'name', field:'name'}];
	};

	$table.prototype.infoBtnActions = function(action, p){
		var $this = this;
		var gridId = $this.getParams('gridId');
		var divId = p.divId;
		var element_id = $('#' + divId + " #div_hidden #element_id").val();
		var params = { 
			db: $this.getParams('db'), 
			table: $this.getParams('table'), 
			real_table: $this.getParams('table') + "_detail", 
			gridid: gridId,
			cycle_id: element_id,
			tabSelector: '#' + p.divId
		};
		switch(action){
			// case 'addcase':
				// $this.addcase(params);
				// break;
			
			case 'freeze':
				$this.freeze(params);
				break;
				
			case 'unfreeze':
				$this.unfreeze(params);
				break;
				
			case 'uploadfile':
				$this.imports(params);
				break;
		
			case 'script':
				$this.script(params);
				break;
				
			case 'view_edit_export':
				$this.view_edit_export(params);
				break;
				
			case 'generate_report':
				$this.generateReport(params);
				break;
			
			default:
				// alert(action);
				return $table.supr.prototype.infoBtnActions.call(this, action, p);
		}
	};

	$table.prototype.getGridsForInfo = function(divId){
		var grids = [
			{tab:'cycle_detail', container:'cycle_detail', table:'zzvw_cycle_detail', params:{real_table:'cycle_detail'}},
			{tab:'cycle_stream', container:'cycle_stream', table:'zzvw_cycle_detail_stream', params:{real_table:'cycle_detail'}},
		];
		return grids;
	};
	
	$table.prototype.information_open = function(divId, element_id, pageName){
		var $this = this;
		var gridId = $this.getParams('gridId');
		var tabSelector = "#" + divId;
		//执行父类函数
		pageName = pageName || 'all';
		$table.supr.prototype.information_open.call(this, divId, element_id, pageName);
		
		var div = tabSelector + " #view_edit_" + element_id + " #div_view_edit";
		$(div + " #tag").attr('disabled', true);
		$(div + " #template").attr('disabled', true);
		$(div + " #tag").parent().parent().hide();
		$(div + " #template").parent().parent().hide();	
// tool.debug($(div + " #assistant_owner_id").attr("value"));
		if($(div + " #assistant_owner_id").attr("value") == 0){
			var db = $this.getParams('db'), table = $this.getParams('table');
			$(div + " #assistant_owner_id").unbind('click').bind('click', {div:div}, function(event){
				var inputs = tool.getAllInput(div);
				var tester_ids = inputs.data['tester_ids'];
				if(tester_ids.length > 1){
	// tool.debug(tester_ids.length);
					$.post("/jqgrid/jqgrid", {db:db, table:table, oper:"getAssisOwner", element:element_id, tester_ids:tester_ids}, function(data){
						if(tool.handleRequestFail(data))return;
						$(div + " #assistant_owner_id").find('option').remove();
						var options = tool.str2Array(JSON.parse(data));
// tool.debug(options);
						tool.generateOptions($(div + " #assistant_owner_id"), options, 'id', 'name', true);
						$(div + " #assistant_owner_id").unbind('click');
					})
					
				}
			});
		}
		$this.myname(div);
		$this.setLinkage('div' + div);
		$this.linkage(div);
	};
	
	$table.prototype.linkage = function(div){
		var generateCycleName = function(event){
			var div = event.data.div; 
			var prj = $(div + " #prj_id").find("option:selected").text(); 
			var cycle_type = $(div + " #cycle_type_id").find("option:selected").text();
			var week = $(div + " #week").find("option:selected").text(); 
			var rel = $(div + " #rel_id").find("option:selected").text(); 
			var myname = $(div + " #myname").attr("value");
			var cycle_name = prj + '-' + cycle_type + '-' + rel + '-' + week + '-' + myname ;
			$(div + " #name").val(cycle_name);
			tool.checkUnique(div + " #name", {})
		};
		var showHideFields = function(event){
			var div = event.data.div;
			var os_id = $(div + ' #os_id').val();
			var fields = ['compiler_id', 'build_target_id'];
			var show = false;
			
			switch(os_id){
				case OS_MQX:
				case OS_PSDK:
				case OS_USB:
				case OS_KINETICS:
// tool.debug('aaa')				;
					show = true;
					break;
				default:
					break;
			}
// alert('>>os_id = ' + os_id + ', div = ' + div + ', show = ' + show + '<<');
			$.each(fields, function(i, n){
				if(show)
					$(div + ' #ces_tr_' + n).show();
				else
					$(div + ' #ces_tr_' + n).hide();
			});
			
		};
		$(div + " #prj_id").unbind('change', generateCycleName).bind('change', {div:div}, generateCycleName);
		$(div + " #rel_id").unbind('change', generateCycleName).bind('change', {div:div}, generateCycleName);
		$(div + " #cycle_type_id").unbind('change', generateCycleName).bind('change', {div:div}, generateCycleName);
		$(div + " #week").unbind('change', generateCycleName).bind('change', {div:div}, generateCycleName);
		$(div + " #myname").unbind('keyup', generateCycleName).bind('keyup', {div:div}, generateCycleName);
		$(div + " #os_id").unbind('change', showHideFields).bind('change', {div:div}, showHideFields);
	};
	
	$table.prototype.myname = function(div){
		var cycleName = $(div + ' #name').attr("value");
		if(cycleName != '' && cycleName != undefined){
			var name = cycleName.match(/^.*-\d{2}WK(\d{2})-(.*)$/);
			if(name != undefined){
				var week = name[1];
				var myname = name[2];
				$(div + ' #myname').attr("value", myname).attr("original_value", myname);
				$(div + ' #week').attr("value", week).attr("original_value", week);
			}
		}
	};
	
	$table.prototype.view_edit_edit = function(p){
		var $this = this;
		var divId = p.divId;
		var dialog = $('#' + divId);
		var db = dialog.find('#div_hidden #db').attr('value');
		var cycle_id = dialog.find('#div_hidden #id').attr('value');	
		var view_edit_selector = "#view_edit_" + cycle_id;
		var os_id = dialog.find(view_edit_selector + ' #os_id').val();
		var divSelector = '#' + divId + " " + view_edit_selector;
		
		$table.supr.prototype.view_edit_edit.call(this, p);
		
		// dialog.find('ul li:eq(1)').attr('disabled', true);
		$('#' + divId).tabs('option', 'disabled', [1,2]);
		var disabled = {freeze:true, unfreeze:true, addcase:true, uploadfile:true, script:true};
// tool.debug(disabled);
		$.each(disabled, function(key, value){
			dialog.find(view_edit_selector + ' #' + key).attr('disabled', value);
		});
		$this.linkage('div' + divId);
		
		$this.cyclelink('/jqgrid/jqgrid/oper/linkage/db/' + db + '/table/compiler', divSelector + ' select#compiler_id', os_id, 'os_ids');
		$this.cyclelink('/jqgrid/jqgrid/oper/linkage/db/' + db + '/table/build_target', divSelector + ' select#build_target_id', os_id, 'os_ids');
		$this.cyclelink('/jqgrid/jqgrid/oper/linkage/db/' + db + '/table/testcase_type', divSelector + ' select#testcase_type_id', os_id, 'os_ids');
	}
	
	$table.prototype.view_edit_cloneit = function(p){
		var $this = this;
// tool.debug($this.getParams());
		var divSelector = '#' + p.divId;
		var dialog = $(divSelector);
		var db = dialog.find('#div_hidden #db').attr('value');
		var table = dialog.find('#div_hidden #table').attr('value');
		var cycle_id = dialog.find('#div_hidden #id').attr('value');
		var view_edit_selector = "#div_view_edit";//不要加上view_edit_ + id
		var oper = 'addtional';
		
		$table.supr.prototype.view_edit_cloneit.call(this, p);

		$(divSelector).tabs('option', 'disabled', [1,2]);
		var disabled = {os_id:false, board_type_id:false, chip_id:false, prj_id:false, rel_id:false, testcase_id:false, test_env_id:false, 
			freeze:true, unfreeze:true, addcase:true, uploadfile:true, script:true};
		$.each(disabled, function(key, value){
			dialog.find(view_edit_selector + ' #' + key).attr('disabled', value);
		});
		$this.linkage('div' + p.divId);
		
		var url = '/jqgrid/jqgrid/db/' + db + '/table/' + table + '/oper/' + oper + '/element/' + cycle_id;
		$.get(url, function(data, status){
			if(tool.handleRequestFail(data))return;
			var oper = 'codecStreamClone';
			var input_csc = 'input[name="codec_stream_clone[]"]';
			var div_acce = '#div_addtional_codec_clone_edit';
		
			dialog.find(view_edit_selector).append(data);
			dialog.find(input_csc).unbind('change').bind('change', function(){	
				if (dialog.find(input_csc).attr('checked') == 'checked'){
					var id = dialog.find(div_acce).attr('id');
// tool.debug(id);
					if(typeof id == "undefined"){
						var c_url = '/jqgrid/jqgrid/db/' + db + '/table/' + table + '/oper/' + oper + '/element/' + cycle_id;
						$.get(c_url, function(datas, status){
							if(tool.handleRequestFail(data))return;
							dialog.find(view_edit_selector).append(datas);
						});
					}	
					else{
						dialog.find(div_acce).show();
					}
						
				}
				else{
					dialog.find(div_acce + ' :input').not(':disabled').each(function(i){
						var original_value = $(this).attr('original_value');
						switch($(this).attr('type')){
							case 'button':
								break;
							case 'checkbox':
								if(original_value == '1')
									$(this).attr('checked', true);
								else if (original_value == '0')
									$(this).attr('checked', false);
								break;
							case 'select':
							default:
								$(this).val(original_value);
								break;
						}
					});
					dialog.find(div_acce).hide();
				}
			});
		});
	}
		
	$table.prototype.view_edit_cancel = function(p){
		var $this = this;
		var divId = p.divId;
		var dialog = $('#' + divId);
		
		$table.supr.prototype.view_edit_cancel.call(this, p);
		$('#' + divId).tabs('option', 'disabled', []);
		
		var disabled = {freeze:false, unfreeze:false, addcase:false, uploadfile:false, script:false};
		$.each(disabled, function(key, value){
			dialog.find('#div_button_edit #' + key).attr('disabled', value);
		});

		dialog.find('#div_view_edit #div_addtional_clone_edit,#div_addtional_codec_clone_edit').remove();
	}
	
	$table.prototype.view_edit_save = function(p){	
		var $this = this;
		var divId = p.divId;
		var dialog = $('#' + divId);
		var cloneit = dialog.find('#div_hidden #clone').val();
		var oper = (cloneit == "true") ? 'cloneit':'save';
		
		$table.supr.prototype.view_edit_save.call(this, p);
// tool.debug(oper);
		$('#' + divId).tabs('option', 'disabled', []);
		var disabled = {freeze:false, unfreeze:false, addcase:false, uploadfile:false, script:false};
		$.each(disabled, function(key, value){
			dialog.find('#div_button_edit #' + key).attr('disabled', value);
		});
		dialog.find('#div_view_edit #div_addtional_clone_edit,#div_addtional_codec_clone_edit').remove();
	}
	
	$table.prototype.view_edit_afterSave = function(divId, id, p_id, data){
		var $this = this;
// tool.debug("xxxxxxxxxxxx" + divId);
		var p = this.getParams(['db', 'table', 'container']);
		var dialog = $('#' + divId);
		var element_id = dialog.find('#div_hidden #id').val();
		window.location = '/jqgrid/jqgrid/newpage/1/db/' + p.db + '/table/' + p.table + '/oper/information/element/' + element_id + '/parent/0';
		// window.location.reload();
	};
	
	$table.prototype.freeze = function(params){
		var $this = this;
		$.post('/jqgrid/jqgrid', {db:params.db, table:params.table, element:JSON.stringify([params.cycle_id]), oper:'freeze'}, 
			function(data, status){
				if(tool.handleRequestFail(data))return;
				$(params.tabSelector + ' #view_edit_' + params.cycle_id + ' #div_button_edit').html(data);
				$(params.tabSelector).tabs('destroy').tabs({selected: 'tabs-current'});
				$this.information_open(params.tabSelector.substr(1), params.cycle_id);
			}
		);
	};
	
	$table.prototype.unfreeze = function(params){
		var $this = this;
		$.post('/jqgrid/jqgrid', {db:params.db, table:params.table, element:JSON.stringify([params.cycle_id]), oper:'unfreeze'}, 
			function(data, status){
				if(tool.handleRequestFail(data))return;
				$(params.tabSelector + ' #view_edit_' + params.cycle_id + ' #div_button_edit').html(data);
				$(params.tabSelector).tabs('destroy').tabs({selected: 'tabs-current'});
				$this.information_open(params.tabSelector.substr(1), params.cycle_id);
			}
		);
	};
	
	$table.prototype.imports = function(params){
		var $this = this;
		var div_id = 'import_div';
		var dialog_params = {
			html_type: 'url',
			text: '/jqgrid/jqgrid/db/' + params.db + '/table/' + params.table + '/oper/import/element/' + params.cycle_id,
			div_id: div_id, 
			width: 900, 
			height: 450, 
			title: 'Upload',
			open: function(){
				$('#' + div_id + ' :button,:submit').button();
				$data = "<input id='element' type='hidden' name='element' value='" + params.cycle_id + "'>";
				$('#' + div_id + " #import_form").append($data);
			},
			close:function(){
				$(this).remove();
			}
		};
		tool.popDialog(dialog_params);
	};
	
	$table.prototype.view_edit_export = function(params){
		var url = '/jqgrid/jqgrid/db/' + params.db + '/table/' + params.table + '/oper/view_edit_export/element/' + params.cycle_id;
		tool.actionDialog({div_id:'view_edit_export_div', width:400, height:300, title:'Export'}, url, undefined, function(data){
			location.href = "/download.php?filename=" + encodeURIComponent(data) + "&remove=1";
		});
	};
	
	$table.prototype.generateReport = function(params){
		//var dialog = XT.waitingDialog();
		var $this = this;
		$.ajax({
			type:'POST', 
			url:'/jqgrid/jqgrid', 
			data:{db:params.db, table:params.table, table_name:params.table, oper:'report', element:params.cycle_id}, 
			success: function(data, status){
					if(tool.handleRequestFail(data))return;
					//dialog.dialog('close');
					if (data == 1004){ // not found the action
						alert("Sorry, this feature report is not implemented yet");
					}
					else{
						if(data){
							var datas = JSON.parse(data);
							location.href = "/download.php?filename=" + encodeURIComponent(datas['file_name']) + "&remove=1";
						}
						else
							alert("No Report");
					}
				},
			error: function(httpReq, textStatus, errorThrown){
				alert("Error!! " + textStatus);
			}
		});
	};
	
	$table.prototype.buttonActions = function(action, options){
		var $this = this;
		var ret = true;
		var params = $this.getParams();
		var db = params.db;
		var table = params.table;
// tool.debug(params);
		var gridSelector = params.gridSelector;
		var conditionSelector = params.conditionSelector;
		var selectedRows = $(gridSelector).getGridParam('selarrrow');
		var element = JSON.stringify(selectedRows);
		var postData = {db: db, table: table, element: element};
// tool.debug(action);		
		switch(action){
			case 'query_new':
				window.open('/jqgrid/jqgrid/newpage/1/oper/information/db/xt/table/zzvw_cycle/element/0');
				// $this.information(0, 1, 0);
				break;
			case 'freeze':
				if (tool.checkSelectedRows(selectedRows, 1)){
					postData.oper = action;
					postData.flag = 1;
					$.post('/jqgrid/jqgrid',postData, function(data, status){
						if(tool.handleRequestFail(data))return;
						$(gridSelector).trigger('reloadGrid');
					});
				}
				break;
			case 'clone':
				if (tool.checkSelectedRows(selectedRows, 1)){
					$this.clone(gridSelector, postData);
				}
				break;
			case 'set_group':
				if (tool.checkSelectedRows(selectedRows, 1)){
// tool.debug("set_group");
					var url = '/jqgrid/jqgrid/db/' + db + '/table/' + table + '/oper/set_group';	
					var postData = {element: element};
					var dialogParams = {div_id: 'div_set_group', width: 400, height: 200, title: 'Set Group', postData: postData};
					tool.actionDialog(dialogParams, url, undefined, function(){
						$(gridSelector).trigger('reloadGrid');
					});
				}
				break;
			case 'export':
				if (tool.checkSelectedRows(selectedRows, 1)){
					var url = '/jqgrid/jqgrid/db/' + db + '/table/' + table + '/oper/export';
					tool.actionDialog({div_id:'export_div', width:400, height:300, title:'Export', postData:{element: element}}, url, undefined, function(data){
// tool.debug(data);
						location.href = "/download.php?filename=" + encodeURIComponent(data) + "&remove=1";
					});
				};
			break;
			default:
				ret = $table.supr.prototype.buttonActions.call(this, action, options);						
		}
		return ret;
	};
	
	$table.prototype.clone = function(gridSelector, postData){
		var oper = 'cloneall';
		var div_id = 'div_clone_all';
		
		var dialog_params = {
			div_id: div_id,
			height: 300,
			width: 600,
			modal: true,
			autoOpen: false,
			close: function(){$(this).remove();},
			open: function(){
				$('#' + div_id + ' input[date="date"]').each(function(i){
					tool.datePick(this);
				});
			},
			buttons: {
				'Clone': function(){
					var dialog = $(this);
					var myname = $('#' + div_id + ' #myname').val();
					var checkData = postData;
					checkData = $.extend(true, checkData, {oper: 'checkMyname', myname: myname});
					
					$.post('/jqgrid/jqgrid', checkData, function(data){
						if(tool.handleRequestFail(data))return;
						if(data){
							var datass = JSON.parse(data);
							alert(datass + "\n" + "Has Already Exists!!" + "\n" + "Pls Change To Another Name!!!");
						}
						else{
							var inputs = tool.getAllInput('#' + div_id)['data'];
							postData = $.extend(true, postData, {oper: oper});
							postData = $.extend(true, postData, inputs);
							$.post('/jqgrid/jqgrid', postData, function(data, status){
								if(tool.handleRequestFail(data))return;
								alert('Clone Successfully');//判断？？？？？？
								$(gridSelector).trigger('reloadGrid');
							});
							dialog.dialog( "close" );
						}
					});
				},
				Cancel: function() {
					//è???
					$(this).dialog( "close" );				
				}
			}
		};
		var url = "/jqgrid/jqgrid/db/" + postData.db + "/table/" + postData.table + "/oper/" + oper;
		var dialogParams = $.extend(true, dialog_params, {html_type:'url', text:url});
		tool.actionDialog(dialogParams, url);
	};
	
	$table.prototype.exportConstrast = function(gridSelector, postData){
// tool.debug('xxx');
		var div_id = 'div_cycle_report';
		var report_type = 'report_type';
		var html = 
			'<div id="' + div_id + '">' + 
				'<table>' + 
					'<tr>' + 
						'<td>' +
							'<label for="report_type_contrast">' + 
								'<input type="radio" name="' + report_type + '" id="report_type_contrast" checked="checked" required="1" value="1">' + 
								'Contrast: Different Cycle Contrast' + 
							'</label>' + 
						'</td>' + 
					'</tr>' + 
					'<tr>' + 
						'<td>' +
							'<label for="report_type_contrast_oobt">' + 
								'<input type="radio" name="' + report_type + '" id="report_type_contrast_oobt" value="2" required="1">' + 
								'Contrast: Different Cycle Contrast( For OOBT )' + 
							'</label>' + 
						'</td>' + 
					'</tr>' + 
				'</table>' + 
			'</div>';
		var dialog_params = {
			width:400,
			height:300,
			autoOpen: false,
			title: 'Generate Reports',
			modal: true,
			buttons: {
				'Generate': function(){
					var input = tool.getAllInput('div#' + div_id)['data'];			
					var report_type = input[report_type];
					
					if(report_type == 1)
						oper = 'export_contrast';
					else
						oper = 'export_oobt';
					var postData = $.extend(true, postData, {oper: oper});
					var waitdialog = tool.waitingDialog();// this.popDialog('notice', {'text': "Processing......", 'title':"Waiting", 'buttonok':false});
					
					$.ajax({
						type: 'POST', 
						url: '/jqgrid/jqgrid', 
						data: postData, 
						success: function(data, status){
								waitdialog.dialog('close');
								if(tool.handleRequestFail(data))return;
								if (data == 1004){ // not found the action
									alert("Sorry, this feature " + action + " is not implemented yet");
								}
								else{
									if(data)
										location.href = "download.php?filename=" + encodeURIComponent(data) + "&remove=1";
									else
										alert("No Report");
								}
							},
						error: function(httpReq, textStatus, errorThrown){
							alert("Error!! " + textStatus);
						}
					});
					$(this).dialog('close');
					$(gridSelector).trigger('reloadGrid');
				},
				Cancel:function(){
					$(this).dialog('close');
					$(gridSelector).trigger('reloadGrid');
				}
			},
			close:function(event, ui){
				$(this).remove();
			}				
		}
		var dialog = $(html).dialog(dialog_params);
		dialog.dialog('open');
	}
	
	$table.prototype.exportCombine = function(gridSelector, conditionSelector, postData){
		var prj_id = $(conditionSelector + ' #prj_id').val();
		if(typeof prj_id != 'undefined' && prj_id != 0){
			var waitdialog = tool.waitingDialog();// this.popDialog('notice', {'text': "Processing......", 'title':"Waiting", 'buttonok':false});
			$.ajax({
				type: 'POST', 
				url: '/jqgrid/jqgrid', 
				data: postData, 
				success: function(data, status){
						waitdialog.dialog('close');
						if(tool.handleRequestFail(data))return;
						if (data == 1004){ // not found the action
							alert("Sorry, this feature " + action + " is not implemented yet");
						}
						else{
							if(data)
								location.href = "download.php?filename=" + encodeURIComponent(data) + "&remove=1";
							else
								alert("No Report");
						}
					},
				error: function(httpReq, textStatus, errorThrown){
					alert("Error!! " + textStatus);
				}
			});
			$(this).dialog('close');
			$(gridSelector).trigger('reloadGrid');
		}
		else
			alert("Pls Select Prj");
	}
	
	$table.prototype.cyclelink = function(url, selector, value, field){
		var newParams = {value:value, cond:'REGEXP', field:field};
		$.post(url, newParams, function(data){
			if(tool.handleRequestFail(data))return;
			var currentVal = $(selector).val();
			$(selector).find('option').remove();
			tool.generateOptions($(selector), data, 'id', 'name', true);
			$(selector).val(currentVal);
		}, 'json');
	};
	
	$table.prototype.setLinkage = function(divSelector, sources){
		var $this = this;
		var db = $this.getParams('db'), table = $this.getParams('table');
		sources = sources || ['select#os_id', 'select#board_type_id', 'select#chip_id', 'select#group_id'];
		var os_chip_board_type = {os_id:divSelector + ' select#os_id', chip_id:divSelector + ' select#chip_id', board_type_id:divSelector + ' select#board_type_id'};
		for(var i in sources){
			switch(sources[i]){
				case 'select#os_id':
					var target = [{selector:divSelector + ' select#testcase_type_id', type:'select', field:'os_ids', cond:'REGEXP', url:'/jqgrid/jqgrid/oper/linkage/db/' + db + '/table/testcase_type'},
								  {selector:divSelector + ' select#prj_id', type:'select', field:'os_id', url:'/jqgrid/jqgrid/oper/linkage/db/' + db + '/table/zzvw_prj'},
								  {selector:divSelector + ' select#compiler_id', type:'select', field:'os_ids', cond:'REGEXP', url:'/jqgrid/jqgrid/oper/linkage/db/' + db + '/table/compiler'},
								  {selector:divSelector + ' select#chip_id', type:'select', field:'os_id', url:'/jqgrid/jqgrid/oper/getchip/db/' + db + '/table/zzvw_prj'},
								  {selector:divSelector + ' select#board_type_id', type:'select', field:'os_id', url:'/jqgrid/jqgrid/oper/getboardtype/db/' + db + '/table/zzvw_prj'}];
					tool.linkage({selector:divSelector + ' select#os_id'}, target, {selector:os_chip_board_type});
					break;
				case 'select#board_type_id':
					var target = [{selector:divSelector + ' select#prj_id', type:'select', field:'board_type_id', url:'/jqgrid/jqgrid/oper/linkage/db/' + db + '/table/zzvw_prj'},
								  {selector:divSelector + ' select#chip_id', type:'select', field:'board_type_id', url:'/jqgrid/jqgrid/oper/getchip/db/' + db + '/table/zzvw_prj'},
								  {selector:divSelector + ' select#os_id', type:'select', field:'board_type_id', url:'/jqgrid/jqgrid/oper/getos/db/' + db + '/table/zzvw_prj'}];
					tool.linkage({selector:divSelector + ' select#board_type_id'}, target, {selector:os_chip_board_type});
					break;
				case 'select#chip_id':
					var target = [{selector:divSelector + ' select#prj_id', type:'select', field:'chip_id', url:'/jqgrid/jqgrid/oper/linkage/db/' + db + '/table/zzvw_prj'},
								  {selector:divSelector + ' select#board_type_id', type:'select', field:'chip_id', url:'/jqgrid/jqgrid/oper/getboardtype/db/' + db + '/table/zzvw_prj'},
								  {selector:divSelector + ' select#os_id', type:'select', field:'chip_id', url:'/jqgrid/jqgrid/oper/getos/db/' + db + '/table/zzvw_prj'}];
					tool.linkage({selector:divSelector + ' select#chip_id'}, target, {selector:os_chip_board_type});
				case 'select#group_id':
					var template = function(){
						var val = $(divSelector + ' select#group_id').val();
						if(val == 3 || val == 9){
							$(divSelector + " #tag").parent().parent().show();
							$(divSelector + " #template").parent().parent().show();
							$(divSelector + " #tag").attr('disabled', false);
							$(divSelector + " #template").attr('disabled', false);
						}
						else{
							$(divSelector + " #tag").parent().parent().hide();
							$(divSelector + " #template").parent().parent().hide();
							$(divSelector + " #tag").attr('disabled', true);
							$(divSelector + " #template").attr('disabled', true);
						}
					}
					$(divSelector + ' select#group_id').unbind('change', template).bind('change', template);
					break;
			}
		}
	};
}());
