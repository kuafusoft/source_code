var tool = new kf_tool();

function gc_grid(params){
	tool.loadFile('/js/grid_action_factory.js', 'js');
	params.container = params.container || 'mainContent';
	if (params.real_table == undefined)
		params.real_table = params['table'];
// tool.debug(params);	
// alert("in gc_grid, pos_1");		
	gc_grid.supr.call(this, params);
// alert("in gc_grid, pos_2");		
	this.genId();
// alert("in gc_grid, pos_3");		
	this.grid_action = grid_action_factory.get(this, params);
// alert("in gc_grid, pos_4");		
};

tool.extend(gc_grid, gc_db_table);

gc_grid.prototype.getAction = function(){
	return this.grid_action;
};

gc_grid.prototype.genId = function(forced){ //应该和container相关，生成gridId, pagerId, gridSelector, pagerSelector等，并填入params
// alert("gc_grid, genId, pos_1");
	var container = this.getParams('container') || 'mainContent';
// alert("gc_grid, genId, pos_2");
	var base = container + '_' + this.getParams('db') + '_' + this.getParams('table');
	var gridId = base + '_list', gridSelector = '#' + gridId;
	var pagerId = base + '_pager', pagerSelector = '#' + pagerId;
	var toppagerId = gridId + '_toppager', toppagerSelector = '#' + toppagerId;
	var conditionId = base + '_cond', conditionSelector = '#' + conditionId;
	var buttonId = pagerId + '_left', buttonSelector = '#' + buttonId;
	var topbuttonId = toppagerId + '_left', topbuttonSelector = '#' + topbuttonId;
	var advancedId = base + '_advanced', advancedSelector = '#' + advancedId;
	var hideFilterId = base + '_showHide', hideFilterSelector = '#' + hideFilterId;
	var optionId = base + '_option', optionSelector = '#' + optionId;
	var hiddenId = base + '_hidden', hiddenSelector = '#' + hiddenId;
// alert("gc_grid, genId, pos_3");
	
	var p = {
		container:container,
		base:base,
		gridId:gridId, 
		pagerId:pagerId, 
		gridSelector:gridSelector, 
		pagerSelector:pagerSelector, 
		toppagerId:toppagerId,
		toppagerSelector : toppagerSelector,
		conditionId:conditionId, 
		conditionSelector:conditionSelector, 
		buttonId:buttonId, 
		buttonSelector:buttonSelector,
		topbuttonId:topbuttonId, 
		topbuttonSelector:topbuttonSelector,
		advancedButtonId:advancedId,
		advancedButtonSelector:advancedSelector,
		hideFilterButtonId:hideFilterId,
		hideFilterButtonSelector:hideFilterSelector,
		optionId:optionId,
		optionSelector:optionSelector,
		hiddenId:hiddenId,
		hiddenSelector:hiddenSelector
	};
// alert(p);	
	this.setParams(p, false);
};
	
gc_grid.prototype.selfConfig = function(config){
	return config;
};

