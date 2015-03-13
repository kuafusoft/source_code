function gc_grid_action(grid){
	gc_grid_action.supr.call(this, grid.getParams());
	this.grid = grid;
}

var tool = new kf_tool();
tool.extend(gc_grid_action, gc_kf);

gc_grid_action.prototype.previousTag = 0;

gc_grid_action.prototype.updateInformationPage = function(verId, nodeId, pageName){
	var $this = this;
	var p = this.getParams(['db', 'table', 'container']);
	var tabId = 'information_tabs_' + p.db + '_' + p.table + '_' + nodeId;
	var page = pageName + '_' + nodeId;
	var real_node_id = $('#' + tabId + ' #div_view_edit #node_id').val();// 如果是New Case，那么nodeId = 0，但真正的id是real_node_id
// tool.debug([verId, nodeId, pageName, tabId, page, real_node_id]);
	$('#' + tabId + ' #' + page).load('/jqgrid/jqgrid/db/' + p.db + '/table/' + p.table + '/oper/update_information_page/page/' + pageName + '/element/' + real_node_id + '/ver/' + verId, function(data){
		$('#' + tabId).tabs('enabled', page);
		$('#' + tabId).tabs('option', 'selected', page);
		$this.information_open(tabId, verId, pageName);
	});
};

gc_grid_action.prototype.saveDisplayCookie = function(){
	var $this = this;
	var gridId = this.getParams('gridSelector');
	var p_optionSelector = this.grid.getParams('p_optionSelector');
	var colModel = $(gridId).getGridParam('colModel'), content = {}, saved_colModel = [];
//$this.debug(colModel);
	var order = 0;
	for(var key in colModel){
		if (colModel[key].name == 'cb' || colModel[key].name == 'subgrid'){
			continue;
		}
		saved_colModel.push(colModel[key]);
		content[colModel[key]['name']] = {order:order ++, hidden:colModel[key]['hidden'], width:colModel[key]['width']};
//        order ++;
//xt.debug(colModel[key]['name']);        
	}
	$.ajax({
		type: "post",
		url: '/jqgrid/jqgrid',
		data: {container:this.getParams('container'), db:this.getParams('db'), table:this.getParams('table'), oper:'saveCookie', type:'display', content:JSON.stringify(content)},
		error: function(XMLHttpRequest, textStatus, errorThrown){
			alert(textStatus);
		},
		success: function(options){
			var gw = parseInt($(gridId).getGridParam("width"));
			$(gridId).setGridWidth(gw-0.01,true);
			// 更新保存的gridoptions
			if (p_optionSelector){
				var suboptions = p_optionSelector + ' #subgridoptions';
				var subgridOptions = JSON.parse($(suboptions).val());
				subgridOptions.gridOptions.colModel = saved_colModel;
				$(suboptions).val(JSON.stringify(subgridOptions));
			}
		}
	});
};

gc_grid_action.prototype.ready = function(base){
	base = base || this.getParams('base');
// tool.debug("base = " + base);	
	var $this = this;
	
	var cond = '#' + base + '_cond';
	var ac = $this.getAutoCompleteField();
	$.each(ac, function(i, n){
		if (n.input){
			var table = n['table'] || $this.getParams('table');
			$('div' + cond + ' #' + n.input).autocomplete({
				minLength: 2,
				source: "/jqgrid/jqgrid/oper/autocomplete/db/xt/table/" + table + "/field/" + n.field + "/rows/12"
			});
			// $('div' + cond + ' #' + n.input).unbind('keypress').bind('keypress', {button:'div' + cond + ' #query'}, function(event){
				// tool.triggerButton(event, event.data.button);
			// });
		}
	});
	$(cond).unbind('keypress').bind('keypress', {button:'div' + cond + ' #query'}, function(event){
		tool.triggerButton(event, event.data.button);
	});
};

gc_grid_action.prototype.getAutoCompleteField = function(){
	return [{input:'key', field:'name'}];
};

gc_grid_action.prototype.query = function(){
	var gridSelector = this.getParams('gridSelector'), 
		pagerSelector = this.getParams('pagerSelector'),
		conditionSelector = this.getParams('conditionSelector'),
		optionSelector = this.getParams('optionSelector'),
		advancedButtonId = this.getParams('advancedButtonId'),
		hideFilterButtonId = this.getParams('hideFilterButtonId'),
		hiddenSelector = this.getParams('hiddenSelector'),
		displaySelector = conditionSelector + '_display',
		query_conditionSelector = conditionSelector + '_query_conditions';
	var db = this.getParams('db'), table = this.getParams('table');
	
	var filter = tool.getAllInput(conditionSelector)['data'];
	var hidden = tool.getHidden(hiddenSelector);
	var postData = {table:table, db:db};
	var gridExist = false;
	// remove the advanced_button
	delete filter[advancedButtonId];
	delete filter[hideFilterButtonId];
	try{
		gridExist = ($(gridSelector).html().trim() != '');
	}catch(e){};
	if (gridExist)
		postData = $(gridSelector).getGridParam('postData');
	postData['_search'] = true;
	for(var i in filter){
		postData[i] = filter[i];
	}
/*		
	for(var i in hidden)
		postData[i] = hidden[i];
*/		
	var rules = [];
//filters:'{"groupOp":"AND","rules":[{"field":"' + expandField + '","op":"eq","data":' + row_id + '}]}'
	for(var i in hidden){
		if(i == 'sql' || i == 'sqls')
			continue;
		rules.push({field:i, op:'eq', data:hidden[i]});
	}
	if (rules.length)
		postData['filters'] = JSON.stringify({groupOp:'AND', rules:rules});
// tool.debug(rules);		
// tool.debug(postData);		
	if(!gridExist){
		// var gridOptions = JSON.parse($(optionSelector + ' #gridoptions').val());
		this.grid.load(postData, false, optionSelector);
	}
	else{
		$(gridSelector).setGridParam({postData:postData});
		$(gridSelector).trigger("reloadGrid");
	}
	// $(query_conditionSelector).html(postData['filters']);
//	$(displaySelector).click();
};

