function kf_tool(){
//	this.loadedScript = {};
};

kf_tool.prototype = {
	constructor: kf_tool,
	loadedScript : {},
	debug : function($obj){
		if (window.console && window.console.log) {
			window.console.log($obj);
		}
	},
	timeStart: function($name){
		if (window.console && window.console.time) {
			window.console.time($name);
		}
	},
	timeEnd: function($name){
		if (window.console && window.console.timeEnd) {
			window.console.timeEnd($name);
		}
	},
	profile: function($name){
		if (window.console && window.console.profile) {
			window.console.profile($name);
		}
	},
	profileEnd: function($name){
		if (window.console && window.console.profileEnd) {
			window.console.profileEnd($name);
		}
	},
	isObject : function(obj){
		return (typeof obj=='object')&&obj.constructor==Object;
	},
	
	getDateStr : function(month, day){
		var today = new Date(), newM, newD;
		month = month || 0;
		day = day || 0;
		newM = today.getMonth() + month;
		newD = today.getDate() + day;
// this.debug([newM, newD]);
		today.setMonth(newM, newD);
		var dd = today.getDate();
		var mm = today.getMonth() + 1; //January is 0!
		var yyyy = today.getFullYear();

		if(dd < 10) {
			dd = '0' + dd
		} 

		if(mm < 10) {
			mm = '0' + mm
		} 
// this.debug([yyyy, mm, dd]);
		return yyyy + '-' + mm + '-' + dd;
	},
	
	getParams: function(name, obj){
		var ret;
		if (name == null || name == undefined){
			ret = {};
			for(var i in obj){
				ret[i] = obj[i];
			}
		}
		else if($.isArray(name)){
			ret = {};
			for(var i in name){
				ret[name[i]] = obj[name[i]];
			}
		}
		else{
			ret = obj[name];
		}
		return ret;
	},
	
	setParams: function(p, obj, forced){
		if(forced == undefined) forced = true;
		p = p || {};
		for(var i in p){
			if (forced || !(i in obj)) // 不覆盖
				obj[i] = p[i];
		}
		return obj;
	},
	
	delParams: function(p, obj){
		for(var i in p){
			delete obj[p[i]];
		}
		return obj;
	},
	
	extend : function(subCls,superCls) {    
		//暂存子类原型  
		var sbp = subCls.prototype;  
		//重写子类原型--原型继承  
		subCls.prototype = Object.create(superCls.prototype);
		//重写后一定要将constructor指回subCls  
		subCls.prototype.constructor = subCls;  
		//还原子类原型  
		for(var atr in sbp) {  
			subCls.prototype[atr] = sbp[atr];  
		}  
		subCls.supr = superCls;  
	},

	str2Array : function(str){
		var ret = {}, a = [], b = [];
		if (typeof str == 'string'){
			a = str.split(';');
			for(var i = 0; i < a.length; i ++){
				b = a[i].split(':');
				ret[b[0]] = b[1];
			}
		}
		else	
			return str;
		return ret;
	},
	
	sendRequest: function(url, type, postData, fun_success, fun_fail, fun_error){
		fun_success = fun_success || function(msg){
			
		};
		fun_fail = fun_fail || function(msg){
			alert("No Enough Right");
		};
		fun_error = fun_error || function(request, textStatus, errorThrown){
			alert("ERROR:" + errorThrown.getMessage());
		};
		$.ajax({
			type:type,
			url:url,
			dataType:'json',
			data: postData,
			success: function(data, textStatus){
				if(data.errCode == undefined){
					return fun_success(data);
				}
				else if(data.errCode != undefined && data.errCode == 0){
					return fun_success(data.msg);
				}
				else{
					return fun_fail(data);
				}
			},
			error: function(request, textStatus, errorThrown){
				return fun_error(request, textStatus, errorThrown);
			}
		})
	},
	
	loadFile : function(filePath, type){
		var cont;
		if (this.loadedScript[filePath] == undefined){
			type = type || 'js';
			if (type == 'js'){
				cont = '<script type="text/javascript" src="' + filePath + '"></script>'; 
			}        
			else if (type == 'css'){
				cont = '<script type="text/css" href="' + filePath + '"></script>';
			}
//this.debug(cont);		
			try{
				$('head').append(cont);    // 因异步的原因不能使用$.getScript
			}catch(e){
				// this.debug(">>>>>>>" + filePath + "<<<<<<<<");
			}
			this.loadedScript[filePath] = true;
	//this.debug(this.loadedScript);			
		}
	},

	createFunction : function(obj,func){
		var args=[];
		if(!obj) obj = XT; //window;
		if (typeof func == 'string'){
			for(var i=2;i<arguments.length;i++)args.push(arguments[i]);
			return function(){
				obj[func].apply(obj,args);
			}
		}
		else{
			return function(){
				func.apply(obj, arguments);
			}
		}
	},

	newTab : function(url, div4Tab, title, events){
		var $this = this;
		if (title == undefined)
			title = "Unknown Title";
		var id = div4Tab + ' #' + title.replace(/ /g, '');
		if($(id).length > 0){
			$(div4Tab).tabs('select', $(id).attr('href'));
		} 
		else{
			$(div4Tab).tabs({ajaxOptions:{type:'GET'}});
			if (events != undefined){
				for(var type in events){
					$(div4Tab).unbind(type).bind(type, events[type]);
				}
			}
			$(div4Tab).tabs('add', url, title);
		}
	},

	getTabId : function(ele, tabSelector){
		tabSelector = tabSelector || '#mainContent > .ui-tabs-panel';
		var tab = $(ele).parents(tabSelector);
		return tab.attr('id');
	},
		
	go : function(url){
		window.open(url);
	},
		
	ucwords : function(str){
		return (str).replace(/^([a-z])|\s+([a-z])/g, function ($1) {
			return $1.toUpperCase();
		});
	},

	clearBox : function(selector, value){
	//    debug(selector);
		if ($(selector).attr('value') == value)
			$(selector).attr('value', '');
	},

	jumpTo : function(event, selector){
		if (event.keyCode == 13){
			$(selector).focus();
			$(selector).select();
		}
		return true;
	},

	beginEdit : function(e, params){
		if (($e).attr('disabled') == true)
			$(e).attr('disable', false);
	},

	triggerButton : function(event, selector){
		if (event.keyCode == 13){
			$(selector).focus();
			$(selector).click(); // 模拟一个click
		}
		return true;
	},

	datePick : function(elem){
		if(!$(elem).attr('datepickinit')){
			$(elem).attr('datepickinit', true);
			$(elem).datepicker({dateFormat:'yy-mm-dd'});
		}
		// $(elem).click();
		// $(elem).datepicker('destroy').datepicker({dateFormat:'yy-mm-dd'});
	},

	getFileContent: function(elem, filename){
		if(!$(elem).attr('contentloaded')){
			$.post('/getfilecontent.php', {filename:filename}, function(data){
				$(elem).attr('title', data);
				$(elem).attr('contentloaded', true);
			});
		}
	},
	
	sameValue : function(tr, ignoreCols){
		var same = true;
		var lastValue;
		ignoreCols = ignoreCols || [0];
		tr.find("td").each(function(i){
			if (ignoreCols.indexOf(i) == -1) {
				currentValue = $(this).html();
				if (lastValue != undefined && lastValue != currentValue)
					same = false;
				lastValue = currentValue;
			}
		});
		return same;
	},
	
	hideTheSame : function(checkbox, event){
		var $this = this;
		var checked = $(checkbox).attr('checked');
		$(event.data.selector).each(function(){
			if (checked && $this.sameValue($(this)))
				$(this).hide();
			else
				$(this).show();
		});
	},
	
	// hilightTheNotSame : function(event){
		// var $this = this;
		// var checked = $(this).attr('checked');
// $this.debug($(this));
// $this.debug(checked);
// $this.debug(event.data);
		// $(event.data.selector).each(function(){
			// if (checked && !$this.sameValue($(this)))
				// $(this).addClass('hilight');
			// else
				// $(this).removeClass('hilight');
		// });
	// },
	
	replaceSelectOptions : function(selectSelector, options, previousSelected){
		var data = [];
		$.each(options, function(i, n){
			data.push({id:i, name:n});
		});
		$(selectSelector).find('option').remove();
		this.generateOptions($(selectSelector), data, 'id', 'name', false);
	},
	
	generateCheckbox : function(containerSelector, name, datapair, op){ //op can be replace/append
		var del = [];
		op = op || 'replace'
		$(containerSelector + " label").remove();
		$.each(datapair, function(v, label){
			if (label != undefined){
				$(containerSelector).append("<label><input type='checkbox' name='" + name + "' value='" + v + "'>" + label + "</label>");
			}
		});
	},

	isEmail : function(str){
       var reg = /^([\.a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+((\.[a-zA-Z0-9_-]{2,3}){1,2})$/;
       return reg.test(str);
	},

	resetAllInput : function(divSelector){
		var img = $(divSelector + ' #img_unique_check');
//debug(img);		
		if (img.length > 0 && !img.attr('disabled'))
			img.attr('src', '/img/aHelp.png');
		$(divSelector + " :input").each(function(i){
			$(this).removeClass('required_error');
			
			var original_value = $(this).attr('original_value');
			switch($(this).attr('type')){
				case 'button':
					break;
				case 'checkbox':
					if(original_value == '1')
						$(this).attr('checked', true);
					else if (original_value == '0')
						$(this).attr('checked', false);
					else
						$(this).attr('checked', false);
					break;
				case 'select':
				default:
					$(this).val(original_value);
					break;
			}
		});
		$(divSelector + ' :input:enabled:first').focus();
	},
	
	checkUnique : function(e, params){
//$this.debug("This is checkUnique");	
		if (!$(e).attr('unique')) return true;
		var field = $(e).attr('id');
		var img = $(e).parent().siblings('td:has(img)').find('img#img_unique_check')[0];
		var value = $(e).val();
		img.src = '/img/aHelp.png';
//$this.debug(e);		
		if (value != undefined && value != '' && value != null){
			var div = $(e).parents('div:has(#div_hidden)')[0];
			var hidden_div = $(div).find('#div_hidden');
			var db = $(hidden_div).find('#db').val() || params['db'], 
				table = $(hidden_div).find('#table').val() || params['table'], 
				isClone = $(hidden_div).find('#clone').val() || false,
				id = $(hidden_div).find('#id').val() || params['id'] || 0;
//$this.debug([db, table, isClone, id]);				
//$this.debug("isClone = " + isClone);				
			if (isClone == 'true')
				id = 0;
			$.ajax({
				url:'/jqgrid/jqgrid',
				type:'POST',
//				async: false,
				data: {oper:'checkUnique', db:db, table:table, field:field, value:value, id:id},
				success:function(data){
//alert("data = " + data);				
					if (data == '1')
						img.src = '/img/aCheck.png';
					else{
						img.src = '/img/b_drop.png';
					}
					return true;
				}
			});
		}
//$this.debug("End checkUnique");		
		return true;
	},
	
	getAllInput : function(divSelector, ignored){
		var $this = this;
// this.debug($(divSelector));	
		if(ignored == undefined)
			ignored = true;
		var $this = this;
		var params = {}, text = {};
		var passed = [];
		var inputName, label, input_id;
		var checkboxes = {}, checkboxes_text = {};
		var checkRequired = {};
		var uniqueClass = $(divSelector + ' .unique_unchecked'), uniq_id = uniqueClass.attr('id'), uniqText = $(divSelector + ' #' + uniq_id + '_label').html();
		// var uniqTd = uniqueClass.parent().pre();//.children('input;);
		// var uniqText = uniqueClass.parent().children('span:first').html();//find('input').attr('id');
//$this.debug(uniqText);		
		var tips = [];
//$this.debug(img);
//$this.debug(inputAfterImg);		
		$(uniqueClass).removeClass('unique_error');
		if (uniqueClass.length > 0/* && inputAfterImg.attr('readonly') != 'readonly'*/){
			passed.push('unique');
			tips.push(uniqText + ' is Not unique');
			$(uniqueClass).addClass('unique_error');
		}

		var selector = divSelector + " :input[multi_row_edit!='multi_row_edit']";
		if(ignored)
			selector += "[ignored!='ignored']";
// this.debug(selector);		
		$.each($(selector), function(i, n){
			var required = n.required;//$(n).attr('required');
			var disabled = $(n).attr('disabled');
// $this.debug([i,$(n).attr('type'), n]);			
			if ($(n).attr('name') !== undefined)
				inputName = $(n).attr('name');
			else if ($(n).attr('id') !== undefined)
				inputName = $(n).attr('id');
			else{
//				alert("NO NAME, NO ID");
				return;
			}
			
			input_id = $(n).attr('id');
			// 检查是否存在[],如存在，则去除
			var lastIndexOf = inputName.lastIndexOf('[]');
			if (lastIndexOf != -1)
				inputName = inputName.substring(0, lastIndexOf);
			if(input_id != undefined)
				label = $('#' + input_id + '_label').html();
			else
				label = $(n).parents('.cont-td').prev('td.e-pre').children('span:first').html();
			var n_type = $(n).attr('type');
// $this.debug(n_type);			
			switch(n_type){
				case 'button':
					//检查是否Cart Button
					var cartTable = $(n).attr('cart');
					required = $(n).attr('required');
					if (cartTable != undefined){
						if (checkboxes[cartTable] == undefined)
							checkboxes[cartTable] = [];
						if (required)
							checkRequired[cartTable] = 1;
						$.each($('table_cart_' + cartTable + ' :checkbox'), function(j, m){
							if(m.checked){
								checkboxes[cartTable].push($(m).val());
								if(checkboxes_text[cartTable] == undefined)
									checkboxes_text[cartTable] = [];
								checkboxes_text[cartTable].push($(m).text());
							}
						});
					}
					break;
					
				case 'checkbox':
// $this.debug(inputName);
					if (checkboxes[inputName] == undefined)
						checkboxes[inputName] = [];
					if (required)
						checkRequired[inputName] = 1;
					if (n.checked){//$(n).attr('checked')){
						checkboxes[inputName].push($(n).val());
						if(checkboxes_text[inputName] == undefined)
							checkboxes_text[inputName] = [];
						checkboxes_text[inputName].push($(n).text());
					}
					break;
				case 'radio':
					params[inputName] = $(':radio[name=' + inputName + ']:checked').val();
					text[inputName] = $(':radio[name=' + inputName + ']:checked').attr('label') || $(n).parent('label').text();
					break;
				default:
					params[inputName] = $(n).val();
					text[inputName] = $(n).text();
// $this.debug(params);
// $this.debug(text);					
					if(n_type == 'select'){
						text[inputName] = $(n).find("option:selected").text();
					}
					else if(n_type == 'textarea'){
						text[inputName] = $(n).val();
					}
					else if(n_type == 'text')
						text[inputName] = $(n).val();
					if($(n).val() == null || params[inputName] == '' || params[inputName] == undefined || (params[inputName] == 0 && $(n).attr('type') == 'select')){
						params[inputName] = '';
						text[inputName] = '';
						if (required && disabled == undefined){
							passed.push(inputName);// = false;
							tips.push(label + ' is required');
							$(n).addClass('required_error');
						}
					}
					else{
						$(n).removeClass('required_error');
						var invalidChar = $(n).attr('invalidchar');
						if(invalidChar != undefined && invalidChar != ''){
							var pattern = new RegExp(invalidChar);
							if (pattern.test($(n).val())){
								passed.push(inputName);
								tips.push(label + ' has invalid char :' + invalidChar);
								$(n).addClass('required_error');
								$(n).attr('title', "There're invalid char " + invalidChar);
							}
						}
						var minval = $(n).attr('min'), maxval = $(n).attr('max'), intval = Number($(n).val());
						if (minval != undefined){
							if (intval < minval){
								passed.push(inputName);
								tips.push(label + ' must be >= ' + minval);
								$(n).addClass('required_error');
							}
						}
						if (maxval != undefined){
							if (intval > maxval){
								passed.push(inputName);
								tips.push(label + ' must be <= ' + maxval);
								$(n).addClass('required_error');
							}
						}
						if ($(n).attr('email') != undefined){
							if (!$this.isEmail($(n).val())){
								passed.push(inputName);
								tips.push(label + ' is NOT match the Email format');
								$(n).addClass('required_error');
								$(n).attr('title', "Not Email");
							}
						}
					}
			}
		})
	//debug(params);    	
		for(i in checkboxes){
			params[i] = checkboxes[i];
			text[i] = checkboxes_text[i];
			// 需要检查Checkbox是否required
			var fieldset = $("fieldset#fieldset_" + i);
			label = $(fieldset).parents('td.cont-td').prev('td.e-pre').children('span:first').html();
			if (checkRequired[i] != undefined && (params[i].length == 0 || params[i] == 0)){
				$(fieldset).addClass('required_error');
				passed.push(i);
				tips.push(label + ' is required');
			}
			else
				$(fieldset).removeClass('required_error');
		}
		// 检查有没有multirow
		$(divSelector + ' div[multirowedit="multirowedit"]').each(function(i_div){
			var prefix = $(this).attr('id');
			var valuesTable = '#' + prefix + '_values';
			var values = [];
			$(valuesTable + ' > tbody > tr').each(function(i){
				var tr, row = {};
				if(i == 0)
					return true;
				$(this).find(':input').each(function(j){
					id = $(this).attr('id');
					if(id != 'del')
						row[id] = $(this).val();
				})
				values.push(row);
			})
			params[prefix] = params[prefix] || {};
			params[prefix]['data'] = values;
		});
		return {passed:passed, data:params, tips:tips, text:text};
	},
	
	getHidden : function(divSelector){
		var hidden = {}
		$(divSelector + ' :hidden').each(function(){
			hidden[$(this).attr('id')] = $(this).val();
		});
		return hidden;
	},
	
	checkSelectedRows : function(selectedRows, limit, tips){
		var atleast, most, strTip = '', passed = true;;
		if (typeof limit == 'number')
			atleast = limit;
		else if (typeof limit == 'object'){
			atleast = limit.min;
			most = limit.max;
		}
		if(typeof selectedRows == 'object')
			selectedRows = selectedRows.length;
		
		if (atleast != undefined && selectedRows < atleast){
			strTip += " at least " + atleast;
			passed = false;
		}
		if (most != undefined && selectedRows > most){
			if (strTip.length > 0)
				strTip += ' AND ';
			strTip += " at most " + most;
			passed = false;
		}
		if(selectedRows == undefined)
			passed = false;
		if (!passed){
			strTip = 'Please Select ' + strTip + ' Record(s)';
			tips = tips || strTip;
		}
		
		if (!passed)
			alert(tips);
		return passed;
	},

	handleRequestFail: function(data){
		var ret = false;
		var $this = this;
		if(data == null) return false;
		if(typeof data == 'string'){
			var res = data.match(/^\{"errCode":\d+\}/);
	$this.debug(res);
			if(res != null){
				ret = $this.requestFail(JSON.parse(data));
			}
		}
		else if(data.errCode != undefined){
			ret = $this.requestFail(data);
		}
		return ret;
	},
	
	requestFail: function(data){
		switch(data.errCode){
			case 3: // not logined
				this.noticeDialog("You have no logined yet, please login first", "warning", undefined, 400, 200);
				break;
			
			case 4: // NO enough privilege
				this.noticeDialog("You have no enough privilege to do the operation", "warning", undefined, 400, 200);
				break;
		}
		return data.errCode;
	},

	defaultForActionDialog : function(params, closeDialog){
		var $this = this;
		var data = $this.getAllInput('#' + params['div_id']);
// $this.debug(params);
// $this.debug(data);
		var validated = data['passed'].length == 0;
		var data_params = data['data'];
		if (closeDialog == undefined)
			closeDialog = true;
//debug(params);    			
//debug(data_params);
		if (params['fun_validation'] !== undefined){
			validated = params['fun_validation'](data);
		}
		if(validated){
//debug(myDialog);				
			if (closeDialog)
				$('#' + params['div_id']).dialog( "close" );
			if (params['postData'])
				$.extend(true, data_params, params['postData']);
			var dialog = $this.waitingDialog();
			$.post(params['text'], data_params, function(data){
				dialog.dialog('close');
				if (params['fun_complete'] !== undefined){
					params['fun_complete'](data, params);
				}
			});
		}
		else if (data['tips'].length > 0)
			alert(data['tips'].join('\n'));
	},
	
	defaultDialogParams : function(){
		return {
			width:1024,
			height:800,
			autoOpen: false,
			title: 'Dialog',
			html_type:'text', // text or url
			text:'Notice....',
			modal: true,
			div_id:'div_id_tmp',
			zIndex:900,
			
			close: function(event, ui){
				$(this).html('');
				$(this).remove();
			},
			resize: function(event, ui){
				$(this).find('.ui-jqgrid-btable').setGridWidth($(this).width() - 45);
			}
		};
	},
		
	popDialog : function(dialogParams){
		var myDialog;
		var $this = this;
		var params = this.defaultDialogParams();
		dialogParams = dialogParams || {};
		
		$.extend(true, params, dialogParams);
		if (params['html_type'] == 'text'){
			myDialog = $('<div id="' + params['div_id'] + '"></div>')
				.html(params['text'])
				.dialog(params);
			myDialog.dialog('open');
		}
		else{
			var wait_dialog = $this.waitingDialog();
			$('<div id="' + params['div_id'] + '"></div>').load(params['text'], function(data){
				wait_dialog.dialog('close');
				if($this.handleRequestFail(data) == false){
					myDialog = $(this).dialog(params);
					myDialog.dialog('open');
				}
			});
		}
		return myDialog;
	},

	waitingDialog : function(dialogParams){
		dialogParams = dialogParams || {};
		dialogParams['modal'] = true;
		dialogParams['width'] = dialogParams['width'] || 300;
		dialogParams['height'] = dialogParams['height'] || 100;
		dialogParams['title'] = dialogParams['title'] || 'Processing...';
		dialogParams['text'] = dialogParams['text'] || 'Processing, please wait a moment...';
		return this.popDialog(dialogParams);
	},

	noticeDialog : function(text, title, okbutton, width, height){
		var dialogParams = {html_type:'text', text:text, title:title, width:width || 500, height:height || 400};
		if(okbutton == undefined)
			dialogParams['buttons'] = {Close:function(){$(this).dialog('close');}};
		return this.popDialog(dialogParams);
	},

	optionsDialog : function(text, title, buttons, width, height){
		var dialogParams = {html_type:'text', text:text, title:title, width:width || 500, height:height || 400};
		dialogParams['buttons'] = buttons;
		return this.popDialog(dialogParams);
	},

	actionDialog : function(dialog_params, url, fun_validation, fun_complete){
		var $this = this;
		var dialogParams = $.extend(true, dialog_params, {html_type:'url', text:url, fun_validation:fun_validation, fun_complete:fun_complete});
	//$this.debug(dialogParams);
		if (dialogParams['buttons'] == undefined){
			dialogParams['buttons'] = {
				Ok:function(){
					$this.defaultForActionDialog(dialogParams);
				}, 
				Cancel:function(){
					$(this).dialog('close');
				}
			};
		}
		return this.popDialog(dialogParams);
	},

	dialogLoadGrid : function(db, table, dialog_params, postData){
		var container = 'dialog_grid', base = container + '_' + db + '_' + table, table_id = base + '_list', pager_id = base + '_pager';
		postData = postData || {};
	
		dialog_params = dialog_params || {};
		dialog_params.div_id = container;
		// dialog_params.html_type = 'text';
		// dialog_params.text = '<table id="' + table_id + '"></table><div id="' + pager_id + '"></div>';
		dialog_params.html_type = 'url';
		dialog_params.text = 'jqgrid/index/db/' + db + '/table/' + table +'/container/' + container;
		dialog_params.open = function(){
			var grid = grid_factory.get(db, table, {container:container});
			grid.ready(postData);
		};
		this.popDialog(dialog_params);
	},
	
	defaultActionForTab : function(tabId, selected){
		var $this = this;
		selected = selected || 0;
		$(tabId + ' input[date="date"]').each(function(i){
			$this.datePick(this);
		});
		var disabled = [];
		// gather the all disabled information to disable some tab-page
	//debug(tabId + ' li:disabled');			
		$(tabId + ' li').each(function(i){
	//debug(this);				
			if ($(this).attr('disabled') == 'disabled'){
				disabled.push(i);
				$(this).removeAttr('disabled');
				if (selected == i)
					selected ++;
			}
		});
		
	//		$(tabId).tabs('destroy').tabs({selected: 'tabs-current', disabled:disabled});		
		$(tabId).tabs('destroy').tabs({selected: selected, disabled:disabled});		
	},

	tabDialog : function(dialog_params, url){
		var $this = this;
		var func_tab = function(event, ui, url){
			var tabId = dialog_params['tabId']; 
			$this.defaultActionForTab(tabId);
		};
		var dialogParams = $.extend(true, dialog_params, {html_type:'url', text:url});
		var openFunc = dialogParams['open'];
		dialogParams['open'] = function(){
			func_tab();
			if (openFunc)
				openFunc();
		};
//this.debug(dialogParams);
		return this.popDialog(dialogParams);
	},
	
	// 联动，source和linked都是Object，source = {selector:**, type:**, field:**}, linked = [{selector:**, type:**}]
	// source的类型限制为:select, checkbox, radio, text
	// linked的类型限制为:select, checkbox, radio, text
	linkage: function(source, linked, params){
		params = params || {};
		var $this = this;
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
// $this.debug(params);			
// $this.debug(newParams);			
			$.post(target.url, newParams, function(data){
// $this.debug(data);
				if (data.nochange != undefined && data.nochange == 1)
					return;
				if (target.type == undefined)
					target.type = 'select';
				switch(target.type){
					case 'select':
						var currentVal = oldVal = $(target.selector).val();
						$(target.selector).find('option').remove();
						var existed = $this.generateOptions($(target.selector), data, 'id', 'name', true, currentVal);
						var trigger = true;
// $this.debug(data.length);
						if(data.length == 1){
							if(data[0]['id'] != currentVal){
								currentVal = data[0]['id'];
								trigger = true;
							}
							else	
								trigger = false;
						}
						else if(existed){
							trigger = false;
						}
// tool.debug([target.selector, oldVal, data, trigger]);
// tool.debug("source_val = " + source_val);
						if(source_val != 0)
							$(target.selector).val(currentVal);
						if(trigger)
							$(target.selector).trigger('change');
						break;
					case 'checkbox':
						break;
					case 'radio':
						break;
					case 'text':
						if(target.update != undefined){
							for(var i in target.update){
								$(target.update[i]).val(data[i]);
							}
						}
						else{
							for(var i in data){
								$(target.selector + '_' + i).val(data[i]);
							}
						}
						break;
					case 'div':
// $this.debug([target, data]);
// $this.debug($(target.selector));
$this.debug(data['html']);
						$(target.selector).html(data['html']);
						break;
				}
				if(target.moreOp != undefined){
					$this.doMoreOp(target.moreOp, data, target);
				}
			}, 'json');
		};
		var fun_linkage = function(event){
// tool.debug(event);			
			for(var i in event.data.linked){
				fun_oneLink(event.data.linked[i], event.data.params, $(this).val());
			}
		};
// $this.debug(source.selector);		
		$(source.selector).each(function(i){
// $this.debug("i = " + i);
			$(this).unbind('change', fun_linkage).bind('change', {linked:linked, params:params}, fun_linkage);
			//初始化设置：读取source的当前值，根据当前值设置关联的内容
			$(this).trigger('change');
		});
	},

	doMoreOp : function(more, data, target){
		var action = more.action, fun = more.fun;
		return action[fun](data, target, more);
	},
	
	auto_fill_calc_result : function($dest, $source, $op, decimal){
		var result;
		var $this = this;
		$.each($source, function(i, n){
			var v = $(n).val();
// $this.debug([i, n, v]);
			switch($op){
				case '+':
					if(result == undefined)
						result = v;
					else
						result += v;
					break;
				case '*':
					if(result == undefined)
						result = v;
					else
						result *= v;
					break;
			}
		});
		decimal = decimal || 0;
		switch(decimal){
			case 0:
				result = Math.round(result);
				break;
			case 1:
				result = Math.round(result * 10) / 10;
				break;
			case 2:
				result = Math.round(result * 100) / 100;
				break;
		}
		$($dest).val(result);
	},
	
	checkElement : function(e, params){
//$this.debug(params);		
//		var params = json_decode(strParams);
		this.checkRequiredElement(e, params);
		this.checkUnique(e, params);
		return false;
	},
	
	checkRequiredElement : function(e, params){
		var field = $(e).attr('id');
		var value = $(e).val();
		var required = $(e).attr('required');
		var passed = true;
//debug(required);		
		if (required == 'required' && (value == undefined || value == null || value == '')){
			$(e).addClass('required_error');
			e.title = 'This field cannot be empty';
			passed = false;
		}
		else
			$(e).removeClass('required_error');
//debug(passed);		
		return passed;
	},
	
	checkUnique : function(e, params){
//$this.debug("This is checkUnique");	
		if (!$(e).attr('unique')) return true;
		params = params || {};
		var field = $(e).attr('id');
		// var img = $(e).parent().siblings('td:has(img)').find('img#img_unique_check')[0];
		var value = $(e).val();
		// img.src = '/img/aHelp.png';
//$this.debug(e);		
		if (value != undefined && value != '' && value != null){
			var div = $(e).parents('div:has(#div_hidden)')[0];
			var hidden_div = $(div).find('#div_hidden');
			var db = params['db'] || $(hidden_div).find('#db').val(), 
				table = params['table'] || $(hidden_div).find('#table').val(), 
				isClone = $(hidden_div).find('#clone').val() || false,
				id = params['id'] || $(hidden_div).find('#id').val() || 0;
//$this.debug([db, table, isClone, id]);				
//$this.debug("isClone = " + isClone);				
			if (isClone == 'true')
				id = 0;
			$.ajax({
				url:'/jqgrid/jqgrid',
				type:'POST',
//				async: false,
				data: {oper:'checkUnique', db:db, table:table, field:field, value:value, id:id},
				success:function(data){
//alert("data = " + data);				
					$(e).removeClass('unique_unknown unique_unchecked unique_checked unique_error');
					if (data == '1'){
						$(e).addClass('unique_checked')
						// img.src = '/img/aCheck.png';
					}
					else{
						$(e).addClass('unique_unchecked unique_error');
						// img.src = '/img/b_drop.png';
					}
					return true;
				}
			});
		}
//$this.debug("End checkUnique");		
		return true;
	},
	
	bindOptions : function(event){
		$.post(event.url, event.data, function(data){
			event.target.find('option').remove();
			this.generateOptions(event.target, data, 'id', 'name', event.blankItem);
		}, 'json');
	},

	generateOptions : function(select, data, value, title, blankItem, currentVal){
		var existed = false;
		var $this = this;
		value = value || 'id';
		title = title || 'name';
		blankItem = blankItem || false;
		currentVal = currentVal || 0;
		if (blankItem){
			select.append('<option value="0"> </option>');
			if(currentVal == 0)
				existed = true;
		}
		$.each(data, function(i, n){
			if(currentVal && currentVal == n[value])
				existed = true;
			//可以将data里的所有字段都打包到option里
			select.append($this.generateOptionStr(n, value, title));
			
			// var optionProp = [];
			// for(f in n){
				// if(f == title)
					// continue;
				// if(f == value)
					// optionProp.push('value="' + n[value] + '"');
				// else
					// optionProp.push(f + '="' + n[f] + '"');
			// }
			// select.append('<option ' + optionProp.join(' ') + '>' + n[title] + '</option>');
			// select.append('<option value=' + n[value] + '>' + n[title] + '</option>');
		});
		return existed;
		// if(data.length == 1)
			// select.find("option[value='" + data[0][value] + "']").attr('selected', true);
	},
	
	generateOptionStr : function(item, value, title){
		var optionProp = [];
		for(f in item){
			if(f == title)
				continue;
			if(f == value)
				optionProp.push('value="' + item[value] + '"');
			else if(f == 'note')
				optionProp.push('title="' + item['note'] + '"');
			else
				optionProp.push(f + '="' + item[f] + '"');
		}
		return '<option ' + optionProp.join(' ') + '>' + item[title] + '</option>';
	},
	
	single2multi2 : function(div){
this.debug("single2multi2");		
this.debug(div);		
		var	se = div.children('select'),
			se_id = se.attr('id'), 
			single_multi = JSON.parse(div.attr('single_multi')),
			cart_db = single_multi['db'], 
			cart_table = single_multi['table'], 
			cart_data = single_multi['data'] || "{}", 
			cols = single_multi['cols'] || 4,
			required = se.attr('required'),
			label = single_multi['label'] || se_id,
			onAddClick = " onclick='XT.selectToCart(\"" + se_id + "\", \"" + cart_db + "\", \"" + cart_table + "\", \"" + label + "\", " + cart_data + ")'",
			onResetClick = " onclick='XT.resetCart(\"" + se_id + "\", \"" + cart_db + "\", \"" + cart_table + "\", \"" + label + "\", " + cart_data + ")'",
			str = [];
		str.push("<fieldset id='fieldset_" + se_id + "'>");
		str.push("<table cols='" + cols + "' id='table_cart_" + se_id + "' style='width:100%'></table></fieldset>");

		str.push("<div id='cart_button' style='display:none'>");
		str.push("<button type='button' editable='1' id='cart_add_" + se_id + "' cart='" + se_id + "'");
		if(required)
			str.push(" required='" + required + "'");
		str.push(onAddClick);
		str.push(">Add</button>");
		
		str.push("<button type='button' editable='1' id='cart_reset_" + se_id + "' cart='" + se_id + "'");
		if(required)
			str.push(" required='" + required + "'");
		str.push(onResetClick);
		str.push(">Reset</button>");
		str.push("</div>");
		
		return str.join('');
	},
	
	single2multi : function(div){
		var	se = div.children('select'),
			se_id = se.attr('id'), 
			single_multi = JSON.parse(div.attr('single_multi')),
			cart_db = single_multi['db'], 
			cart_table = single_multi['table'], 
			cart_data = single_multi['data'] || "{}", 
			cols = single_multi['cols'] || 4,
			disabled = se.attr('disabled'),
			editable = se.attr('editable'),
			required = se.attr('required'),
			label = single_multi['label'] || se_id,
			onMouseOut = " onmouseout='XT.hideCartButton(\"div_cart_" + se_id + "\")'",
			onMouseOver = " onmouseover='XT.showCartButton(\"div_cart_" + se_id + "\")'",
			onAddClick = " onclick='XT.selectToCart(\"" + se_id + "\", \"" + cart_db + "\", \"" + cart_table + "\", \"" + label + "\", " + cart_data + ")'",
			onResetClick = " onclick='XT.resetCart(\"" + se_id + "\", \"" + cart_db + "\", \"" + cart_table + "\", \"" + label + "\", " + cart_data + ")'",
			str = [];
		str.push("<div id='div_cart_" + se_id + "' prop_edit='disabled' single_multi='" + se.attr('single_multi') + "'");
		if(disabled)
			str.push(" disabled='" + disabled + "'");
		if(editable)
			str.push(" editable='" + editable + "'");
		else
			onMouseOver = '';
		str.push(onMouseOut);
		str.push(onMouseOver);
		str.push(">");
		str.push("<fieldset id='fieldset_" + se_id + "'>");
		str.push("<table cols='" + cols + "' id='table_cart_" + se_id + "' style='width:100%'></table></fieldset>");

		str.push("<div id='cart_button' style='display:none'>");
		str.push("<button type='button' editable='1' id='cart_add_" + se_id + "' cart='" + se_id + "'");
		if(required)
			str.push(" required='" + required + "'");
		str.push(onAddClick);
		str.push(">Add</button>");
		
		str.push("<button type='button' editable='1' id='cart_reset_" + se_id + "' cart='" + se_id + "'");
		if(required)
			str.push(" required='" + required + "'");
		str.push(onResetClick);
		str.push(">Reset</button>");
		str.push("</div>");
		str.push("</div>");
		
		return str.join('');
	},	
	single2multi : function(div){
		var	se = div.children('select'),
			se_id = se.attr('id'), 
			single_multi = JSON.parse(div.attr('single_multi')),
			cart_db = single_multi['db'], 
			cart_table = single_multi['table'], 
			cart_data = single_multi['data'] || "{}", 
			cols = single_multi['cols'] || 4,
			disabled = se.attr('disabled'),
			editable = se.attr('editable'),
			required = se.attr('required'),
			label = single_multi['label'] || se_id,
			onMouseOut = " onmouseout='XT.hideCartButton(\"div_cart_" + se_id + "\")'",
			onMouseOver = " onmouseover='XT.showCartButton(\"div_cart_" + se_id + "\")'",
			onAddClick = " onclick='XT.selectToCart(\"" + se_id + "\", \"" + cart_db + "\", \"" + cart_table + "\", \"" + label + "\", " + cart_data + ")'",
			onResetClick = " onclick='XT.resetCart(\"" + se_id + "\", \"" + cart_db + "\", \"" + cart_table + "\", \"" + label + "\", " + cart_data + ")'",
			str = [];
		str.push("<div id='div_cart_" + se_id + "' prop_edit='disabled' single_multi='" + se.attr('single_multi') + "'");
		if(disabled)
			str.push(" disabled='" + disabled + "'");
		if(editable)
			str.push(" editable='" + editable + "'");
		else
			onMouseOver = '';
		str.push(onMouseOut);
		str.push(onMouseOver);
		str.push(">");
		str.push("<fieldset id='fieldset_" + se_id + "'>");
		str.push("<table cols='" + cols + "' id='table_cart_" + se_id + "' style='width:100%'></table></fieldset>");

		str.push("<div id='cart_button' style='display:none'>");
		str.push("<button type='button' editable='1' id='cart_add_" + se_id + "' cart='" + se_id + "'");
		if(required)
			str.push(" required='" + required + "'");
		str.push(onAddClick);
		str.push(">Add</button>");
		
		str.push("<button type='button' editable='1' id='cart_reset_" + se_id + "' cart='" + se_id + "'");
		if(required)
			str.push(" required='" + required + "'");
		str.push(onResetClick);
		str.push(">Reset</button>");
		str.push("</div>");
		str.push("</div>");
		
		return str.join('');
	},
	
	multi2single : function(div){
// this.debug(div);		
		var se_id = div.attr('id').substr(9),
			disabled = div.attr('disabled'),
			editable = div.attr('editable'),
			style = div.attr('style'),
			css = div.attr('class'),
			single_multi = JSON.parse(div.attr('single_multi')),
			options = single_multi.options,
			size = options.size;
// this.debug(options);
		var str = [];
		str.push("<select type='select' id='" + se_id + "' name='" + se_id + "' prop_edit='disabled'");
		if(size)
			str.push(" size='" + size + "'");
		if(editable)
			str.push(" editable='" + editable + "'");
		if(disabled)
			str.push(" disabled='" + disabled + "'");
		if(style)
			str.push(" style='" + style + "'");
		else	
			str.push(" style='width:100%'");
		if(css)
			str.push(" class='" + css + "'");
		else
			str.push(" class='ces'");
		str.push(" single_multi='" + div.attr('single_multi') + "'");
		str.push(">");
		
		var optionData = this.str2Array(options.value);
		var $this = this;
		$.each(optionData, function(i, n){
			if(typeof n == 'object'){
				str.push($this.generateOptionStr(n, 'id', 'name'));
			}
			else
				str.push($this.generateOptionStr({id:i, name:n}, 'id', 'name'));
		});
		str.push("</select>");
		
		return str.join('');
	},
	
	addToCart : function(e_name, newAdded){//tableSelector, newAdded, checkboxName){
		var tableSelector = '#div_cart_' + e_name + ' #table_cart_' + e_name;
		var cols = $(tableSelector).attr('cols');
		var str = '';
		var currents = {};
		var currentCol = 0;
		$(tableSelector + ' :checkbox').each(function(i){
			var id = $(this).val();
			currents[id] = $(this);
		});
		for (var i in newAdded){
			//检查该值是否已经存在，避免重复显示
			//如果已经存在，则Check，否则添加
			if (currents[i]){
				currents[i].attr('checked', true);
			}
			else{
				if (currentCol == 0)
					str += "<tr newadd='newadd'>";
				str += "<td><label><input type='checkbox' checked='checked' editable='editable' value='" + i + "' name='" + e_name + "[]'>" + newAdded[i] + "</label></td>";
				currentCol ++;
				if (currentCol == cols){
					str += "</tr>";
					currentCol = 0;
				}
			}
		}
		$(tableSelector).append(str);
	},
	
	selectToCart : function(e_name, db, table, title, postData){
		var $this = this;
		// 获取Projects
		var container = "select_cart", 
			gridId = '#' + container + '_' + db + '_' + table + '_list', 
			pagerId = '#' + container + '_' + db + '_' + table + '_pager';
		var url='/jqgrid/index/db/' + db + '/table/' + table + '/container/' + container;
		var defaultParams = this.defaultDialogParams();
//		postData = JSON.parse(postData);
// this.debug(postData);		
		postData['table'] = table;
		postData['db'] = db;
		var dialog_params = {
			div_id: container,
			title: 'Select ' + title,
			width: 1024,
			height: 600,
			close: function(event, ui){
				$(this).html('');
				$(this).remove();
			},
			open:function(){
				var grid = grid_factory.get(db, table, {container:container});
//				var optionSelector = grid.getParams('optionSelector');
				return grid.load(postData);//, false, optionSelector);
			}
		};
		var dialogParams = $.extend(true, defaultParams, dialog_params, {html_type:'url', text:url});
		var buttons = {
			'Add ': function(){
				//需要得到id和name
				var selectedPrj = $(gridId).getGridParam('selarrrow');
				var prjs = {};
				var value = '';
				for(var i in selectedPrj){
					value = $(gridId).getCell(selectedPrj[i], 'name');
					if (value == false)
						value = $(gridId).getCell(selectedPrj[i], 'code');
					prjs[$(gridId).getCell(selectedPrj[i], 'id')] = value;
					if(db=='useradmin' && table=='users')
						prjs[$(gridId).getCell(selectedPrj[i], 'id')] = $(gridId).getCell(selectedPrj[i], 'nickname');
				}
				$this.addToCart(e_name, prjs);
				$(this).dialog('close');
			},
			Close: function(){
				$(this).dialog('close');
			}
		};
		dialogParams['buttons'] = buttons;
		return $this.actionDialog(dialogParams, url);		
	},
	
	resetCart : function(e_name){
		$('div#div_cart_' + e_name + ' table#table_cart_' + e_name + ' tr[newadd="newadd"]').remove();
	},
	
	clearCart : function(e_name){
		$('div#div_cart_' + e_name + ' table#table_cart_' + e_name + ' tbody').remove();
	},
	
	addNewRowForMulti : function(prefix){
		var $this = this;
		var temp = '#' + prefix + '_temp', valuesTable = '#' + prefix + '_values';
		var row, id, td = [];
		row = $("<tr><td id='del'><a onclick='javascript:XT.deleteSelfRow(this)' href='javascript:void(0)'>X</a></td></tr>");
		var vs = this.getAllInput(temp, false);
this.debug(vs);		
		$(valuesTable + " tr#" + prefix + "_header th").each(function(i){
// $this.debug($(this));
			var id = $(this).attr('id');
			if(id == 'del')
				return;
// $this.debug(id);			
			var v =vs['data'][id], t = vs['text'][id];
			var td = "<td><input type='hidden' value='" + v + "' id='" + id + "' multi_row_edit='multi_row_edit'>" + t + "</td>";
			// var input = $(this).children()[0];
			// var input_val = $(input).val();
			// // var td = "<td><input type='hidden' value='" + input_val + "' id="
			// // tool.debug(" i = " + i + ", value = " + $(input).val());
			// var td = $(this).clone();
			// $(td.children()[0]).val(input_val);
			row.append(td);
		})
		if(vs.passed.length > 0){
			alert(vs.tips.join('\n'));
		}
		else{
			$(valuesTable).append(row);//"<tr>" + td.join() + "</tr>");
		}
	},

	hide: function(selector){
		$(selector).hide();
	},
	
	show: function(selector){
		$(selector).show();
	}

};
