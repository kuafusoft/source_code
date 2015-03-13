// JavaScript Document
(function(){
	var DB = KF_GRID_ACTIONS.xt;
	var tool = new kf_tool();
	tool.loadFile('/js/xt/tc_ver_comm_action.js', 'js');
	
	DB.testcase = function(grid){
		$table.supr.call(this, grid);
		this.comm_action = new tc_ver_comm_action(this);
	};

	var $table = DB.testcase;
	tool.extend($table, gc_grid_action);
	
	function resetCart(base){
		// reset the cart onclick function
		$('#cart_add_prj_id').removeAttr('onclick').unbind('click').bind('click', function(){
			var os_id = $(base + ' #os_id').val(), board_type_id = $(base + ' #board_type_id').val(), chip_id = $(base + ' #chip_id').val();
			var rules = [], cart_data = {};
			if (os_id > 0)
				rules.push({field:'os_id', op:'eq', data:os_id});
			if (board_type_id > 0)
				rules.push({field:'board_type_id', op:'eq', data:board_type_id});
			if (chip_id > 0)
				rules.push({field:'chip_id', op:'eq', data:chip_id});
			if (rules.length > 0)
				cart_data.filters = JSON.stringify({groupOp:'AND', rules:rules});
			tool.selectToCart('prj_id', 'xt', 'prj', 'Project', cart_data);
		});
		$('#cart_add_testcase_module_id').removeAttr('onclick').unbind('click').bind('click', function(){
			var testcase_type_id = $(base + ' #testcase_type_id').val();
			var rules = [], cart_data = {};
			if (testcase_type_id > 0)
				rules.push({field:'testcase_type_id', op:'eq', data:testcase_type_id});
			if (rules.length > 0)
				cart_data.filters = JSON.stringify({groupOp:'AND', rules:rules});
			tool.selectToCart('testcase_module_id', 'xt', 'testcase_module', 'Module', cart_data);
		});
	}
	
	$table.prototype.ready = function(base){
		$table.supr.prototype.ready.call(this, base);
		
		base = '#' + base + '_cond';
		tool.datePick('div' + base + ' #last_run');
		this.setLinkage(base, ['select#os_id', 'select#board_type_id', 'select#chip_id', 'select#testcase_type_id', 'select#testcase_module_id']);
		
		resetCart(base);
	};
	
	$table.prototype.getAutoCompleteField = function(){
		return [{input:'key', field:'code'}];
	};

	// $table.prototype.getGridsForInfo = function(divId){
		// var ver_id = $('#' + divId + ' #div_view_edit #ver_id').val();
		// var grids = [
			// {tab:'edit_history', container:'edit_history', table:'testcase_ver'},
			// {tab:'test_history', container:'test_history', table:'zzvw_cycle_detail', params:{real_table:'cycle_detail'}},
			// //{tab:'srs_cover', container:'srs_cover', table:'prj_srs_node_testcase'},
			// // {tab:'view_edit', container:'steps', table:'testcase_ver_step', rules:[{"field":"testcase_ver_id","op":"eq","data":ver_id}]}
		// ];
		// return grids;
	// };
	
	$table.prototype.bindEventsForInfo = function(divId, rowId){
		var base = 'div#' + divId + ' #div_view_edit';
		this.setLinkage(base, ['select#testcase_type_id', 'select#testcase_module_id']);
	};
	
	$table.prototype.information_open = function(divId, element_id, pageName){
		pageName = pageName || 'all';
		$table.supr.prototype.information_open.call(this, divId, element_id, pageName);
		//调整编辑UI的比例
// tool.debug(divId);
		// var t1 = $('#' + divId + " #div_view_edit table.ces:first"), t2 = $('#' + divId + " #div_view_edit table.ces:nth-child(2)");
		// t1.find("th.ces:first").width('10%');
		// t2.find("th.ces:first").width('10%');
		// if(element_id != 0){
			// var db = 'xt';
			// var step_postData = {db:db, table:'testcase_ver_step', 'parent':ver_id};

			// //加载Version Steps
			// if (pageName == 'all' || pageName == 'view_edit'){
				// var step_grid = grid_factory.get('xt', 'testcase_ver_step', {container:'steps_' + element_id});
				// step_postData.filters = JSON.stringify({groupOp:'AND', rules:[{"field":"testcase_ver_id","op":"eq","data":ver_id}]});
				// step_grid.load(step_postData);
			// }
		// }

		// $('#edit_history #btn #diff').button().unbind().bind('click', function(){
			// var vers = [];
			// var i = 0;
			// $('#edit_history [name="id"]:checked').each(function(){
				// vers[i ++] = $(this).val();
			// });
			// if (vers.length == 0)
				// alert("Please select at most 1 item");
			// else
				// xt.testcase_ver.ver_diff(vers);
		// });
		// $('#hide_same').unbind().bind('click', {selector:'#ver_diff tr.jqgrow'}, XT.hideTheSame);
		return true;	
		
	};

	$table.prototype.infoBtnActions = function(action, p){
		switch(action){
			case 'view_edit_ask2review':
				testcase_id = $('#' + p['divId'] + ' #div_view_edit #node_id').val();
				ver_id = $('#' + p['divId'] + ' #div_view_edit #ver_id').val();
				this.comm_action._ask2review(testcase_id, ver_id);
				break;
				
			case 'view_edit_publish':
				testcase_id = $('#' + p['divId'] + ' #div_view_edit #node_id').val();
				ver_id = $('#' + p['divId'] + ' #div_view_edit #ver_id').val();
				this.comm_action._publish(testcase_id, ver_id, 'testcase_ver');
				break;
					
			case 'view_edit_coversrs':
				testcase_id = $('#' + p['divId'] + ' #div_view_edit #node_id').val();
				ver_id = $('#' + p['divId'] + ' #div_view_edit #ver_id').val();
				var url = '/jqgrid/jqgrid/db/xt/table/testcase/oper/coverSRS/element/' + JSON.stringify([testcase_id]);
				var checkSRS = function(data){
					return (data['prj_ids'].length > 0 && data['srs_node_ids'].length > 0);
				};
				var complete = function(data, params){
					tool.debug(data);
				};
				tool.actionDialog({width:800, height:600, 'title':'Cover SRS', 'div_id':'div_cover_srs'}, url, checkSRS, complete);
				break;
			
			default:
				$table.supr.prototype.infoBtnActions.call(this, action, p);
		}
	};
	
	$table.prototype.buttonActions = function(action, options){
		var $this = this;
		var testcase_id, ver_id;
		var gridId = this.getParams('gridSelector');
		var selectedRows = $(gridId).getGridParam('selarrrow');
		var element = JSON.stringify(selectedRows);
		var postData = $(gridId).getGridParam('postData');
		var conditionSelector = this.getParams('conditionSelector');
	//XT.debug(postData);	
	//XT.debug(options);
		var checkPrj = function(postData){
			if (postData['prj_id'] == undefined || postData['prj_id'] == 0){
				alert("Please use project as condition to query the testcase first");
				return false;
			}
			return true;
		};
		switch(action){
			case 'query_new':
				window.open('/jqgrid/jqgrid/newpage/1/oper/information/db/xt/table/testcase/element/0/ver/0');
				// $this.information(0, 1, 0);
				break;
			case 'query_report':
				var dialog_params = {
					html_type:'url',
					text:'/jqgrid/jqgrid/db/xt/table/testcase/oper/query_report',
					div_id:'query_report_div', 
					width:600, 
					height:300, 
					title:'Report',
					open: function(){
						$("#query_report_div [date='date']").datepicker({dateFormat: "yy-mm-dd"});
						$('#query_report_div #not_tested_from').val(tool.getDateStr(-3, 0));
						$('#query_report_div #not_tested_to').val(tool.getDateStr(0, 1));
						$('#query_report_div #last_result_from').val(tool.getDateStr(-3, 0));
						$('#query_report_div #last_result_to').val(tool.getDateStr(0, 1));
						$('#query_report_div #modified_from').val(tool.getDateStr(-3, 0));
						$('#query_report_div #modified_to').val(tool.getDateStr(0, 1));
					},
					close:function(){
						$(this).remove();
					}
				};
				tool.actionDialog(dialog_params, '/jqgrid/jqgrid/db/xt/table/testcase/oper/query_report', undefined, function(data){
					// tool.debug(data);
					location.href = "/download.php?filename=" + encodeURIComponent(data) + "&remove=1";
				});
				break;
			
			case 'single_to_multi':
				var td = $(options).parent('td').prev('td.cont-td'),
					se = td.children('select');
				var str = tool.single2multi(se);
				td.html(str);
				$(options).button('destroy');
				$(options).attr('id', 'multi_to_single');
				$(options).val('-');
				$(options).html('-');
				$(options).attr('title', 'Change to single selection');
				$(options).button();
				$(options).unbind('click').bind('click', function(){
					$this.buttonActions('multi_to_single', options);
				});

				resetCart(conditionSelector);
				$(td).find('#cart_add_' + se.attr('id')).click();
				break;
				
			case 'ask2review':
				break;

			case 'publish':
				if (tool.checkSelectedRows(selectedRows, 1) && checkPrj(postData)){
					this.comm_action._publish(selectedRows, postData['prj_id'], 'testcase');
				}
				break;
		
			case 'coversrs':
				if (tool.checkSelectedRows(selectedRows, 1))
					this.comm_action._coverSRS(element);
				break;
				
			case 'link2prj':
				if (checkPrj(postData) && tool.checkSelectedRows(selectedRows, 1)){
					var getPrj = function(){
						var projects = [], i = 0;
						$('div#div_testcase_link2prj input[name="projects[]"]:checked').each(function(){
							projects[i ++] = $(this).val();
						});
// tool.debug(projects);							
						return projects;
					};
					var buttons = {
						'Link To': function(){
							var projects = getPrj();
							var url = '/jqgrid/jqgrid/oper/' + action + '/db/xt/table/testcase';
							$.post(url, {element:element, prj_id:postData['prj_id'], projects:projects, link:'link'}, function(data){
								dialog.dialog( "close" );
								if(tool.handleRequestFail(data))return;
								$(gridId).trigger('reloadGrid');
							});
						},
						Cancel:function(){
							dialog.dialog('close');
						}
					};
					// list the all active and ongoing projects
					var dialog = $('<div id="div_testcase_link2prj" />').dialog({
						width:800,
						height:500,
						autoOpen: false,
						title: 'Link Projects',
						modal: true,
						buttons: buttons,
						close:function(event, ui){
							$(this).remove();
						},
						open: function(event, ui){
						// alert("asdfdf");
							var url = '/jqgrid/jqgrid/oper/getlink2prj/db/xt/table/testcase';
							var $this = this;
							$.ajax({
								type:'POST', 
								url:url, 
								data:{element:element, prj_id:postData['prj_id']}, 
								success:function(data){
									$($this).html(data);
								}
							})
						}
					});
					dialog.dialog('open');
				}
				break;
					
			case 'unlinkfromprj':
				if (tool.checkSelectedRows(selectedRows, 1)){
					var url = '/jqgrid/jqgrid/oper/' + action + '/db/xt/table/testcase/element/' + element;
					// list the all active and ongoing projects
					$('<div id="div_testcase_unlinkfromprj" />').load(url, function(data){
						if(tool.handleRequestFail(data))return;

						var getPrj = function(){
							var projects = [], i = 0;
							$('div#div_testcase_unlinkfromprj input[name="projects[]"]:checked').each(function(){
								projects[i ++] = $(this).val();
							});
	// tool.debug(projects);							
							return projects;
						};
						var buttons = {
							'Drop': function(){
								var projects = getPrj();
	tool.debug(projects);
								$.post(url, {projects:projects, link:'drop'}, function(data){
									dialog.dialog( "close" );
									if(tool.handleRequestFail(data))return;
									$(gridId).trigger('reloadGrid');
								});
							},
							Cancel:function(){
								dialog.dialog('close');
							}
						};

						var dialog = $(this).dialog({
								width:800,
								height:500,
								autoOpen: false,
								title: 'Drop Projects',
								modal: true,
								buttons: buttons,
								close:function(event, ui){
									$(this).remove();
								}
							});
						dialog.dialog('open');
					});		
				}
				break;
				
			case 'change_owner':
				if (checkPrj(postData) && tool.checkSelectedRows(selectedRows, 1)){
					var url = '/jqgrid/jqgrid/db/xt/table/testcase/oper/change_owner/prj_id/' + postData['prj_id'];
			//XT.debug(url);		
					tool.actionDialog({div_id:'div_change_ownew', width:400, height:300, title:'Change Owner', postData:{element:element}}, url, undefined, function(){
						$(gridId).trigger('reloadGrid');
					});
				}
				break;

			case 'export':
				if (tool.checkSelectedRows(selectedRows, 1)){
					var checkPrj2 = function(data){
						switch(data['data']['export_type']){
							case 'excel':
							case 'report':
								return true;
							case 'rel':
							case 'excel_rel':
								if (data['data']['prj_id'] == '0' || data['data']['prj_id'] == 0){
									alert("Please select a project");
									return false;
								}
								break;
							default:
								if (data['data']['prj_id'] == '0' || data['data']['prj_id'] == 0 || data['data']['prj_id'] == '-1' || data['data']['prj_id'] == -1){
									alert("Please select a project");
									return false;
								}
						}
						return true;
					};
					var hidden = this.getParams('hiddenSelector'), $sqls = $(hidden + " #sqls").val();
					var dialog_params = {
						div_id:'export_options',
						width:800,
						height:600,
						title:'Export',
						postData:{element:element, sqls:$sqls},
						open:function(){
							$('#export_options #div_for_report_options,#div_for_xml_cmd').hide();
							$('#export_options #select_prj').hide();
							$('#export_options #ces_tr_include_editing_versions,#ces_tr_edit_from,#ces_tr_edit_to,#ces_tr_test_from,#ces_tr_test_to').hide();
							
							$('#export_options input[date="date"]').each(function(){$(this).datepicker('destroy').datepicker({dateFormat:'yy-mm-dd'})});
							$('#export_options input:radio[name="export_type"]').change(function(event){
								if ($(this).is(':checked')){
									switch($(this).val()){
										case 'report':
											$('#export_options #div_for_report_options').show();
											$('#export_options #div_for_xml_cmd,#select_prj').hide();
											break;
										case 'excel':
											$('#export_options #div_for_report_options').hide();
											$('#export_options #div_for_xml_cmd,#select_prj').hide();
											break;
										case 'codec_xml_cmd':
											$('#export_options #div_for_xml_cmd').show();
											$('#export_options #div_for_report_options').hide();
											$('#export_options #select_prj').show();
											break;
										default:
											$('#export_options #div_for_report_options').hide();
											$('#export_options #div_for_xml_cmd').hide();
											$('#export_options #select_prj').show();
											break;
									}
								}
							});
							$('#export_options input:checkbox[name="edit_history[]"]').change(function(event){
// tool.debug($(this).val());
								if ($(this).is(':checked')){
									$('#export_options #ces_tr_edit_from,#ces_tr_edit_to,#ces_tr_include_editing_versions').show();
								}
								else{
									$('#export_options #ces_tr_edit_from,#ces_tr_edit_to,#ces_tr_include_editing_versions').hide();
								}
							});
							$('#export_options input:checkbox[name="test_history[]"]').change(function(event){
								if ($(this).is(':checked')){
									$('#export_options #ces_tr_test_from,#ces_tr_test_to').show();
								}
								else{
									$('#export_options #ces_tr_test_from,#ces_tr_test_to').hide();
								}
							});
						}
					};
					var url = '/jqgrid/jqgrid/db/xt/table/testcase/oper/export/select_prj/' + postData['prj_id'];
					tool.actionDialog(dialog_params, url, checkPrj2, function(data){
tool.debug(data);
						// location.href = "/download.php?filename=" + encodeURIComponent(data) + "&remove=1";
					});
				}
				break;				
			default:
				$table.supr.prototype.buttonActions.call(this, action, options);
		}
	};
	
	$table.prototype.view_edit_abort = function(p){
		var divId = p.divId;
		var dialog = $table.supr.prototype.view_edit_abort.call(this, p);
		//询问是否确认要Abort当前Version
		var ver = dialog.find('#ver_id').val(), tc_id = dialog.find('#node_id').val();
		return this.comm_action._abort(tc_id, [ver]);
	};
	
	$table.prototype.view_edit_edit = function(p){
		var dialog = $table.supr.prototype.view_edit_edit.call(this, p);
		dialog.find('#view_edit_ask2review,#view_edit_publish,#view_edit_coversrs,#view_edit_abort').hide();
		return dialog;
	};
	
	$table.prototype.view_edit_cancel = function(ui){
		var dialog = $table.supr.prototype.view_edit_cancel.call(this, ui);
		dialog.find('#view_edit_ask2review,#view_edit_publish,#view_edit_coversrs').show();
		//以coversrs是否存在来判断当前的version是否Published
		var coversrs = dialog.find('#view_edit_coversrs');
		if (coversrs.length == 0){
			dialog.find('#view_edit_abort').show();
		}
		return dialog;
	};

	$table.prototype.view_edit_afterSave = function(divId, id, parent_id, data){
		var ver_id = $('#' + divId + ' #ver_id').val(), tc_id = $('#' + divId + ' #node_id').val();
		var node_ver = data['msg'].split(':'), oper = data.oper;
// tool.debug([parent_id, id, ver_id, tc_id, node_ver, oper, divId]);
		if(id == 0 || oper == 'cloneit'){//更新整个页面，重定向
			location.href = "/jqgrid/jqgrid/newpage/1/oper/information/db/xt/table/testcase/element/" + node_ver[0] + "/parent/0/ver/" + node_ver[1];
		}
		else{ 
			this.updateInformationPage(ver_id, id, 'view_edit');
			//刷新edit_history
			var grid = grid_factory.get('xt', 'testcase_ver', {container:'edit_history_' + id});
	// alert("id = " + id + ", parentId = " + parent_id + ", ver_id = " + ver_id + ", tc_id = " + tc_id);		
			var gridId = grid.getParams('gridSelector');
			$(gridId).trigger('reloadGrid');
		}
	};
	
	$table.prototype.view_edit_cloneit = function(ui){
		var dialog = $table.supr.prototype.view_edit_cloneit.call(this, ui);
		dialog.find('#view_edit_ask2review,#view_edit_publish,#view_edit_coversrs').hide();
		return dialog;
	};
	
	$table.prototype.setLinkage = function(divSelector, sources){
		var $this = this;
		sources = sources || ['select#os_id', 'select#testcase_type_id', 'select#testcase_module_id', 'select#board_type_id', 'select#chip_id', 'select#chip_type_id']
		var os_chip_board_type = {os_id:divSelector + ' select#os_id', chip_id:divSelector + ' select#chip_id', board_type_id:divSelector + ' select#board_type_id'};
		for(var i in sources){
			switch(sources[i]){
				case 'select#os_id':
					var target = [
//						{selector:divSelector + ' select#testcase_type_id', type:'select', field:'os_ids', cond:'REGEXP', url:'/jqgrid/jqgrid/oper/linkage/db/xt/table/testcase_type'},
						{selector:divSelector + ' select#prj_id', type:'select', field:'os_id', url:'/jqgrid/jqgrid/oper/linkage/db/xt/table/zzvw_prj'},
						{selector:divSelector + ' select#chip_id', type:'select', field:'os_id', url:'/jqgrid/jqgrid/oper/getchip/db/xt/table/zzvw_prj'},
						{selector:divSelector + ' select#board_type_id', type:'select', field:'os_id', url:'/jqgrid/jqgrid/oper/getboardtype/db/xt/table/zzvw_prj'},
						// {selector:divSelector + ' select#testcase_module_id', type:'select', field:'testecase_module_id', url:'/jqgrid/jqgrid/oper/getmodulebyos/db/xt/table/testcase_module'}
					];
					tool.linkage({selector:divSelector + ' select#os_id'}, target, {selector:os_chip_board_type});
					break;
				case 'select#board_type_id':
					var target = [{selector:divSelector + ' select#prj_id', type:'select', field:'board_type_id', url:'/jqgrid/jqgrid/oper/linkage/db/xt/table/zzvw_prj'},
								  {selector:divSelector + ' select#chip_id', type:'select', field:'board_type_id', url:'/jqgrid/jqgrid/oper/getchip/db/xt/table/zzvw_prj'},
								  {selector:divSelector + ' select#os_id', type:'select', field:'board_type_id', url:'/jqgrid/jqgrid/oper/getos/db/xt/table/zzvw_prj'}];
					tool.linkage({selector:divSelector + ' select#board_type_id'}, target, {selector:os_chip_board_type});
					break;
				case 'select#chip_id':
					var target = [{selector:divSelector + ' select#prj_id', type:'select', field:'chip_id', url:'/jqgrid/jqgrid/oper/linkage/db/xt/table/zzvw_prj'},
								  {selector:divSelector + ' select#board_type_id', type:'select', field:'chip_id', url:'/jqgrid/jqgrid/oper/getboardtype/db/xt/table/zzvw_prj'},
								  {selector:divSelector + ' select#os_id', type:'select', field:'chip_id', url:'/jqgrid/jqgrid/oper/getos/db/xt/table/zzvw_prj'}];
					tool.linkage({selector:divSelector + ' select#chip_id'}, target, {selector:os_chip_board_type});
					break;
				case 'select#testcase_type_id':
					tool.linkage({selector:divSelector + ' select#testcase_type_id'}, [
						{selector:divSelector + ' select#os_id', type:'select', field:'testcase_type_id', cond:'REGEXP', url:'/jqgrid/jqgrid/oper/linkage/db/xt/table/os'},
						{selector:divSelector + ' select#testcase_module_id', type:'select', field:'testcase_type_id', cond:'REGEXP', url:'/jqgrid/jqgrid/oper/linkage/db/xt/table/testcase_module'},
						]
					);
					break;
				case 'select#testcase_module_id':
					tool.linkage({selector:divSelector + ' select#testcase_module_id'}, [
						{selector:divSelector + ' select#testcase_testpoint_id', type:'select', field:'testcase_module_id', url:'/jqgrid/jqgrid/oper/linkage/db/xt/table/testcase_testpoint'},
						]
					);
					break;
				case 'select#chip_type_id':
					tool.linkage({selector:divSelector + ' select#chip_type_id'}, [
						{selector:divSelector + ' select#chip_id', type:'select', field:'chip_type_id', url:'/jqgrid/jqgrid/oper/linkage/db/xt/table/chip'},
						], 
						{selector:{os_id:divSelector + ' select#os_id', chip_id:divSelector + ' select#chip_id', board_type_id:divSelector + ' select#board_type_id'}}
					);
					break;
			}
		}
	};
	
}());