gc_grid_action.prototype.resetQuery = function(){
	var conditionSelector = this.getParams('conditionSelector');
	//debug(conditionId + ':input');
	$(conditionSelector + ' :input').not(':disabled').each(function(i){
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
				// 重置当前选项
			default:
				var currentVal = $(this).val();
				$(this).val(original_value);
				if(currentVal != original_value){
// tool.debug([currentVal, original_value, $(this)]);
					$(this).trigger('change'); //这里有些问题，如果是Select，主要是自己的option list 没法恢复
				}
				break;
		}
	});
};

gc_grid_action.prototype.expandSubGridRow = function(subgrid_id, row_id, subGrid){
// tool.debug(subGrid);
	var expandField = subGrid.expandField,
		db = subGrid.db, 
		table = subGrid.table,
		parent_db = this.getParams('db'),
		parent_table = this.getParams('table'),
		condMap = subGrid.additional || {};
// tool.debug(condMap);		
	var base, subgrid_table_id, pager_id, optionSelector, html; 
	optionSelector = this.getParams('optionSelector');
	base = subgrid_id + '_' + db + '_' + table;
//    canExpand = canExpand || false;
	subgrid_table_id = base + '_list'; 
	pager_id = base + '_pager'; 
	gridOption_div_id = base + '_option';
	html = "<div id='" + gridOption_div_id + "'><input type='hidden' id='gridoptions'><input type='hidden' id='subgridoptions'></div><table id='"+subgrid_table_id+"' class='scroll'></table><div id='"+pager_id+"' class='scroll'></div>";
	$("#"+subgrid_id).html(html);
	var p = {container:subgrid_id, p_db:parent_db, p_table:parent_table, p_id:row_id, subgrid:true};

	var rules = [{field:expandField, op:'eq', data:row_id}];
	var postData = this.getPostDataForSubgrid();
	for(var i in condMap){
// tool.debug([i, condMap[i]]);
		rules.push({field:i, 'op':'eq', 'data':condMap[i]});
	}
// tool.debug(rules);	
	postData['filters'] = JSON.stringify({groupOp:'AND', rules:rules});
	var subGrid = grid_factory.get(db, table, p);
	//需要更新subGrid数据
	
	return subGrid.load(postData, true, optionSelector);
};

gc_grid_action.prototype.getPostDataForSubgrid = function(){
	return {};
};

gc_grid_action.prototype.fillTagOptions = function(gridSelector, tagSelectId, topTagSelectId, data){
	var $this = this;
	var selector = '#' + tagSelectId + ',#' + topTagSelectId;
	$(selector).find('option').remove();
	// $('#' + topTagSelectId).find('option').remove();
	var options = [//"<option id='op_no_op' value='no_op' class='option_gray'>==Tag Search==</option>", 
		"<option id='op_0' value='0'>=Without Tag=</option>",
		"<option id='op_refresh_tag' value='refresh_tag'>=Refresh Tag=</option>",
		"<option disabled='disabled' class='option_gray'>--------------</option>"
		];
	$.each(data, function(i, n){
		options.push("<option id='op_" + n['id'] + "' value='" + n['id'] + "'>" + n['name'] + "</option>");
	});
	$.each(options, function(i, n){
		$(selector).append(n);
		// $('#' + topTagSelectId).append(n);
	});
	
	$(selector).focus(function () {
		// Store the current value on focus and on change
		$this.previousTag = this.value;
	}).unbind('change').bind('change', $this.getParams(), function(event){
		// Do something with the previous value after the change
		var op_id = $(this).children('option:selected').val();
		$(selector).val(op_id);
		$this.tagSearchActions(op_id, gridSelector, event.data, tagSelectId, topTagSelectId);

		if (op_id !== 'refresh_tag'){
			$this.previousTag = op_id;
		}
	});
	
	$(selector).children('#op_' + $this.previousTag).attr('selected', 'selected');
	$(selector).val($this.previousTag);
};

gc_grid_action.prototype.tagSearchActions = function(op_id, gridSelector, options, tagSelectId, topTagSelectId){
	var $this = this;
	
	switch(op_id){
		case 'no_op':
			break;
		case 'refresh_tag':
			$.post('/jqgrid/jqgrid', {db:options.db, table:options.table, oper:'fetch_tags'}, 
				function(data, status){
					$this.fillTagOptions(gridSelector, tagSelectId, topTagSelectId, data);
				}, 'json'
			);
			break;
		case 'no_tag':
		default:
			var postData = {__interTag:op_id};
			$(gridSelector).setGridParam({postData:postData});
			$(gridSelector).trigger("reloadGrid");
			break;
	}
};

