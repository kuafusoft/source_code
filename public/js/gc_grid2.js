/*


*/
function gc_grid(params){
	this.tool.loadFile('/js/grid_action_factory.js', 'js');
	params.container = params.container || 'mainContent';
	gc_grid.supr.call(this, params);
	this.genId();
//this.tool.debug(params);	
	this.grid_action = grid_action_factory.get(this, params);
};

var tool = new kf_tool();
tool.extend(gc_grid, gc_db_table);

gc_grid.prototype.getAction = function(){
	return this.grid_action;
};

gc_grid.prototype.testthis = function(){
	this.alertthis();
};

gc_grid.prototype.alertthis = function(){
	alert("gc_grid.testthis");
};

gc_grid.prototype.genId = function(forced){ //应该和container相关，生成gridId, pagerId, gridSelector, pagerSelector等，并填入params
	var container = this.getParams('container') || 'mainContent';
	var base = container + '_' + this.getParams('db') + '_' + this.getParams('table');
	var gridId = base + '_list', gridSelector = '#' + gridId;
	var pagerId = base + '_pager', pagerSelector = '#' + pagerId;
	var conditionId = base + '_cond', conditionSelector = '#' + conditionId;
	var buttonId = pagerId + '_left', buttonSelector = '#' + buttonId;
	var advancedId = base + '_advanced', advancedSelector = '#' + advancedId;
	var optionId = base + '_option', optionSelector = '#' + optionId;
	var hiddenId = base + '_hidden', hiddenSelector = '#' + hiddenId;
	
	var p = {
		container:container,
		base:base,
		gridId:gridId, 
		pagerId:pagerId, 
		gridSelector:gridSelector, 
		pagerSelector:pagerSelector, 
		conditionId:conditionId, 
		conditionSelector:conditionSelector, 
		buttonId:buttonId, 
		buttonSelector:buttonSelector,
		advancedButtonId:advancedId,
		advancedButtonSelector:advancedSelector,
		optionId:optionId,
		optionSelector:optionSelector,
		hiddenId:hiddenId,
		hiddenSelector:hiddenSelector
	};
	
	this.setParams(p, false);
};
	
gc_grid.prototype.selfConfig = function(config){
	return config;
};

gc_grid.prototype.ready = function(postData){
	if (postData){
		for(var i in postData){
			this.params[i] = postData[i];
//			this.setParams({:postData[i]});
		}
	}
	var base = this.getParams('base');
	var conditionSelector = this.getParams('conditionSelector');
	var advancedCheckbox = this.getParams('advancedButtonSelector');
	var advancedDiv = conditionSelector + " " + conditionSelector + "_advanced";
	var buttons = conditionSelector + ' :button';
	var grid_action = this.getAction();
	$(advancedCheckbox).button().unbind('change').bind('change', function(){
		if (this.checked)
			$(advancedDiv).show();
		else
			$(advancedDiv).hide();
	});
	
	$(buttons).each(function(i){
		$(this).button();
		var action = $(this).attr('id');
		$(this).unbind('click').bind('click', function(){
			grid_action.buttonActions(action, this);
		});
	});
	grid_action.index(base);
};