gc_grid.prototype.ready = function(postData){
	var $this = this;
	if (postData){
		for(var i in postData){
			this.params[i] = postData[i];
//			this.setParams({:postData[i]});
		}
	}
	var base = this.getParams('base');
	var conditionSelector = this.getParams('conditionSelector');
	var advancedCheckbox = this.getParams('advancedButtonSelector');
	var hideFilterCheckbox = this.getParams('hideFilterButtonSelector');
	
	var displayButton = conditionSelector + "_display";
	var displayLabel = displayButton + "_label";
	var advancedDiv = conditionSelector + " " + conditionSelector + "_advanced";
	var filterDiv = conditionSelector + " #" + base + "_filter_div";
	
	var buttons = conditionSelector + ' :button';
	var grid_action = this.getAction();
	var gridSelector = this.getParams('gridSelector');
	var pager_help = this.getParams('gridSelector') + '_help';
	$(advancedCheckbox).button().unbind('change').bind('change', function(){
		if (this.checked){
			$(advancedDiv).show();
		}
		else
			$(advancedDiv).hide();
	});
	$(hideFilterCheckbox).button().unbind('change').bind('change', function(){
// tool.debug($(this));
		if (this.checked){
			$(filterDiv).hide();
			$(advancedCheckbox).hide();
			$(advancedCheckbox).next().hide();
			$(this).next().find('span').html('Show Filter');
		}
		else{
			$(filterDiv).show();
			$(advancedCheckbox).show();
			$(advancedCheckbox).next().show();
			$(this).next().find('span').html('Hide Filter');
		}
	});
	$(displayButton).button().unbind('change').bind('change', function(){
		if (this.checked){
			$(conditionSelector).show();
			$(conditionSelector + " input:first").focus();
			$(displayLabel).find('span').html('-');
		}
		else{
			$(conditionSelector).hide();
			$(displayLabel).find('span').html('+');
		}
	});
	
	$(buttons).each(function(i){
		$(this).button();
		var action = $(this).attr('id');
		if (action.substr(0, 5) == 'cart_')return;
		$(this).unbind('click').bind('click', function(){
			grid_action.buttonActions(action, this);
		});
	});
	
	$(pager_help + ' #grid_help_prepage').unbind('click').bind('click', {gridSelector:gridSelector}, function(event){
		var grid = $(event.data.gridSelector);
		var gridP = grid.getGridParam();
		// tool.debug(gridP);
		grid.setGridParam({page:gridP.page - 1});
		grid.trigger('reloadGrid');
	});
	$(pager_help + ' #grid_help_nextpage').unbind('click').bind('click', {gridSelector:gridSelector}, function(event){
		var grid = $(event.data.gridSelector);
		var gridP = grid.getGridParam();
		// tool.debug(gridP);
		grid.setGridParam({page:parseInt(gridP.page) + 1});
		grid.trigger('reloadGrid');
	});
	$(pager_help + ' #grid_help_column').unbind('click').bind('click', {gridSelector:gridSelector}, function(event){
		$this.grid_action.buttonActions('columns');
	});
	$(pager_help + ' #grid_help_unselect_all').unbind('click').bind('click', {gridSelector:gridSelector}, function(event){
		var grid = $(event.data.gridSelector);
		grid.resetSelection();
	});
	$(pager_help + ' #grid_help_select_all').unbind('click').bind('click', {gridSelector:gridSelector}, function(event){
		var grid = $(event.data.gridSelector);
		var ids = grid.getDataIDs();
		$.each(ids, function(i, n){
			grid.setSelection(n);
		})
	});
	$(pager_help + ' #grid_help_export').unbind('click').bind('click', {gridSelector:gridSelector}, function(event){
		$this.grid_action.buttonActions('export');
	});
	
	$(conditionSelector + ' input[date="date"]').each(function(i){
		tool.datePick(this);
	});
	
	grid_action.ready(base);
};