gc_grid_action.prototype.infoBtnActions = function(action, p){
	switch(action){
		case 'view_edit_cancel':
			this.view_edit_cancel(p);
			break;
			
		case 'view_edit_edit':
			this.view_edit_edit(p);
			break;
			
		case 'view_edit_save':
			this.view_edit_save(p);
			break;
			
		case 'view_edit_cloneit':
			this.view_edit_cloneit(p);
			break;
			
		case 'view_edit_saveandnew':
			this.view_edit_saveAndNew(p);
			break;
			
		case 'view_edit_abort':
			this.view_edit_abort(p);
			break;

		case 'view_edit_ask2review':
			this.view_edit_ask2review(p);
			// testcase_id = $('#' + p['divId'] + ' #div_view_edit #node_id').val();
			// ver_id = $('#' + p['divId'] + ' #div_view_edit #ver_id').val();
			// this.comm_action._ask2review(testcase_id, ver_id);
			break;
			
		case 'view_edit_publish':
			this.view_edit_publish(p);
			// testcase_id = $('#' + p['divId'] + ' #div_view_edit #node_id').val();
			// ver_id = $('#' + p['divId'] + ' #div_view_edit #ver_id').val();
			// this.comm_action._publish(testcase_id, ver_id, 'testcase_ver');
			break;
				
	}
};

gc_grid_action.prototype.getParamsForDefaultAction = function(action, data){
	var params = {
		div_id:'div_' + action,
		actionType:'post', // 'actionDialog' or 'post'
		width: 600,
		height: 400
	};
	return $.extend(true, params, data);
};