gc_grid.prototype.initGrid = function(options){
	var $this = this;
// this.tool.debug("init");
//this.tool.debug(options);	
	var loadContextMenu = function(config){
		if (config.contextMenuItems.length == 0)
			return;
		var gridId = $this.getParams('gridId');
		var myMenuId = gridId + '_myMenu';
	//xt.debug(gridId);
	//xt.debug(config);    
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
				return $this.grid_action.buttonActions(action, el);
				return $this.grid_action.contextAction(action, el);
			},
			onContextMenuShow:function(el){
				if (config.onContextMenuShow != undefined){
					var fun = config.onContextMenuShow;
					if (typeof fun == 'string'){
						fun = eval(fun);
					}
					fun(el.attr('id'));
				}
			}
		});
		return;
	};
	

	var setupGrid = function(options){
		$this.genId();
	// this.tool.debug("setup");
		var handleOptions = function(options){
			var defaultOptions = {
				gridOptions:{
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
					rowNum:25, 
					rowList:[10, 25, 50, 100, 200, 'ALL'], 
					shrinkToFit:true,
					forceFit:true,
					sortorder:"asc", 
					loadonce:false,
					autowidth:true,
					width:'1200',
					height:'auto',
		//	toppager:true,
					keyIndex:'id',
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
					top:100, left:500, width:600, closeOnEscape:true
				},
				buttons:{},
				lastSel: 0
			};
			var config = $.extend(true, defaultOptions, options);	
			$.each(config.gridOptions.colModel, function(i, v){
				if (v.label == undefined)
					v.label = $this.tool.ucwords(v.name);
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
			if (config.gridOptions.inlineEdit == undefined || (config.gridOptions.inlineEdit == 'true' || config.gridOptions.inlineEdit == true)){
				config.gridOptions.ondblClickRow = function(rowid, iRow, iCol, e){
					var gridId = $this.getParams('gridSelector');
//$this.tool.debug([gridId, rowid, iRow, iCol, e, config.lastSel]);
					if(rowid && rowid !== config.lastSel){ 
						if (config.lastSel)
							jQuery(gridId).restoreRow(config.lastSel); 
						config.lastSel = rowid; 
					}
					jQuery(gridId).editRow(rowid, true); 
				}
			}
			
			config.gridOptions.loadComplete = function(data){
				// bind the contextMenu
//$this.tool.debug(config);				
				if (config.contextMenuItems != undefined)
					loadContextMenu(config);
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
		var pagerId = $this.getParams('pagerId');
		var button_nav = $this.getParams('buttonId');
		var buttonId =  button_nav + "_buttons";
		var tagSearchId = button_nav + '_tagSearch';

		allOptions.gridOptions.pager = pagerSelector;
		
		$(gridSelector).jqGrid(allOptions.gridOptions);
		
		var navGrid = $(gridSelector).jqGrid('navGrid', pagerSelector, allOptions.navOptions, allOptions.editOptions, allOptions.addOptions,
			allOptions.delOptions, allOptions.searchOptions, allOptions.viewOptions);
		var buttons = Object.keys(allOptions.buttons).length, maxButtons = 4;
		var postData = allOptions.gridOptions['postData'];
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
		
		if (buttons > maxButtons)
			$('#' + button_nav + " tr").append("<td><select id='" + buttonId + "' style='width:120px'><option id='no_op' value='no_op' class='option_gray'>=Select Action=</option></select></td>");
		if (allOptions.tags != undefined){
			//附加Tag Search
			$('#' + button_nav + " tr").append("<td><select id='" + tagSearchId + "' style='width:120px'></select></td>");
		}

		for(var key in allOptions.buttons){
			if (key == 'add'){
				var add_id = 'add_'+ db + '_' + tb;
				navGrid.navButtonAdd(pagerSelector, {caption:'', id:add_id, 'title':'Add a new record', onClickButton:function(){
					$this.grid_action.buttonActions('query_new');
				}});
				$(pagerSelector + ' #' + add_id + ' span').addClass('ui-icon-plus');
				continue;
			}
			allOptions.buttons[key]['onClickButton'] = $this.tool.createFunction($this.grid_action, 'buttonActions', key);
/* 以下写法是不对的，因js是运行时绑定，在函数体内的key和buttons【key】是不同的，函数体内的key是运行该函数是的key的值，一般是buttons的最后一个索引
			allOptions.buttons[key]['onClickButton'] = function(){
				$this.grid_action.buttonActions(key, allOptions);
			}
*/			
			if (buttons > maxButtons && key != 'columns' && key != 'export'){
				$('#' + buttonId).append("<option id='" + key + "' value='" + key + "'>" + allOptions.buttons[key]['caption'] + "</option>");
			}
			else{
//this.tool.debug(allOptions.buttons[key]);				
				navGrid.navButtonAdd(pagerSelector, allOptions.buttons[key]);
			}
		}
		if (buttons > maxButtons){
			$('#' + buttonId).bind('change', function(event){
				var op_id = $(this).children('option:selected').val();
				$(this).children('#no_op').attr('selected', 'selected');
				$this.grid_action.buttonActions(op_id);
			});
		}

		if (allOptions.tags != undefined){
			var previousTag;
			// fill out the tags for tag search
			var options = [//"<option id='op_no_op' value='no_op' class='option_gray'>==Tag Search==</option>", 
				"<option id='op_0' value='0'>=Without Tag=</option>",
				"<option id='op_refresh_tag' value='refresh_tag'>=Refresh Tag=</option>",
				"<option disabled='disabled' class='option_gray'>--------------</option>"
				];
			$.each(allOptions.tags, function(i, n){
				options.push("<option id='op_" + n['id'] + "' value='" + n['id'] + "'>" + n['name'] + "</option>");
			});
			
			$this.tool.replaceSelectOptions('#' + tagSearchId, options, previousTag);
			// bind the event for tag search
			(function () {
				$('#' + tagSearchId).focus(function () {
					// Store the current value on focus and on change
					previousTag = this.value;
				}).bind('change', allOptions, function(event){
					// Do something with the previous value after the change
					var op_id = $(this).children('option:selected').val();
					$this.tagSearchActions(op_id, gridSelector, event.data, tagSearchId);

					if (op_id !== 'refresh_tag'){
						previousTag = op_id;
					}
				});
			})();
		}
		
	//xt.debug(config.gridOptions);	 

	//    $(gridId).jqGrid('sortableRows'); 

		// check if the query div exist, if existed, then do not use this.toolbar search
	//		if ($('#' + db + '_' + tb + '_cond').html().trim() == '' &&
		if (allOptions['gridOptions']['search'] == undefined || allOptions['gridOptions']['search'] == true){
			$(gridSelector).jqGrid('filterToolbar');
		}
		if (allOptions['sortableRows'])
			$(gridSelector).jqGrid('sortableRows');		
	};

	if (options.getConfig != undefined && options.getConfig == true){
		var that = this;
		$.ajax({
			url:options.config.url,
			type:'POST',
			data:options.config.data,
			dataType:'json',
			success:function(retData, textStatus){ // get the colModels
				options = $.extend(true, retData, options);
// $this.tool.debug(options);					
// $this.tool.debug("success to getconfig");
				setupGrid(options);
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

gc_grid.prototype.load = function(postData){
	var data = this.getParams();
	var p_db = data['p_db'], p_table = data['p_table'], p_id = data['p_id'];
//this.tool.debug(data);	
	var removedParams = ['container', 'base', 'gridId', 'gridSelector', 'pagerId', 'pagerSelector', 'conditionId', 'conditionSelector', 
		'buttonId', 'buttonSelector', 'optionId', 'optionSelector', 'advancedButtonId', 'advancedButtonSelector', 'hiddenId', 'hiddenSelector', 'p_db', 'p_table', 'p_id'];
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
//this.tool.debug('editurl = ' + editurl);
	var options = {
		getConfig:true,
		config:{
			url:'/jqgrid/jqgrid',
			data:$.extend({oper:'getGridOptions'}, data),
		},
		gridOptions:{
			url:'/jqgrid/list',
			postData:data,
			editurl:editurl
		},
		editOptions:{
			
		}
	};
//this.tool.debug(options);			
	return this.initGrid(options);
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
	return this.tool.newTab(url, div4Tab, tabTitle, events);
};

gc_grid.prototype.indexInDiv = function(postData){
	var $this = this;
	this.genId();
	var db = this.getParams('db'), table = this.getParams('table'), div = '#' + this.getParams('container'), conditionSelector = this.getParams('conditionSelector');
	var url = '/jqgrid/index/db/' + db + '/table/' + table + '/container/' + this.getParams('container');
	$(div).load(url, postData, function(data){
		if ($(conditionSelector).html() != null && $(conditionSelector).html().trim() == ''){
			return $this.load(postData);
		}
	});
};