gc_grid.prototype.initGrid = function(options, subgrid, optionSelector){
	var $this = this;
	subgrid = subgrid || false;
	var loadContextMenu = function(config){
		if (config.contextMenuItems.length == 0)
			return;
		var gridId = $this.getParams('gridId');
		var myMenuId = gridId + '_myMenu';
		if ($("#" + myMenuId).length == 0){ // do not load context menu many times
			var html = '<ul id="' + myMenuId + '" class="contextMenu">';
			for(var href in config.contextMenuItems){
				html += '<li id="' + href + '" class="' + href + '"><a>' + config.contextMenuItems[href] + '</a></li>';
			}
			html += '</ul>';
			$("body").append(html);
		}
		$('#' + gridId + " tr").contextMenu({
			shadow:true,
			menuId:myMenuId,
			onContextMenuItemSelected:function(action, el){
				return $this.grid_action.contextActions(action, el);
			},
			onContextMenuShow:function(el){
				return $this.grid_action.onContextMenuShow(el);
			}
		});
		return;
	};
	
	var setupGrid = function(options){
		$this.genId();
		var handleOptions = function(options){
			var defaultOptions = {
				gridOptions:{
					// prmNames:{
						// page:"page",
						// rows:"rows", 
						// sort:"sidx", 
						// order:"sord", 
						// search:"_search", 
						// nd:"nd", 
						// id:"id", 
						// oper:"oper", 
						// editoper:"edit", 
						// addoper:"add", 
						// deloper:"del", 
						// subgridid:"id", 
						// npage:1, 
						// totalrows:"totalrows"
					// },
				// rownumbers: true, 
	// rownumWidth: 40, 
	// scroll:1,
					// gridview:true,
					pagerpos:'center',
					datatype:'json',
					ajaxSelectOptions:{type:'POST'},
					ajaxGridOptions:{type:'POST'},
					altRows:true,
					altclass:'ui-priority-secondary',
					mtype: 'POST',
					cellEdit: false,
					cellsubmit:'remote', 
					hiddengrid:false,
					multiselect:true,
					multiboxonly:false,
		//				rownumbers:true,
					rowNum:100, 
					rowList:[10, 25, 50, 100, 200, 'ALL'],//'0'], 
					shrinkToFit:true,
					forceFit:true,
					sortorder:"asc", 
					loadonce:false,
					autowidth:true,
					width:'1200',
					height:'auto',
					toppager:true,
					keyIndex:'id',
					pagerpos:'right',
					viewrecords:true,
					search:true,
					jsonReader:{
						root:'rows',
						page:'page',
						total:'pages',
						records:'records',
						repeatitems:false,
						cell:''
					},
					treeGridModel:'nested'
				},
				navOptions:{add:false, view:false, edit:false, search:false, del:false, cloneToTop:true},
				menuOptions:{view:true, edit:true, del:false, clone:false}, // context
				editOptions:{
					top:100, left:200, width:600, closeOnEscape:true, closeAfterEdit:true, reloadAfterSubmit: false,
					bottominfo:"Fields marked with (*) are required"
				},
				addOptions:{
					top:100, left:500, closeOnEscape:true, bottominfo:"Fields marked with (*) are required",
					afterSubmit:function(response, postdata){
						var ret = $.parseJSON(response.responseText);
						if (ret.code == 1){
		//                    alert(ret.msg);
							return [false, ret.msg, 1];
						}
						return [true, 'Success to save the record', ret.msg];            
					}
				},
				delOptions:{
					top:100, 
					left:500
				},
				viewOptions:{
					top:100, 
					left:500, 
					width:600, 
					closeOnEscape:true
				},
				buttons:{},
				lastSel: 0
			};
			var config = $.extend(true, defaultOptions, options);	
			$.each(config.gridOptions.colModel, function(i, v){
				if (v.label == undefined)
					v.label = tool.ucwords(v.name);
				if (v.index == undefined)
					v.index = v.name;
				if (v.editoptions != undefined && v.editoptions.dataInit != undefined){
					if (typeof v.editoptions.dataInit == 'string'){
						v.editoptions.dataInit = eval(v.editoptions.dataInit);
					}
				}
				if (v.addoptions != undefined && v.addoptions.dataInit != undefined){
					if (typeof v.addoptions.dataInit == 'string'){
						v.addoptions.dataInit = eval(v.addoptions.dataInit);
					}
				}
				if (v.searchoptions != undefined && v.searchoptions.dataInit != undefined){
					if (typeof v.searchoptions.dataInit == 'string'){
						v.searchoptions.dataInit = eval(v.searchoptions.dataInit);
					}
				}
			});			
// tool.debug(config.gridOptions.buttons);
			if (!config.gridOptions.multiselect)
				delete(config.gridOptions.buttons['tag']);

			config.gridOptions.resizeStop = config.gridOptions.resizeStop || function(newWidth, index){
				$this.grid_action.saveDisplayCookie(config);
			}; 

			config.gridOptions.sortable = config.gridOptions.sortable || { 
				update: function(permutation) {
					$this.grid_action.saveDisplayCookie(config);
				}
			};
			
			config.gridOptions.subGridRowExpanded = function(subgrid_id, row_id) {
				var subGrid = config.subGrid;
				$this.grid_action.expandSubGridRow(subgrid_id, row_id, subGrid);
			};
			
			if (typeof config.gridOptions.treeGrid != 'undefined' && config.gridOptions.treeGrid)
				config.gridOptions.rowNum = -1;
			config.gridOptions.inlineEdit = config.gridOptions.inlineEdit || false;
			if (config.gridOptions.inlineEdit == 'true' || config.gridOptions.inlineEdit == true){
				config.gridOptions.ondblClickRow = function(rowid, iRow, iCol, e){
					var gridId = $this.getParams('gridSelector');
					if(rowid && rowid !== config.lastSel){ 
						if (config.lastSel)
							jQuery(gridId).restoreRow(config.lastSel); 
						config.lastSel = rowid; 
					}
					jQuery(gridId).editRow(rowid, true); 
				}
			}
			
			config.gridOptions.gridComplete = function(){
				$this.grid_action.gridComplete();
				var gridId = $this.getParams('gridSelector');
				var gridP = $(gridId).getGridParam();
				$(gridId + '_help').show();
				if (gridP.lastpage != 1){
					$(gridId + '_help #grid_help_prepage').show();
					$(gridId + '_help #grid_help_nextpage').show();
					if (gridP.page == 1){
						$(gridId + '_help #grid_help_prepage').hide();//attr('disabled', 'disabled');
					}
						
					if (gridP.page == gridP.lastpage)
						$(gridId + '_help #grid_help_nextpage').hide();//attr('disabled', 'disabled');
				}
				else{
					$(gridId + '_help #grid_help_prepage').hide();
					$(gridId + '_help #grid_help_nextpage').hide();
				}
				
				// $('#mainContent').keyup(function(event){
// tool.debug(event);				
					// switch(event.keyCode){
						// case 16:
							// $(gridId + '_help #grid_help_prepage').click();
							// break;
						// case '>':
							// $(gridId + '_help #grid_help_nextpage').click();
							// break;
						
					// }
					// return false;
				// });
				
				var toppagerSelector = $this.getParams('toppagerSelector');
				if (gridP.reccount < 5){
					$(toppagerSelector).hide();
				}
				else
					$(toppagerSelector).show();
				
				// var container = $this.getParams('container');
// // tool.debug(container)				;
				// if(container == 'mainContent')
					// $(gridId).setGridWidth($('#' + container).width() - 24);
				// else
					// $(gridId).setGridWidth($('#' + container).width());
			};
			
			config.gridOptions.loadComplete = function(data){
				// bind the contextMenu
				if (config.contextMenuItems != undefined)
					loadContextMenu(config);
					
				$this.grid_action.loadComplete(data);
			};
			
			config.gridOptions.container = $this.getParams('container');
			config.gridOptions.p_db = $this.getParams('p_db');
			config.gridOptions.p_table = $this.getParams('p_table');
			config.gridOptions.p_id = $this.getParams('p_id');

			config = $this.selfConfig(config);
			return config;
		};
			
		var db = $this.getParams('db'), tb = $this.getParams('table');
		var allOptions = handleOptions(options);
		var gridSelector = $this.getParams('gridSelector');
		var pagerSelector = $this.getParams('pagerSelector');
		var toppagerSelector = $this.getParams('toppagerSelector');
		var pagerId = $this.getParams('pagerId');
		var button_nav = $this.getParams('buttonId');
		var topbutton_nav = $this.getParams('topbuttonId');
		var buttonId =  button_nav + "_buttons";
		var topbuttonId =  topbutton_nav + "_buttons";
		var tagSearchId = button_nav + '_tagSearch';
		var toptagSearchId = topbutton_nav + '_tagSearch';
// tool.debug([toppagerSelector, topbutton_nav, topbutton_nav]);
		allOptions.gridOptions.pager = pagerSelector;
//tool.debug(allOptions.gridOptions);		
		$(gridSelector).jqGrid(allOptions.gridOptions);
// tool.debug(allOptions.gridOptions);		
		
		var navGrid = $(gridSelector).jqGrid('navGrid', pagerSelector, allOptions.navOptions, allOptions.editOptions, allOptions.addOptions,
			allOptions.delOptions, allOptions.searchOptions, allOptions.viewOptions);
		var buttons = Object.keys(allOptions.buttons).length, maxButtons = 6;
		var postData = allOptions.gridOptions['postData'];
// tool.debug(postData);
// tool.debug(allOptions.buttons);
		var p_id = allOptions.gridOptions['p_id'];
		if (p_id == undefined){
			var parentId = postData['parent'] || 0;
			if (parentId == 0 && postData['filters'] != undefined){
				var filters = JSON.parse(postData['filters']);
				var rules = filters['rules'];
				parentId = rules[0]['data'];
				allOptions.gridOptions.p_id = parentId;
				$this.setParams({p_id:parentId});
			}
		}
		
		if (buttons > maxButtons){
			$('#' + button_nav + " tr").append("<td><select id='" + buttonId + "' style='width:120px'><option id='no_op' value='no_op' class='option_gray'>=Select Action=</option></select></td>");
			$('#' + topbutton_nav + " tr").append("<td><select id='" + topbuttonId + "' style='width:120px'><option id='no_op' value='no_op' class='option_gray'>=Select Action=</option></select></td>");
		}
		if (allOptions.tags != undefined){
			//附加Tag Search
			$('#' + button_nav + " tr").append("<td><select id='" + tagSearchId + "' style='width:120px'></select></td>");
			$('#' + topbutton_nav + " tr").append("<td><select id='" + toptagSearchId + "' style='width:120px'></select></td>");
		}

		for(var key in allOptions.buttons){
			if (key == 'add'){
				var add_id = 'add_'+ db + '_' + tb;
				navGrid.navButtonAdd(pagerSelector, {caption:'New', id:add_id, 'title':'Add a new record', onClickButton:function(){
					$this.grid_action.buttonActions('query_new');
				}});
				$(pagerSelector + ' #' + add_id + ' span').addClass('ui-icon-plus');
				navGrid.navButtonAdd(toppagerSelector, {caption:'New', id:add_id, 'title':'Add a new record', onClickButton:function(){
					$this.grid_action.buttonActions('query_new');
				}});
				$(toppagerSelector + ' #' + add_id + ' span').addClass('ui-icon-plus');
				continue;
			}
			allOptions.buttons[key]['onClickButton'] = tool.createFunction($this.grid_action, 'buttonActions', key);
/* 以下写法是不对的，因js是运行时绑定，在函数体内的key和buttons【key】是不同的，函数体内的key是运行该函数是的key的值，一般是buttons的最后一个索引
			allOptions.buttons[key]['onClickButton'] = function(){
				$this.grid_action.buttonActions(key, allOptions);
			}
*/			
			if (buttons > maxButtons && key != 'columns' && key != 'export'){
				$('#' + buttonId).append("<option id='" + key + "' value='" + key + "'>" + allOptions.buttons[key]['caption'] + "</option>");
				$('#' + topbuttonId).append("<option id='" + key + "' value='" + key + "'>" + allOptions.buttons[key]['caption'] + "</option>");
			}
			else{
//tool.debug(allOptions.buttons[key]);				
				var key_id = key + '_'+ db + '_' + tb;
				allOptions.buttons[key]['id'] = key_id;
				navGrid.navButtonAdd(pagerSelector, allOptions.buttons[key]);
				$(pagerSelector + ' #' + key_id + ' span').addClass('ui-icon-' + key);
				navGrid.navButtonAdd(toppagerSelector, allOptions.buttons[key]);
				$(toppagerSelector + ' #' + key_id + ' span').addClass('ui-icon-' + key);
			}
		}
		if (buttons > maxButtons){
			$('#' + buttonId + ',#' + topbuttonId).bind('change', function(event){
				var op_id = $(this).children('option:selected').val();
				$(this).children('#no_op').attr('selected', 'selected');
				$this.grid_action.buttonActions(op_id);
			});
		}

		if (allOptions.tags != undefined){
			$this.grid_action.fillTagOptions(gridSelector, tagSearchId, toptagSearchId, allOptions.tags);
			// $this.grid_action.fillTagOptions(gridSelector, toptagSearchId, allOptions.tags);
		};
		
	//xt.debug(config.gridOptions);	 

	//    $(gridId).jqGrid('sortableRows'); 

		// check if the query div exist, if existed, then do not use this.toolbar search
		var div_cond = $this.getParams('conditionSelector');
		if($(div_cond).html() == null || $(div_cond).html().trim() == ''){
	//		if ($('#' + db + '_' + tb + '_cond').html().trim() == '' &&
			if (allOptions['gridOptions']['search'] == undefined || allOptions['gridOptions']['search'] == true){
				$(gridSelector).jqGrid('filterToolbar');
			}
		}
		if (allOptions['sortableRows'])
			$(gridSelector).jqGrid('sortableRows');		
		jQuery(gridSelector).jqGrid('navGrid','#presize',{edit:false,add:false,del:false}); 
		jQuery(gridSelector).jqGrid('gridResize',{minWidth:350,minHeight:80});			
	};

	if (options.getConfig != undefined && options.getConfig == true){
		var that = this;
		$.ajax({
			url:options.config.url,
			type:'POST',
			data:options.config.data,
			dataType:'json',
			success:function(retData, textStatus){ // get the colModels
				if(tool.handleRequestFail(retData) == false){
					options = $.extend(true, retData, options);
					if (subgrid){ // 保存在前台
						var subgridSelector = optionSelector + ' #subgridoptions';
						$(subgridSelector).val(JSON.stringify(options));
					}
	// tool.debug(options);					
	// tool.debug("success to getconfig");
					setupGrid(options);
				}
			},
			error:function(XMLHttpRequest, textStatus, errorThrown){
				alert("error:" + textStatus);
			}
		});
	}
	else{
		setupGrid(options);
	}
};