gc_grid_action.prototype.buttonActions = function(action, p){
	var db = this.getParams('db'), table = this.getParams('table');
	var $this = this;
	var gridId = this.getParams('gridSelector');
	var selectedRows = $(gridId).getGridParam('selarrrow');
	var element = JSON.stringify(selectedRows);
	switch(action){
		case 'query':
			this.query();
			break;
		case 'query_reset':
			this.resetQuery();
			break;
		case 'query_new':
// alert("query_new");		
			this.information(0);
			break;
		case 'import':
		case 'query_import':
			var dialog_params = {
				html_type:'url',
				text:'/jqgrid/jqgrid/db/' + db + '/table/' + table + '/oper/import',
				div_id:'import_div', 
				width:500, 
				height:300, 
				title:'Upload',
				open: function(){
					$('#import_div :button,:submit').button();
				},
				close:function(){
					$(this).html('');
					$(this).remove();
				},
				buttons:{
					Close:function(){
						$(this).dialog('close');
					}
				}
				
			};
			tool.popDialog(dialog_params);
			break;
			
		case 'multi_to_single':
			var td = $(p).parent('td').prev('td.cont-td'),
				cart = td.children('div');
			var str = tool.multi2single(cart);
			td.html(str);

			$(p).button('destroy');
			$(p).attr('id', 'single_to_multi');
			$(p).val('+');
			$(p).html('+');
			$(p).attr('title', 'Change to multi-selection');
			$(p).button();
			$(p).unbind('click').bind('click', function(){
				$this.buttonActions('single_to_multi', p);
			});
			break;
			
		case 'single_to_multi':
			var td = $(p).parent('td').prev('td.cont-td'),
				se = td.children('select');
			var str = tool.single2multi(se);
			td.html(str);
			$(p).button('destroy');
			$(p).attr('id', 'multi_to_single');
			$(p).val('-');
			$(p).html('-');
			$(p).attr('title', 'Change to single selection');
			$(p).button();
			$(p).unbind('click').bind('click', function(){
				$this.buttonActions('multi_to_single', p);
			});
			$(td).find('#cart_add_' + se.attr('id')).click();
			break;
			
		case 'columns':
			$(gridId).setColumns({
				top:100, 
				left:400, 
				width:600, 
				height:"auto", 
				updateAfterCheck: true,
				onClose:function(){
					// save column hidden/width
					$this.saveDisplayCookie(gridId);
				}
			})
			break;
			
		case 'tag':
			var _tag = function(selectedRows, db, table){
				var dialog = $("<div id='create_tag'>Create a tag:<input id='tag' type='text'/><label for='public'>public<input type='checkbox' id='public' checked='checked'/></div>")
					.dialog({
						autoOpen: false,
						title: 'Create a tag for ' + table,
						width:400,
						modal:true,
						open:function(event, ui){
							$("div#create_tag input#tag").autocomplete({
								source:'/jqgrid/jqgrid/db/' + db + '/table/tag/oper/autocomplete/field/name/rows/5/creater_id/current',
								minChars: 0,
								width: 310,
								autoFill: true,
								highlightItem: true,
					//		multiple: true,
					//		multipleSeparator: " ",
					
								formatItem: function(row, i, max, term) {
									return "<i><strong>" + term + "</strong></i>";
								}
							});
						},
						close:function(){
							$(this).html("");
							$(this).remove();
						},
						buttons: {
							Cancel: function() {
								$(this).dialog('close');
							},
							'Tag': function() {
								var t = $(this);
								$.post('/jqgrid/jqgrid', {db:db, table:table, oper:'tag', element:element, tag:t.find('#tag').val(), isPublic:t.find('#public').attr('checked')}, 
									function(data, status){
										if(tool.handleRequestFail(data))return;
			//xt.debug(data);
										var colModel = $(gridId).getGridParam('colModel');
										var lastCol = colModel.pop();
			//xt.debug(lastCol);                            
										if (lastCol.name == '__interTag'){
											lastCol.searchoptions.value = data;
											colModel.push(lastCol);
			//xt.debug(colModel);                                
											$(gridId).setGridParam({colModel:colModel});
										}
										t.dialog('close');
										$(gridId).trigger('reloadGrid');
									}, 'json'
								);
							}
						}
				   });
				dialog.dialog('open');
			};			
			if (tool.checkSelectedRows(selectedRows, 1)){
				_tag(selectedRows, db, table);
			}
			break;
		case 'removeFromTag':
			if (tool.checkSelectedRows(selectedRows, 1)){
				var url = '/jqgrid/jqgrid/db/' + db + '/table/' + table + '/oper/removeFromTag';
				tool.actionDialog({div_id:'div_removeFromtag', width:400, height:300, title:'Remove Select Records From This Tag', postData:{element: element}}, url, undefined, function(){
					$(gridId).trigger('reloadGrid');
				});
			};
			break;
		   
		case 'change_owner':
			if (tool.checkSelectedRows(selectedRows, 1)){
				var url = '/jqgrid/jqgrid/db/' + db + '/table/' + table + '/oper/change_owner';
				tool.actionDialog({div_id:'div_change_owner', width:400, height:300, title:'Change Owner', postData:{element: element}}, url, undefined, function(){
					$(gridId).trigger('reloadGrid');
				});
			};
			break;
			
		case 'lend':
			if (tool.checkSelectedRows(selectedRows, 1)){
				var url = '/jqgrid/jqgrid/db/' + db + '/table/' + table + '/oper/lend';
				tool.actionDialog({width:400, height:300, title:'Lend To', postData:{element: element}}, url, undefined, function(){
					$(gridId).trigger('reloadGrid');
				});
			};
			break;
			
		case 'export':
			if (tool.checkSelectedRows(selectedRows, 1)){
				var url = '/jqgrid/jqgrid/db/' + db + '/table/' + table + '/oper/export';
				var hidden = $this.getParams('hiddenSelector'), $sqls = $(hidden + ' #sqls').val();
				tool.actionDialog({div_id:'export_div', width:400, height:300, title:'Export', postData:{element: element, sqls:$sqls}}, url, undefined, function(data){
// tool.debug(data)				;
					location.href = "/download.php?filename=" + encodeURIComponent(data) + "&remove=1";
				});
			};
			break;

		case 'ver_diff':
		case 'his_diff':
			if (tool.checkSelectedRows(selectedRows, 1)){
				var url = '/jqgrid/jqgrid/db/' + db + '/table/' + table + '/oper/' + action + '/element/' + element;
				var dialog_params = {
					div_id:action + '_div', 
					width:800, 
					height:600, 
					title:'Diff Versions', 
					postData:{element: element},
					open: function(event, ui){
						$('#hide_same').unbind().bind('click', {selector:'#' + action + '_div tr.jqgrow'}, function(event){
							tool.hideTheSame(this, event);
						});
					}
				};
				if(action == 'his_diff')
					dialog_params['title'] = 'Diff History';
				tool.actionDialog(dialog_params, url);
			};
			break;
			
		case 'activate':
		case 'inactivate':
		case 'subscribe':
			if (!tool.checkSelectedRows(selectedRows, 1))
				break;
				
		default:
			var actionParams = this.getParamsForDefaultAction(action, {element:element});
			this.defaultActionForAction(action, '/jqgrid/jqgrid/db/' + db + '/table/' + table + '/oper/' + action, actionParams);
			
			// var dialog = tool.waitingDialog();// this.popDialog('notice', {'text': "Processing......", 'title':"Waiting", 'buttonok':false});
			// $.ajax({
				// type:'POST', 
				// url:'/jqgrid/jqgrid', 
				// data:{db:db, table:table, oper:action, element:element}, 
				// success: function(data, status){
						// dialog.dialog('close');
						// if(tool.handleRequestFail(data))return;
						// if (data == 1004){ // not found the action
							// alert("Sorry, this feature " + action + " is not implemented yet");
						// }
						// else{
							// $(gridId).trigger('reloadGrid');
						// }
					// },
				// error: function(httpReq, textStatus, errorThrown){
					// alert("Error!! " + textStatus);
				// }
			// });
	}
};

gc_grid_action.prototype.onContextMenuShow = function(el){
tool.debug(el);
};

gc_grid_action.prototype.loadComplete = function(data){
	if(tool.handleRequestFail(data) == false){
		var $sql = data.sql, $sqls = data.sqls, hidden = this.getParams('hiddenSelector');
		$(hidden + " #sql").val($sql);
		$(hidden + " #sqls").val($sqls);
	}
};

gc_grid_action.prototype.gridComplete = function(){
	// alert("gridComplete");
};

gc_grid_action.prototype.defaultActionForAction = function(action, url, dialogParams){
	var completeFunction = dialogParams.completeFunction || function(data, params){};
	var gridId = this.getParams('gridSelector');
tool.debug(dialogParams);
tool.debug(url);
	if(dialogParams.actionType == 'actionDialog'){
		var validFunction = dialogParams.validFunction || undefined;
		var title = dialogParams.title || action;
		tool.actionDialog(dialogParams, url, validFunction, function(data){
			if(tool.handleRequestFail(data))return;
			if (data == 1004){ // not found the action
				alert("Sorry, this feature " + title + " is not implemented yet");
			}
			else{
				completeFunction(data, dialogParams);
				$(gridId).trigger('reloadGrid');
			}
		});
	}
	else{
		var dialog = tool.waitingDialog();// this.popDialog('notice', {'text': "Processing......", 'title':"Waiting", 'buttonok':false});
		var db = dialogParams.db, table = dialogParams.table, element = dialogParams.element;
		$.ajax({
			type:'POST', 
			url:url, 
			data:{db:db, table:table, oper:action, element:element}, 
			success: function(data, status){
				dialog.dialog('close');
				if(tool.handleRequestFail(data))return;
				if (data == 1004){ // not found the action
					alert("Sorry, this feature " + action + " is not implemented yet");
				}
				else{
					completeFunction(data, dialogParams);
					$(gridId).trigger('reloadGrid');
				}
			},
			error: function(httpReq, textStatus, errorThrown){
				alert("Error!! " + textStatus);
			}
		});
	}
};

gc_grid_action.prototype.contextActions = function(action, el){
// tool.debug(action);
// tool.debug(el);
	var db = this.getParams('db'), 
		table = this.getParams('table'), 
		gridId = this.getParams('gridSelector'),
		menu = gridId + '_myMenu', 
		element = el.attr('id'), 
		menuItemCaption = $(menu + ' #' + action + ' a').html();
	
// tool.debug(element)	;
	switch(action){
		default:
			var url = '/jqgrid/jqgrid/db/' + db + '/table/' + table + '/oper/' + action + '/element/' + element;
// tool.debug(this);
			var dialogParams = this.getParamsForDefaultAction(action, {actionType:'actionDialog', title: menuItemCaption});
			this.defaultActionForAction(action, url, dialogParams);
			break;
	}
};

gc_grid_action.prototype.getGridsForInfo = function(divId){
	var grids = [];
// tool.debug(divId);
// tool.debug($('#' + divId + " #div_hidden #info_tabs").val());
	var info_tabs = JSON.parse($('#' + divId + " #div_hidden #info_tabs").val());
	$.each(info_tabs, function(i, domEle){
// tool.debug(domEle);
		grids.push({tab:domEle.tab, container:domEle.tab, db:domEle.db, table:domEle.table});
	})
	return grids;
}

gc_grid_action.prototype.information = function(rowId, newpage, ver){
	newpage = newpage || 0;
	var $this = this;
	var gridId = this.grid.getParams('gridId'), db = this.grid.getParams('db'), table = this.grid.getParams('table'), 
		p_id = this.grid.getParams('p_id'), p_db = this.grid.getParams('p_db'), p_table = this.grid.getParams('p_table'), 
		postData = this.getParams('postData');
	var information_div = 'div_information_' + db + '_' + table;
	var caption = this.information_getCaption();
	var title = "Detail Information for " + caption;
	if (rowId == 0)
		title = 'New ' + caption;
	
	var dialog_params = {
		ok:'close', 
		open:function(){
			$this.information_open(information_div, rowId, 'all');
		},
		close: function(){
			$this.information_close();
			$(this).html('');
			$(this).remove(); 
		},
		gridId:gridId,
		rowId:rowId,
		div_id:information_div,
		title:title,
		height: 600, 
		width: 1200,
		tabId: "#information_tabs_" + db + '_' + table + '_' + rowId
	};
//tool.debug(dialog_params);	
	var url = '/jqgrid/jqgrid/newpage/' + newpage + '/db/' + db + '/table/' + table + '/oper/information/element/' + rowId;
	if(ver != undefined)
		url += '/ver/' + ver;
	if (p_db != undefined)
		url += '/parentdb/' + p_db;
	if(p_table != undefined)
		url += '/parenttable/' + p_table;
	if (p_id != undefined)
		url += '/parent/' + p_id;
	for(var k in postData)
		url += '/' + k + '/' + postData[k];
	tool.tabDialog(dialog_params, url);
};