gc_grid.prototype.load = function(postData, subgrid, optionSelector){
	var $this = this;
	var data = this.getParams();
	var p_db = data['p_db'], p_table = data['p_table'], p_id = data['p_id'];
	var removedParams = ['container', 'base', 'gridId', 'gridSelector', 'pagerId', 'pagerSelector', 'toppagerId', 'toppagerSelector',
		'conditionId', 'conditionSelector',	'buttonId', 'buttonSelector', 'topbuttonId', 'topbuttonSelector', 
		'optionId', 'optionSelector', 'advancedButtonId', 'advancedButtonSelector', 'hiddenId', 'hiddenSelector', 
		'hideFilterButtonId', 'hideFilterButtonSelector', 'p_db', 'p_table', 'p_id', 'p_optionSelector'];
		
	var gridOptions;
	optionSelector = optionSelector || data['optionSelector'];
	subgrid = subgrid || false;
	if (subgrid){
		// this.setParams({p_optionSelector:optionSelector}, true);
		// if($(optionSelector + ' #subgridoptions').val())gridOptions = JSON.parse($(optionSelector + ' #subgridoptions').val());
	}
	else{
		if($(optionSelector + ' #gridoptions').val())gridOptions = JSON.parse($(optionSelector + ' #gridoptions').val());
	}
// tool.debug(">>>>>>>");
// tool.debug(gridOptions);
// tool.debug("<<<<<<");

	for(var i in removedParams)
		delete data[removedParams[i]];
	postData = postData || {};
	for(var i in postData){
		data[i] = postData[i];
	}
	var editurl = '/jqgrid/jqgrid/db/' + data['db'] + '/table/' + data['table'];
	if (p_db)
		editurl += '/parentdb/' + p_db;
	if (p_table)
		editurl += '/parenttable/' + p_table;
	if (p_id)
		editurl += '/parent/' + p_id;
//tool.debug('editurl = ' + editurl);
	var options = {
		getConfig:true,
		config:{
			url:'/jqgrid/jqgrid',
			data:$.extend({oper:'getGridOptions'}, data)
		},
		gridOptions:{
			url:'/jqgrid/list',
			postData:data,
			editurl:editurl
		},
		editOptions:{
			
		}
	};
	
	var url = options.config.url, postData = options.config.data;
	if (gridOptions != undefined){
		delete gridOptions.gridOptions.postData;
		$.extend(true, options, gridOptions);
		options.getConfig = false;
		url = options.gridOptions.url;
		postData = options.gridOptions.postData;
	}
// tool.debug(options);			
	return this.initGrid(options, subgrid, optionSelector);
};