gc_grid_action.prototype.information_open = function(divId, rowId, pageName){
	var $this = this, this_table = this.getParams('table'), this_id = $('#' + divId + ' #div_hidden #id').val();
// tool.debug([divId, this_table, this_id]);	
	// // //调整view_edit界面的显示比例
	// var t = $('#' + divId + " #div_view_edit table.ces");
	// var div_width = $('#' + divId).width();
// tool.debug(div_width);
	// t.each(function(i){
		// var th1 = $(this).find("th.ces:first");
		// var th1_width = th1.width();
		// if(div_width * th1_width / 100 > 120){
			// th1_width = 120;
// tool.debug(th1_width)			;
			// $(th1).width(th1_width + 'px');
		// }
	// });
	
	tool.defaultActionForTab('#' + divId);
	var gridsLoad = this.getGridsForInfo(divId);
	pageName = pageName || 'all';
	if(pageName == 'all' || pageName == 'view_edit'){
		gridsLoad.unshift({tab:'view_edit', table:this_table});
		// $('#' + divId + ' :button').each(function(i){
			// $(this).button();
			// if ($(this).attr('onclick') == undefined){
				// $(this).unbind('click').bind('click', function(){
					// var id = $(this).attr('id');
					// $this.infoBtnActions(id, {divId:divId, item:this});
				// });
			// }
		// });
	}
tool.debug(gridsLoad);
	$.each(gridsLoad, function(i, n){
// tool.debug(n);	
		var tab = n['tab'] || n['container'], db = n['db'] || $this.getParams('db'), table = n['table'], params = n['params'] || {};
// tool.debug([i, n, tab, pageName]);
		if (pageName == 'all' || pageName == tab){
			$('#' + divId + ' #' + tab + '_' + this_id + ' :button').each(function(i){
				$(this).button();
				if ($(this).attr('onclick') == undefined){
					$(this).unbind('click').bind('click', function(){
						var id = $(this).attr('id');
						$this.infoBtnActions(id, {divId:divId, item:this});
					});
				}
			});
			
			var container = n['container'] + '_' + rowId;
			var rules = $this.getFilterRules(rowId, n);
			var filters = JSON.stringify({groupOp:'AND', rules:rules});
			var detail_grid = grid_factory.get(db, table, {container:container, parent:rowId});
// alert("pageName = " + pageName + ", tab = " + tab + ", filters = " + filters + ", db = " + db + ", table = " + table);
			detail_grid.setParams($.extend(true, params, {p_id:rowId, parent:rowId, filters:filters}));
			detail_grid.indexInDiv({filters:filters});
		}
	});
	this.bindEventsForInfo(divId, rowId);
}

gc_grid_action.prototype.getFilterRules = function(rowId, n){
// tool.debug(this.getParams());
	var table = n['table'];
	var field = table + '.' + this.getParams('real_table') + '_id';
	var rules = [{field:field, op:'eq', data:rowId}];
	if(n['rules']){
		$.each(n['rules'], function(j, v){
			rules.push(v);
		});
	}
	return rules;
}

gc_grid_action.prototype.bindEventsForInfo = function(divId, rowId){

}

gc_grid_action.prototype.information_close = function(){

}

gc_grid_action.prototype.information_getCaption = function(){
	return this.getParams('table');
}

gc_grid_action.prototype.view_edit_edit = function(p){
	var divId = p.divId;
	var dialog = $('#' + divId);
	var node_id = $('#' + divId + ' #div_hidden #id').val();
	dialog.find('#div_view_edit [editable="1"]').each(function(i){
		var prop_edit = $(this).attr('prop_edit');
		$(this).attr(prop_edit, false);
	});	
	dialog.find('#div_button_edit #view_edit_cloneit,#view_edit_edit,#view_edit_ask2review,#view_edit_publish').hide();
	dialog.find('#div_button_edit #view_edit_save,#view_edit_saveandnew,#view_edit_cancel').show();
	dialog.find('#div_hidden #saved').val('false');
	dialog.find('#div_hidden #clone').val('false');
	
	if (node_id != 0)
		dialog.find('#img_unique_check').attr('src', '/img/aCheck.png').show();
	else
		dialog.find('#img_unique_check').attr('src', '/img/aHelp.png').show();
	
	dialog.find('div#cart_button').show();
	dialog.find(':input:enabled[type!=hidden]')[0].focus();
	return dialog;
};

gc_grid_action.prototype.view_edit_cancel = function(p){
	var divId = p.divId;
	var dialog = $('#' + divId);
	var node_id = dialog.find('div#div_hidden #id').val();
	if (node_id == '0' || node_id == 0)
		$(dialog).dialog('close');
	else{
		dialog.find('#div_view_edit [editable="1"]').each(function(i){
			var orig_val = $(this).attr('original_value');
			var type = $(this).attr('type');
			var prop_edit = $(this).attr('prop_edit');
			if (type == 'button')
				return;
			if (type == 'checkbox'){
				if(orig_val == '1' || orig_val == true || orig_val == 'true')
					$(this).attr('checked', true);
				else
					$(this).attr('checked', false);
			}
			else
				$(this).val($(this).attr('original_value'));
			$(this).attr(prop_edit, true);
		});
		dialog.find('#div_button_edit #view_edit_cancel,#view_edit_save,#view_edit_saveandnew').hide();
		dialog.find('#div_button_edit #view_edit_edit,#view_edit_cloneit,#view_edit_ask2review,#view_edit_publish').show();
		dialog.find('#img_unique_check').hide();
		dialog.find('div#cart_button').hide();
	}
	return dialog;
};

gc_grid_action.prototype.view_edit_save = function(p, newNext){
	newNext = newNext || false;
	var divId = p.divId;
	var $this = this;
	var dialog = $('#' + divId);
	var dialog_id = '#' + dialog.attr('id');
	var cloneit = dialog.find('#div_hidden #clone').val();
	var inputs = $this.view_edit_save_input(dialog_id + ' #div_view_edit');
	
	if (inputs['passed'].length == 0){
		if(!newNext){
			dialog.find('#div_view_edit [editable="1"]').each(function(i){
				var prop_edit = $(this).attr('prop_edit');
				if ($(this).attr('type') == 'checkbox'){
					$(this).attr('original_value', this.checked);//.attr('disabled', true);
				}
				else
					$(this).attr('original_value', $(this).val());//.attr('disabled', true);
				$(this).attr(prop_edit, true);
			});
			dialog.find('#div_button_edit #view_edit_save,#view_edit_saveandnew,#view_edit_cancel').hide();
			dialog.find('#div_button_edit #view_edit_edit,#view_edit_cloneit,#view_edit_ask2review,#view_edit_publish').show();
			dialog.find('#div_hidden #saved').val('true');
			dialog.find('#div_hidden #clone').val('false');
			
			dialog.find('#img_unique_check').hide();
			dialog.find('div#cart_button').hide();
		}
		
		var sendSaveCommand = function(db, table, parent_id, id, oper, inputData){
			var isclone = (oper == 'cloneit');
			var wait_dialog = tool.waitingDialog();
tool.debug(wait_dialog);
			$.post('/jqgrid/jqgrid/db/' + db + '/table/' + table + '/parent/' + parent_id + '/element/' + id + '/oper/save/cloneit/' + isclone, inputData, function(data, textStatus){
				wait_dialog.dialog('close');
				if(tool.handleRequestFail(data))return;
				if (data['code'] != 0){
					alert("ERROR:\n" + data['msg']);
					return 0; // error
				}
				else{
		//alert(divId);		
					if (newNext){
						$('#' + divId + ' #div_hidden #id,#element_id').val(0);
						$('#' + divId + ' #div_view_edit #node_id').val(0);
					}
					else{
						//需要分析data['msg'], 是否node:ver格式
						var node_ver = data['msg'].split(':');
// tool.debug(node_ver);						
						if (node_ver.length > 1){
							$('#' + divId + ' #div_hidden #id,#element_id').val(node_ver[0]);
							$('#' + divId + ' #div_view_edit #node_id').val(node_ver[0]);
							$('#' + divId + ' #div_view_edit #ver_id').val(node_ver[1]);
						}
						else{
							$('#' + divId + ' #div_hidden #id,#element_id').val(data['msg']);
							$('#' + divId + ' #div_view_edit #node_id').val(data['msg']);
						}
					}
// tool.debug([divId, id, parent_id, data]);
					data.oper = oper;
					$this.view_edit_afterSave(divId, id, parent_id, data);
				}
			}, 'json');
		};

		var db = $(dialog_id + ' #div_hidden #db').val(), table = $(dialog_id + ' #div_hidden #table').val(), 
			id=$(dialog_id + ' #div_hidden #id').val(), parent_id = $(dialog_id + ' #div_hidden #parent_id').val(), oper = (cloneit == "true") ? 'cloneit':'save';
		if (oper == 'save'){
			$("<div id='div_cover_version'>").load('/jqgrid/jqgrid/db/' + db + '/table/' + table + '/parent/' + parent_id + '/element/' + id + '/oper/beforeSave', inputs['data'], function(data, textStatus){
				if(tool.handleRequestFail(data) == false){
					if (data != ''){
						var dialog_param = tool.defaultDialogParams();
						dialog_param['width'] = 800;
						dialog_param['height'] = 600;
						dialog_param['title'] = 'New or Cover';
						dialog_param['buttons'] = {
							Ok:function(){
	//$this.debug($('div#div_cover_version input[name="ver_id"]'));						
								inputs['data']['cover_ver_id'] = $('div#div_cover_version input[name="ver_id"]:checked').val(); 
	//$this.debug(inputs);							
								sendSaveCommand(db, table, parent_id, id, oper, inputs['data']);
								$(this).dialog('close');
							},
							Cancel:function(){
								$(this).dialog('close');
							}
						};
						var dialog = $(this).dialog(dialog_param);
						dialog.dialog('open');
					}
					else{
						sendSaveCommand(db, table, parent_id, id, oper, inputs['data']);
					}
				}
			
			});
		}
		else{
			sendSaveCommand(db, table, parent_id, id, oper, inputs['data']);
		}
	}
	else{
		alert(inputs['tips'].join('\n'));
		return 0;
	}
	return dialog;
};

gc_grid_action.prototype.view_edit_publish = function(p){
	var node_id = $('#' + p['divId'] + ' #div_view_edit #node_id').val();
	var ver_id = $('#' + p['divId'] + ' #div_view_edit #ver_id').val();
	var db = $('#' + p['divId'] + ' #div_hidden #db').val(), table = $('#' + p['divId'] + ' #div_hidden #table').val();
	
	var $this = this;
	var url = '/jqgrid/jqgrid/oper/publish/db/' + db + '/table/' + table;
	var checkNote = function(data){
// tool.debug("Just a Test");	
// tool.debug(data);	
		return (data.data['note'] != '');
	};
	var complete = function(){
		$this.updateInformationPage(ver_id, node_id, 'view_edit');
	}
	var params = {
		height: 400,
		width: 600,
		title: 'Publish',
		div_id: 'div_publish',
		postData:{element:JSON.stringify(node_id), ver:JSON.stringify(ver_id)},
		open: function(){
			$('#note').focus();
		}
	}
	tool.actionDialog(params, url, checkNote, complete);
};