gc_grid.prototype.index = function(tabTitle){
	var $this = this;
	this.genId();
	var db = this.getParams('db'), table = this.getParams('table'), div4Tab = '#' + this.getParams('container'), conditionSelector = this.getParams('conditionSelector');
	tabTitle = tabTitle || db + ' ' + table;
	var url = '/jqgrid/index/db/' + db + '/table/' + table;
	var tabId = tabTitle.replace(/ /g, '');
	var events = {
		tabsload:function(event, ui){    
			if (ui.tab.id == tabId){
				if ($(conditionSelector).html() != null && $(conditionSelector).html().trim() == ''){
					return $this.load();
				}
			}
			return false;
		}
	};
	return tool.newTab(url, div4Tab, tabTitle, events);
};

gc_grid.prototype.indexInDiv = function(postData){
	var $this = this;
	this.genId();
	var db = this.getParams('db'), table = this.getParams('table'), div = '#' + this.getParams('container'), 
		conditionSelector = this.getParams('conditionSelector'), parent = this.getParams('parent');
	var url = '/jqgrid/index/db/' + db + '/table/' + table + '/container/' + this.getParams('container') + '/parent/' + parent;
	$(div).load(url, postData, function(data){
		if ($(conditionSelector).html() != null && $(conditionSelector).html().trim() == ''){
			return $this.load(postData);
		}
	});
};