gc_grid_action.prototype.view_edit_ask2review = function(p){
	var node_id = $('#' + p['divId'] + ' #div_view_edit #node_id').val();
	var ver_id = $('#' + p['divId'] + ' #div_view_edit #ver_id').val();
	var dialog = $('#' + p['divId']);
	var dialog_id = '#' + dialog.attr('id');
	var db = $(dialog_id + ' #div_hidden #db').val(), table = $(dialog_id + ' #div_hidden #table').val();
// tool.debug([db, table, node_id, ver_id]);
	// this.comm_action._ask2review(testcase_id, ver_id);
	var $this = this, div_id = 'div_ask2review_' + node_id;
	var url = '/jqgrid/jqgrid/oper/ask2review/db/' + db + '/table/' + table + '/element/' + node_id + '/ver/' + ver_id;
	var checkReviewer = function(data){
		var ret = true, reviewers = data['data']['reviewers'] || [], main_reviewer = data['data']['main_reviewer'];
		if (!(reviewers.length)){
			alert("Please select at least one reviewer");
			ret = false;
		}
		else if(!main_reviewer){
			alert("Please select main reviewer");
			ret = false;
		}
		return ret;
	};

	var dialog_params = {
		div_id: div_id,
		title: 'Ask to Review ' + table,
		width: 800,
		height: 500,
		postData:{element:JSON.stringify(ver_id)},
		open:function(){
			XT.datePick("#" + div_id + " #deadline");
			$("div#reviewer_groups input[name='reviewer_groups']").each(function(i){
				$(this).unbind('change').bind('change', function(){
					var groups = [];
					$("div#reviewer_groups input[name='reviewer_groups']").each(function(){if (this.checked) groups.push(this.value);});
					$.post('/useradmin/getuserlist', {groups_id:groups, role_id:4, isactive:true}, function(data){
// tool.debug(data);					
						tool.generateCheckbox("div#reviewers", 'reviewers', data);
						tool.replaceSelectOptions("select#main_reviewer", data)
					}, 'json');
					
				});
			});
		},
	};
	
	tool.actionDialog(dialog_params, url, checkReviewer, function(){
		$this.updateInformationPage(ver_id, node_id, 'view_edit');
		var grid = grid_factory.get(db, table + '_ver', {container:'edit_history_' + node_id});
		var gridId = grid.getParams('gridSelector');
		$(gridId).trigger('reloadGrid');
	});	
};

gc_grid_action.prototype.view_edit_save_input = function(divSelector){
	return tool.getAllInput(divSelector);
};

gc_grid_action.prototype.view_edit_afterSave = function(divId, id, p_id, data){
	var db = this.getParams('db'), table = this.getParams('table');
	var gridId = this.getParams('gridSelector');
	var gridOptions = $(gridId).getGridParam();
	$(gridId).trigger('reloadGrid');
	var ver_id = $('#' + divId + " #div_view_edit #ver_id").val();
// tool.debug(data);
// tool.debug(ver_id);
	if(ver_id){ //有版本管理
		var node_id = $('#' + divId + ' #node_id').val();
		var node_ver = data['msg'].split(':'), oper = data.oper;
// tool.debug([node_ver, oper]);
		if(id == 0 || oper == 'cloneit'){//更新整个页面，重定向
			location.href = "/jqgrid/jqgrid/newpage/1/oper/information/db/" + db + "/table/" + table + "/element/" + node_ver[0] + "/parent/0/ver/" + node_ver[1];
		}
		else{ 
			this.updateInformationPage(ver_id, id, 'view_edit');
			//刷新edit_history
			var grid = grid_factory.get(db, table, {container:'edit_history_' + id});
			var gridId = grid.getParams('gridSelector');
			$(gridId).trigger('reloadGrid');
		}
	}
};

gc_grid_action.prototype.view_edit_abort = function(p){
	var divId = p.divId;
	var dialog = $('#' + divId);
	
	return dialog;
};

gc_grid_action.prototype.view_edit_cloneit = function(p){
	this.view_edit_edit(p);
	
	var divId = p.divId;
	var dialog = $('#' + divId);

	dialog.find('#div_hidden #clone').val('true');
	dialog.find('#img_unique_check').attr('src', '/img/aHelp.png');	
	// 将Name类标志性字段内容加_clone
	var tag_field = ['name', 'code'];
	var field;
	for(var i in tag_field){
		field = dialog.find('#' + tag_field[i]);
//$this.debug(field);			
		if (field.length == 1){
			field.val(field.val() + '-clone');
			break;
		}
	}
	return dialog;
};

gc_grid_action.prototype.view_edit_saveAndNew = function(p){
	return this.view_edit_save(p, true);
};
