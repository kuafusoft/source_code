// JavaScript Document
//var g_webRoot = 'http://www.test.com';

function more_comment(e){
	if ($(e).html() == 'More'){
		$('#more_comment').show();
		$(e).html('Collaps');
	}
	else{
		$('#more_comment').hide();
		$(e).html('More');
	}
}


(function( $ ) {
	$.widget( "ui.combobox", {
		_create: function() {
			var self = this,
				select = this.element.hide(),
				selected = select.children( ":selected" ),
				value = selected.val() ? selected.text() : "";
			var input = this.input = $( "<input>" )
				.insertAfter( select )
				.val( value )
				.autocomplete({
					delay: 0,
					minLength: 0,
					source: function( request, response ) {
						var matcher = new RegExp( $.ui.autocomplete.escapeRegex(request.term), "i" );
						response( select.children( "option" ).map(function() {
							var text = $( this ).text();
							if ( this.value && ( !request.term || matcher.test(text) ) )
								return {
									label: text.replace(
										new RegExp(
											"(?![^&;]+;)(?!<[^<>]*)(" +
											$.ui.autocomplete.escapeRegex(request.term) +
											")(?![^<>]*>)(?![^&;]+;)", "gi"
										), "<strong style='color:red'>$1</strong>" ),
									value: text,
									option: this
								};
						}) );
					},
					select: function( event, ui ) {
						ui.item.option.selected = true;
						self._trigger( "selected", event, {
							item: ui.item.option
						});
					},
					change: function( event, ui ) {
						if ( !ui.item ) {
							var matcher = new RegExp( "^" + $.ui.autocomplete.escapeRegex( $(this).val() ) + "$", "i" ),
								valid = false;
							select.children( "option" ).each(function() {
								if ( $( this ).text().match( matcher ) ) {
									this.selected = valid = true;
									return false;
								}
							});
							if ( !valid ) {
								// remove invalid value, as it didn't match anything
								$( this ).val( "" );
								select.val( "" );
								input.data( "autocomplete" ).term = "";
								return false;
							}
						}
					}
				})
				.addClass( "ui-widget ui-widget-content ui-corner-left" );
			input.data( "autocomplete" )._renderItem = function( ul, item ) {
				return $( "<li></li>" )
					.data( "item.autocomplete", item )
					.append( "<a>" + item.label + "</a>" )
					.appendTo( ul );
			};
			this.button = $( "<button type='button'>&nbsp;</button>" )
				.attr( "tabIndex", -1 )
				.attr( "title", "Show All Items" )
				.insertAfter( input )
				.button({
					icons: {
						primary: "ui-icon-triangle-1-s"
					},
					text: false
				})
				.removeClass( "ui-corner-all" )
				.addClass( "ui-corner-right ui-button-icon" )
				.click(function() {
					// close if already visible
					if ( input.autocomplete( "widget" ).is( ":visible" ) ) {
						input.autocomplete( "close" );
						return;
					}

					// work around a bug (likely same cause as #5265)
					$( this ).blur();

					// pass empty string as value to search for, displaying all results
					input.autocomplete( "search", "" );
					input.focus();
				});
		},

		destroy: function() {
			this.input.remove();
			this.button.remove();
			this.element.show();
			$.Widget.prototype.destroy.call( this );
		}
	});
})( jQuery );

function debug($obj) {
	if (window.console && window.console.log) window.console.log($obj);
};

function isObject(obj){
    return (typeof obj=='object')&&obj.constructor==Object;
} 

function ucwords(str){
//alert(str);
    return (str).replace(/^([a-z])|\s+([a-z])/g, function ($1) {
        return $1.toUpperCase();
    });
};

function clearBox(selector, value){
//    debug(selector);
    if ($(selector).attr('value') == value)
        $(selector).attr('value', '');
}

function jumpTo(event, selector){
    if (event.keyCode == 13){
        $(selector).focus();
    }
    return true;
}

function triggerButton(event, selector){
    if (event.keyCode == 13){
//		$(selector).focus();
        $(selector).click(); // 模拟一个click
    }
    return true;
}

function ajaxuploadItem(id, name, action, type, data, assignedExt, onComplete){
//debug([id, action, data, assignedExt]);
//return;
    var item = $(id);
    var dialog;
    type = type || false;
    data = data || {};
    assignedExt = assignedExt || '*';
//debug([id, action, data, assignedExt]);
//return;    
    new AjaxUpload(item, {
        // Location of the server-side upload script
        // NOTE: You are not allowed to upload files to another domain
        action: action, //'/jqgrid/jqgrid/oper/importsrs/db/sys_req/table/prj/element/' + element,
        // File upload name
        name: name,
        // Additional data to send
        data: data,
        // Submit file after selection
        autoSubmit: true,
        // The type of data that you're expecting back from the server.
        // HTML (text) and XML are detected automatically.
        // Useful when you are using JSON data as a response, set to "json" in that case.
        // Also set server response type to text/html, otherwise it will not work in IE6
        responseType: type,
        // Fired after the file is selected
        // Useful when autoSubmit is disabled
        // You can return false to cancel upload
        // @param file basename of uploaded file
        // @param extension of that file
        onChange: function(file, extension){
            if (assignedExt != '*'){
                if (extension != assignedExt){
                    alert("Please select an " + assignedExt + " file");
                    return false;
                }
            }
            return true;
        },
        // Fired before the file is uploaded
        // You can return false to cancel upload
        // @param file basename of uploaded file
        // @param extension of that file

        onSubmit: function(file, extension) {
//            button.text('Uploading');
            dialog = $('<div></div>')
            		.html('Uploading the file ' + file)
            		.dialog({
            			autoOpen: false,
            			title: 'Uploading',
            			modal: true
            		});
            
    		dialog.dialog('open');
        },
        // Fired when file upload is completed
        // WARNING! DO NOT USE "FALSE" STRING AS A RESPONSE!
        // @param file basename of uploaded file
        // @param response server response
        onComplete: function(file, response) {
            dialog.dialog('close');
            dialog.remove();
            if (typeof onComplete == 'undefined'){
//debug(response);            
            	noticeDialog(response, "completed", true);
            }
            else
            	onComplete(file, response);
        }
    });   

}

function bindOptions(event){
    $.post(event.url, event.data, function(data){
        event.target.find('option').remove();
        generateOptions(event.target, data, 'id', 'name', event.blankItem);
    }, 'json');
}

function generateOptions(select, data, value, title, blankItem){
    value = value || 'id';
    title = title || 'name';
    blankItem = blankItem || false;
    if (blankItem)
        select.append('<option value=0> </option>');
    $.each(data, function(i, n){
        select.append('<option value=' + n[value] + '>' + n[title] + '</option>');
    });
}

function createFunction(obj,strFunc){
    var args=[];
    if(!obj)obj=window;
    //debugger;
    for(var i=2;i<arguments.length;i++)args.push(arguments[i]);
    return function(){
        obj[strFunc].apply(obj,args);
    }
};

function loadFile(filePath, type){
//debug(filePath);
    var f;
//    var cont;
    type = type || 'js';
    if (type == 'js'){
//        cont = "<script type='text/javascript' src='" + filePath + "'>"; 
        f = $("script[type='text/javascript'][src='" + filePath + "']");
    }        
    else if (type == 'css'){
//        cont = "<script type='text/css' href='" + filePath + "'>";
        f = $("script[type='text/css'][href='" + filePath + "']");
    }
    if (f.length > 0){
//        alert("The file existed");
        return;
    }
	$.getScript(filePath);//, function(){alert("ok to load " + filePath);});
//alert("cont = " + cont);    
//    $('head').append(cont);    
//debug($('head'));    
    return;
}  
   
function newTab(url, div4Tab, title, events){
    if (title == undefined)
        title = "Unknown Title";
    var id = div4Tab + ' #' + title.replace(/ /g, '');
    if($(id).html() != null){
        $(div4Tab).tabs('select', $(id).attr('href'));
    } 
    else{
        $(div4Tab).tabs({ajaxOptions:{type:'GET'}});
        if (events != undefined){
            for(var type in events){
                $(div4Tab).unbind(type).bind(type, events[type]);
//                $(div4Tab).bind(type, events[type]);
            }
        }
        $(div4Tab).tabs('add', url, title);
    }
};

function getTabId(ele, tabSelector){
    tabSelector = tabSelector || '#mainContent > .ui-tabs-panel';
    var tab = $(ele).parents(tabSelector);
    return tab.attr('id');
};

function grid_index(db, table, tabTitle){
    tabTitle = tabTitle || db + ' ' + table;
    var url = '/jqgrid/index/db/' + db + '/table/' + table;
    var tabId = tabTitle.replace(/ /g, '');
    var gridId = '#' + db + '_' + table +'_list';
    var pagerId = '#' + db + '_' + table +'_pager';
//debug(gridId);
//debug(pagerId);
    var events = {
        tabsload:function(event, ui){    
        if (ui.tab.id == tabId){
            var options = {
                config:{
                    url:'jqgrid/jqgrid',
                    data:{oper:'getGridOptions', table:table, db:db},
                    getConfig:true
                },
                gridOptions:{
                    url:'jqgrid/list',
                    postData:{table:table, db:db},
                    editurl:'jqgrid/jqgrid/db/' + db + '/table/' + table
                },
                editOptions:{
                
                }
            };
            return grid_init(gridId, pagerId, options);
        }
        return false;
    }};
	var div4Tab = getDiv4Tab();
    return newTab(url, div4Tab, tabTitle, events);
    
}

function noticeDialog(text, title, okbutton){
    if (typeof okbutton == 'undefined')
        okbutton = true;
    var params = {
    		autoOpen: false,
    		title: title,
    		modal: true,
    		close: function(event, ui){
                $(this).remove();
            },
			buttons: {}
/*
				Ok: function() {
					$( this ).dialog( "close" );
				}
			}
*/				
    };
    if (okbutton){
        params['buttons']['Ok'] = function(){
			$( this ).dialog( "close" );
        }
    }
    var dialog = $('<div></div>')
    	.html(text)
    	.dialog(params);
	dialog.dialog('open');
	return dialog;
};

function getAllInput(div){
	var params = {};
	var inputName;
	var checkboxes = {};
	$.each($("#" + div + " :input"), function(i, n){
		if($(n).attr('type') == 'button')
		   return;
		if ($(n).attr('name') !== undefined)
			inputName = $(n).attr('name');
		else if ($(n).attr('id') !== undefined)
			inputName = $(n).attr('id');
		else{
			alert("NO NAME, NO ID");
			return;
		}
		
		if ($(n).attr('type') == 'checkbox'){
			if ($(n).attr('checked')){
				if (checkboxes[inputName] == undefined)
					checkboxes[inputName] = [];
				checkboxes[inputName].push($(n).val());
			} 
		}
		else
			params[inputName] = $(n).val();
	})
//debug(params);    				    
	for(i in checkboxes){
		params[i] = checkboxes[i].join(',');
	}
	return params;
}

function actionDialog(dialog_params, url, fun_validation, fun_complete){
    var notice_dialog = noticeDialog("Processing, please wait...", "Processing", false);
    var dialog_buttons = {};
    dialog_params['title'] = dialog_params['title'] || 'Dialog';
    dialog_params['div_id'] = dialog_params['div_id'] || 'div_id_tmp';
    dialog_params['ok'] = dialog_params['ok'] || 'Ok';
    dialog_params['cancel'] = dialog_params['cancel'] || 'Cancel';
    dialog_params['width'] = dialog_params['width'] || 500;
    dialog_params['height'] = dialog_params['height'] || 400;
	
	if(dialog_params['ok'] == 'Close')
		dialog_params['cancel'] = '';
    $('<div id="' + dialog_params['div_id'] + '" />').load(url, 
        function(data){
            var defaultOk = function(){
        	    var validated = true;
        	    var params = getAllInput(dialog_params['div_id']);
//debug(params);    				    
        		if (fun_validation !== undefined){
                    validated = fun_validation(params);
                }
                if(validated){
        		    $.post(url, params, function(data){
        				dialog.dialog( "close" );
        				if (fun_complete !== undefined){
        					fun_complete(data);
        				}
                    }, 'json');
                }
        	};

            dialog_buttons[dialog_params['ok']] = defaultOk;
            if (dialog_params['fun_ok'] != undefined){
				if(typeof dialog_params['fun_ok'] == 'string')
					dialog_buttons[dialog_params['ok']] = createFunction(null, dialog_params['fun_ok'], url);
				else
					dialog_buttons[dialog_params['ok']] = dialog_params['fun_ok'];
			}
        
			if (dialog_params['cancel'] != ''){
				dialog_buttons[dialog_params['cancel']] = function(){
					dialog.dialog("close");
				}
            };
            
            var dialog = $(this).html(data).dialog({
    			modal: true,
    			autoOpen: false,
    		    width:dialog_params['width'],
    		    height:dialog_params['height'],
    			title: dialog_params['title'],
    			open:function(event, ui){
                    if (dialog_params['open'] !== undefined)
                        dialog_params['open'](event, ui, url);
                },
                close:function(event, ui){
                    $('#' + dialog_params['div_id']).remove();
                },
    			buttons: dialog_buttons
    		});
    	    dialog.dialog('open');
        }
    );
	notice_dialog.dialog('close');

}

jQuery.extend($.fn.fmatter , {
    viewLink:function(cellval, opts, rwd){
        if (cellval){
            var gridId = opts.gid || $('.ui-jqgrid-bdiv table').attr('id');
            return ret = "<a href=\"javascript:grid_view('" + gridId + "'," + opts.rowId + ", {width:500})\">" + cellval + "</a>";
        }
        return $.fn.fmatter.showlink(cellval, opts);
    },

    infoLink:function(cellval, opts, rwd){
        if (cellval){
			var gridId = opts.gid || $('.ui-jqgrid-bdiv table').attr('id');
            return ret = "<a href=\"javascript:information('" + gridId + "'," + opts.rowId + ")\">" + cellval + "</a>";
        }
        return $.fn.fmatter.showlink(cellval, opts);
    },

    downloadLink:function(cellval, opts, rwd){
        if (cellval){
			var gridId = opts.gid || $('.ui-jqgrid-bdiv table').attr('id');
            return ret = "<a href=\"javascript:download('" + cellval + "')\">" + cellval + "</a>";
        }
        return $.fn.fmatter.showlink(cellval, opts);
    },

    editLink:function(cellval, opts, rwd){
        if (cellval){
            var gridId = opts.gid || $('.ui-jqgrid-bdiv table').attr('id');
            return ret = "<a href=\"javascript:grid_edit('" + gridId + "'," + opts.rowId + ", {width:500})\">" + cellval + "</a>";
        }
        return $.fn.fmatter.showlink(cellval, opts);
    },
    
    eShowLink:function(cellval, opts, rwd, cnt){ // enhanced show link
		var op = {type:'url'};
		var ret = "";
		if(!isUndefined(opts.colModel.formatoptions)) {
            op = $.extend({},op,opts.colModel.formatoptions);
        }
        if (op.type == 'function'){
            var link = "<a href='javascript:" + op.baseLinkUrl + "(";
            if (op.addParam){
                if (typeof op.addParam === 'string'){
                    link += "\"" + op.addParam + "\"";
                }
                else{
                    var params = [];
                    var p_i = 0;
                    $.each(op.addParam, function(i, v){
                        var param = v;
                        if(v.indexOf('%') == 0){
                            var field = v.substring(1);
                            if(rwd[field]){
                                param = rwd[field];
                            }
                        }
                        params[p_i ++] = "\"" + param + "\"";
                    });
                    link += params.join(',');
                }
            }
            link += ")'>"; 
            ret = link + cellval + '</a>';
        }
        else 
            ret = $.fn.fmatter.showlink(cellval, opts);
        return ret;
    },
    select_showlink : function(cellval, opt, rwd) {
	   var ret = $.fn.fmatter.select(cellval,opt, rwd);
       opt.rowId = cellval;	   
	   ret = $.fn.fmatter.showlink(ret, opt);
	   return ret;
    },
    idnamepair:function(cellval, opt, rwd, cnt){
        var ret = [];
        var j = 0;
        if (cellval){
            var cells = cellval.split(",");
            cells = $.map(cells,function(n){return $.trim(n);});
            for(var i = 0; i < cells.length; i ++){
                if (cells[i]){
                    var cv = cells[i].split(":");
                    if (cv[1]){
                        ret[j ++] = cv[1];
                    }
                }
            }
            if (j)
                return ret.join(",");
            return $.fn.fmatter.defaultFormat(cellval,opt);
        }
        else
            return $.fn.fmatter.defaultFormat(cellval,opt);
    },
    idnamepair_select:function(cellval, opt, rwd, cnt){
        var ret = [];
        var j = 0;
        if (cellval){
            var cells = cellval.split(",");
            cells = $.map(cells,function(n){return $.trim(n);});
            for(var i = 0; i < cells.length; i ++){
                if (cells[i]){
                    var cv = cells[i].split(":");
                    if (cv[1]){
                        ret[j ++] = $.fn.fmatter.select(cv[1],opt, rwd);
                    }
                }
            }
            if (j)
                return ret.join(",");
            return $.fn.fmatter.defaultFormat(cellval,opt);
        }
        else
            return $.fn.fmatter.defaultFormat(cellval,opt);
    },
    idnamepair_link:function(cellval, opt, rwd, cnt){
        var ret = '';
        if (cellval){
            var cells = cellval.split(",");
            var links = [];
            var j = 0;
            cells = $.map(cells,function(n){return $.trim(n);});
            for(var i = 0; i < cells.length; i ++){
                if (cells[i]){
                    var cv = cells[i].split(":");
                    if (cv[1]){
                        opt.rowId = cv[0];
                        links[j++] = $.fn.fmatter.showlink(cv[1], opt);
                    }
                }
            }
            if (j)
                return links.join(",");
            return $.fn.fmatter.defaultFormat(cellval,opt);
        }
        return $.fn.fmatter.defaultFormat(cellval,opt);
    }
});
/*
jQuery.extend($.fn.fmatter.viewLink, {
    unformat:function(cellval, options, pos, cnt){
        return $.fn.fmatter.showlink.unformat(cellval, options, pos, cnt);    
    }
});

jQuery.extend($.fn.fmatter.select_showlink , {
    unformat : function(cellval, options, pos,cnt) {
		// Spacial case when we have local data and perform a sort
		// cnt is set to true only in sortDataArray
		var ret = [];
		var cell = cellval;
		if(cnt==true) return cell;
		var op = $.extend({},options.colModel.editoptions);
		if(op.value){
			var oSelect = op.value,
			msl =  op.multiple === true ? true : false,
			scell = [], sv;
			if(msl) { scell = cell.split(","); scell = $.map(scell,function(n){return $.trim(n);})}
			if (isString(oSelect)) {
				var so = oSelect.split(";"), j=0;
				for(var i=0; i<so.length;i++){
					sv = so[i].split(":");
					if(msl) {
						if(jQuery.inArray(sv[1],scell)>-1) {
							ret[j] = sv[0];
							j++;
						}
					} else if($.trim(sv[1])==$.trim(cell)) {
						ret[0] = sv[0];
						break;
					}
				}
			} else if(isObject(oSelect)) {
				if(!msl) scell[0] =  cell;
				ret = jQuery.map(scell, function(n){
					var rv;
					$.each(oSelect, function(i,val){
						if (val == n) {
							rv = i;
							return false;
						}
					});
					if( rv) return rv;
				});
			}
			return ret.join(", ");
		} else {
			return cell || "";
		}
    }
});
*/

function buttonActions(action, gridId, options){
//debug(action);
//debug(gridId);
//debug(options);
    var selectedRows = $(gridId).getGridParam('selarrrow');
    var controller = options['controllerName'];
//    alert("Action " + action);
    switch(action){
        case 'columns':
            $(gridId).setColumns({
                top:100, 
                left:400, 
                width:600, 
                height:"auto", 
                updateAfterCheck: true,
                onClose:function(){
                    // save column hidden/width
                    saveDisplayCookie(gridId, controller, options);
                }
            })
            break;
        case 'tag':
            if (selectedRows.length == 0){
                alert("Please select a record first");
                return;
            }
            _tag(gridId, selectedRows, options.db, options.table);
            break;
        case 'activate':
            if (selectedRows.length < 1){
                alert("Please select rows");
                return false;
            }
            _activate(gridId, selectedRows, options.db, options.table, true);
            break;
        case 'inactivate':
            if (selectedRows.length < 1){
                alert("Please select rows");
                return false;
            }
            _activate(gridId, selectedRows, options.db, options.table, false);
            break;
            
        case 'export':
            if (selectedRows.length < 1){
                alert("Please select rows");
                return false;
            }
            _export(gridId, selectedRows, options.db, options.table);
            break;
           
        default:
            $.post('jqgrid/jqgrid', {db:options.db, table:options.table, oper:action, element:JSON.stringify(selectedRows)}, 
                function(data, status){
                    if (data == 1004){ // not found the action
                        alert("Sorry, this feature " + action + " is not implemented yet");
                    }
                    else
            			$(gridId).trigger('reloadGrid');
        		}
        	);
    }
    return false;
}

function saveDisplayCookie(gridId, controller, params){
//    debug(params);
    // save to database
    var colModel = $(gridId).getGridParam('colModel'), content = {};
debug(colModel);
    var order = 0;
    for(var key in colModel){
        if (colModel[key].name == 'cb' || colModel[key].name == 'subgrid')
            continue;
        content[colModel[key]['name']] = {order:order ++, hidden:colModel[key]['hidden'], width:colModel[key]['width']};
//        order ++;
//debug(colModel[key]['name']);        
    }
    $.ajax({
        type: "post",
        url: '/jqgrid/jqgrid',
        data: {db:params.db, table:params.table, oper:'saveCookie', type:'display', content:JSON.stringify(content)},
        error: function(XMLHttpRequest, textStatus, errorThrown){
            alert(textStatus);
        },
        success: function(options){
            var gw = parseInt($(gridId).getGridParam("width"));
            $(gridId).setGridWidth(gw-0.01,true);
        }
    });
};

function _export(gridId, selectedRows, db, table){
    var dialog = noticeDialog("Generating the report......", "Waiting", false);
    
    $.post('/jqgrid/jqgrid', {db:db, table:table, element:JSON.stringify(selectedRows), oper:'export'}, 
        function(fileName, status){
            dialog.dialog("close");
        
//            $.post('/jqgrid/jqgrid', {db:db, table:table, filename:fileName, oper:'download'} function(){alert("aaa")});
debug(fileName);
            location.href = "download.php?filename=" + encodeURIComponent(fileName) + "&remove=0";
//            location.href = "/jqgrid/jqgrid/db/" + db + "/table/" + table + "/oper/download/filename/" + encodeURIComponent(fileName);
		}
	);
};

function _activate(gridId, selectedRows, db, table, activate){
    var oper = activate ? 'activate' : 'inactivate';
    $.post('/jqgrid/jqgrid', {db:db, table:table, oper:oper, element:JSON.stringify(selectedRows)}, 
        function(data, status){
			$(gridId).trigger('reloadGrid');
		}
	);
};

function _tag(gridId, selectedRows, db, table){
    var dialog = $("<div>Create a tag:<input id='tag' type='text'/><label for='public'>public<input type='checkbox' id='public' checked='checked'/></div>")
        .dialog({
    		autoOpen: false,
    		title: 'Create a tag for ' + table,
    		width:400,
    		modal:true,
    		create:function(event, ui){
                $("div#tag").autocomplete('jqgrid/jqgrid/db/' + db + '/table/' + table + '/oper/getTags', {
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
    		buttons: {
    			Cancel: function() {
    				$(this).dialog('close');
    			},
    			'Tag': function() {
    			    var t = $(this);
                    $.post('jqgrid/jqgrid', {db:db, table:table, oper:'tag', element:JSON.stringify(selectedRows), tag:t.find('#tag').val(), isPublic:t.find('#public').attr('checked')}, 
                        function(data, status){
//debug(data);
                            var colModel = $(gridId).getGridParam('colModel');
                            var lastCol = colModel.pop();
//debug(lastCol);                            
                            if (lastCol.name == '__interTag'){
                                lastCol.searchoptions.value = data;
                                colModel.push(lastCol);
//debug(colModel);                                
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

function _comment(gridId, selectedRows, db, table){
    $('<div id="div_comment" />').load('/jqgrid/jqgrid/oper/comment/db/' + db + '/table/' + table + '/element/' + selectedRows, function(data){
        var dialog = $(this).html(data)
    		.dialog({
    		    width:800,
    		    height:500,
    			autoOpen: false,
    			title: 'Comments',
    			modal: true,
    			buttons: {
    				Submit: function(){
    				    $.post('/jqgrid/jqgrid', {oper:'comment', db:db, table:table, element:selectedRows, comment:$('#comment_new').val()}, function(data){
        					dialog.dialog( "close" );
                        });
                    },
					Cancel: function(){
						dialog.dialog('close');
					}
    			},
                close:function(event, ui){
                    $('#div_comment').remove();
//					$(gridId).trigger('reloadGrid');
                }
    		});
    	dialog.dialog('open');
    });
};

function _clone(gridId, rowId, jsonData){
    jsonData.editOptions.beforeSubmit = function(postData, formId){
        postData['cloneId'] = rowId;
//debug(postData);
        postData['oper'] = 'add';
//debug(postData);
        
        return [true, '', 0];
    };
    $(gridId).editGridRow(rowId, jsonData.editOptions);
};


function grid_edit(gridId, rowId, prop){
/*    if (typeof prop == 'undefined')
        prop = $('#' + gridId).jqGrid('')
*/        
    $('#' + gridId).jqGrid('editGridRow', rowId, prop);
};

function grid_view(gridId, rowId, prop){
//alert("gridId=" + gridId + ", rowId=" + rowId);
    $('#' + gridId).jqGrid('viewGridRow', rowId, prop);
};

function download(file_name){
	location.href = "download.php?filename=" + encodeURIComponent(file_name) + "&remove=0";
}

function information(gridId, rowId){
//	debug(rowId);
	var prop = $('#' + gridId).getGridParam();
//	debug(prop);
	var postData = prop.postData;
	var func_open;
	var func_valid;
	var func_close;
	if(prop.information_open != undefined){
		if (typeof prop.information_open == 'string')
			func_open = eval(prop.information_open);
		else
			func_open = prop.information_open;
	}
	else
		func_open = inform_open;
	if(prop.information_valid != undefined){
		if (typeof prop.information_valid == 'string')
			func_valid = eval(prop.information_valid);
		else
			func_valid = prop.information_valid;
	}
	if(prop.information_close != undefined){
		if (typeof prop.information_close == 'string')
			func_close = eval(prop.information_close);
		else
			func_close = prop.information_close;
	}
	var dialog_params = {gridId:gridId, div_id:'div_information', title:'Detail Information', height:prop.information_height || 540, width:prop.information_width || 1000, open:func_open};
//	var dialog_params = {div_id:'div_information', title:'Detail Information', height:540, width:1000, open:func_open};
//debug(dialog_params);	
	var url = '/jqgrid/jqgrid/db/' + postData['db'] + '/table/' + postData['table'] + '/oper/information/element/' + rowId;
	informDialog(dialog_params, url, func_valid, func_close);
}

function informDialog(params, url, func_valid, func_close){
	params['ok'] = 'Close';
	params['fun_ok'] = params['fun_ok'] || function(){
		$(this).dialog('close');
		$('#' + params.gridId).trigger('reloadGrid');
	};
	actionDialog(params, url, func_valid, func_close);
}
//    source: {app:app, mod:mod}
//    target: {app:app, mod:mod}
function attachTo(gridId, config, source, target, attach){
    var selectedRows = $(gridId).getGridParam('selarrrow');
    if (selectedRows.length == 0){
        selectedRows = $(gridId).getGridParam('selrow');
        if (!selectedRows){
            alert("Please select a record first");
            return;
        }
        else
            selectedRows = [selectedRows];
    }
    _attachTo(gridId, selectedRows, config, source, target, attach);
};

function _attachTo(gridId, rowId, config, source, target, attach){
    var dialog = $('<div id="attach_div"></div>').load(source.app + '/' + source.mod + '/attachto.dlg');
    var dialogTitle;
    if (attach)
        dialogTitle = "Attach " + source.mod + ' to ' + target.mod;
    else
        dialogTitle = "Dettach " + source.mod + ' from ' + target.mod;
    $.post('response.php', {app:target.app, mod:target.mod, action:'getList'}, function(json, status){
        var options = '';
        for(var i in json){
            options += "<option value='" + i + "'>" + json[i] + "</option>";
        }
        dialog.find('select').html(options);
        
    	dialog.dialog({
			autoOpen: false,
			title: dialogTitle,
			width:400,
			modal:true,
			buttons: {
				Cancel: function() {
					$(this).dialog('close');
				},
				'Process': function() {
				    var t = $(this);
                    $.post('response.php', {attach:attach, app:source.app, mod:source.mod, action:'attachTo', target:target.app + '.' + target.mod, 
                        source:JSON.stringify(rowId), targetValue:JSON.stringify(dialog.find('select').val())}, 
                        function(data, status){
        					t.dialog('close');
        					$(gridId).trigger('reloadGrid');
        				}
        			);
				}
			},
			close:function(event, ui){
                dialog.find('#attach_div').remove();
            }
		});
		dialog.dialog('open');
    }, 'json');
};

function defaultContextMenu(action, el, gridId, jsonData){
    var rowId = $(el).attr('id');
	var db = jsonData.db, table = jsonData.table;
    action = action.replace(/.*?#(.*)$/, '$1'); // In IE, the action is different from the definition.
    switch(action){
        case 'edit':
            $(gridId).editGridRow(rowId, jsonData.editOptions);
            break;
        case 'delete':
            $(gridId).delGridRow(rowId);
            break;
        case 'view':
            $(gridId).viewGridRow(rowId, jsonData.viewOptions);
            break;
        case 'clone':
            _clone(gridId, rowId, jsonData);
            break;
        case 'comment':
            _comment(gridId, rowId, jsonData.db, jsonData.table);
            break;
        case 'activate':
            _activate(gridId, [rowId], jsonData.db, jsonData.table, true);
             break;
        case 'inactivate':
            _activate(gridId, [rowId], jsonData.db, jsonData.table, false);
            break;
        case 'addSub':
            jsonData.gridOptions.postData.pid = rowId;
//debug(jsonData.gridOptions);            
            $(gridId).editGridRow("new", jsonData.addOptions);
            break;
			
		case 'information':
			var dialog_params = {div_id:'div_information', title:'Detail Information', height:540, width:800, open:inform_open};
			var url = '/jqgrid/jqgrid/db/' + db + '/table/' + table + '/oper/information/element/' + rowId;
			actionDialog(dialog_params, url);
			break;
			
        default:
            $.post('jqgrid/jqgrid', {db:db, table:table, oper:action, element:rowId}, 
                function(data, status){
                    if (data == 1004){ // not found the action
                        alert(
                    		'Action: ' + action + '\n\n' +
                    		'Element id: ' + rowId + '\n\n' 
                    		);
                    }
                    else{
                        if (data == 0 || data == ''){ // successful
                            noticeDialog("Successed to " + action + " the " + table + "(element " + rowId + ")", "Success", true);
                        }
                        else{ // fail
                            noticeDialog(data, "Fail to " + action + " the " + table + "(element " + rowId + ")", true);                        
                        }
            			$(gridId).trigger('reloadGrid');
            		}
        		}
        	);
    }
    return true;
};

function inform_open(event, ui, url){
	$('#information_tabs input[date="date"]').each(function(i){
		datePick(this);
	});
	$('#information_tabs').tabs('destroy').tabs({selected: 'tabs-current'});
}

function loadContextMenu(gridId, config){
    var myMenuId = gridId.substr(1) + '_myMenu';
//debug(gridId);
//debug(config);    
    if ($("#" + myMenuId).length == 0){ // do not load context menu many times
        var html = '<ul id="' + myMenuId + '" class="contextMenu">';
        for(var href in config.contextMenuItems){
            html += '<li id="' + href + '" class="' + href + '"><a>' + config.contextMenuItems[href] + '</a></li>';
        }
        html += '</ul>';
        //$(gridId + '_tmpForm').append(html);
        $("body").append(html);
    }
    $(gridId + " tr").contextMenu({
        shadow:true,
        menuId:myMenuId,
        onContextMenuItemSelected:function(action, el){
            var retCode = 0;
            if (config.contextMenu != undefined && config.contextMenu != ''){
                retCode = config.contextMenu(action, el);
            }
            if (retCode == 0){ // not handled
                retCode = defaultContextMenu(action, el, gridId, config);
            }
        },
        onContextMenuShow:function(el){
            if (config.onContextMenuShow != undefined){
                var fun = config.onContextMenuShow;
                if (typeof fun == 'string'){
                    fun = eval(fun);
                }
                fun(el);
            }
        }
    });
    return;
}

function handleGridOptions(gridId, options){
//debug(options);
// need add the customer buttons

// need handle the context menus

// need handle some events, such as gridloaded, grid completed, edit form show,etc.
    var controller = options['controllerName'];
    var defaultOptions = {
        gridOptions:{
        	pagerpos:'center',
            datatype:'json',
            ajaxSelectOptions:{type:'POST'},
            ajaxGridOptions:{type:'POST'},
            altRows:true,

//            sortable:{ update: function(permutation) {
//                saveDisplayCookie(gridId, controller, options);
//                alert('save permutation somewhere');
//                }},
                
            mtype: 'POST',
            cellEdit: false,
            cellsubmit:'remote', 
            hiddengrid:false,
            multiselect:true,
            multiboxonly:false,
            rowNum:25, 
            rowList:[10, 25, 50, 100, 200, 'ALL'], 
            shrinkToFit:true,
            forceFit:true,
            sortorder:"asc", 
            loadonce:false,
            autowidth:true,
            width:'1200',
            height:'auto',
            keyIndex:'id',
            viewrecords:true,
            jsonReader:{
                root:'rows',
                page:'page',
                total:'pages',
                records:'records',
                repeatitems:false,
                cell:''
            },
            treeGridModel:'nested'

//            resizeStop:function(newWidth, index){
//                saveDisplayCookie(gridId, controller, options);
//            },
             
        },
        navOptions:{view:false, edit:false, search:true, del:false},
        menuOptions:{view:true, edit:true, del:false, clone:false}, // context
        editOptions:{
            top:100, left:200, width:600, closeOnEscape:true, closeAfterEdit:true,
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
        searchOptions:{
            multipleSearch:true,
            gridModel:false, 
            gridNames:true, 
            formtype:"vertical", 
            enableSearch: true, 
            enableClear:true, 
            autosearch:false
//            filterModel:array(),
        },
        
        viewOptions:{
            top:100, left:500, width:600, closeOnEscape:true
        },
        buttons:{},
        contextMenu:''
    };
//debug(options);    
    var config = $.extend(true, defaultOptions, options);
    if (config.construct != undefined){
        try{
            var initFunction = eval(config.construct); //createFunction(null, config.construct, gridId, config);//eval(config.construct);
            config = options = initFunction(gridId, config);
        }catch(e){
            alert(e.message);
        }
    }
//debug(config);
    $.each(config.gridOptions.colModel, function(i, v){
        if (v.label == undefined)
            v.label = ucwords(v.name);
        if (v.index == undefined)
            v.index = v.name;
		if (v.editoptions != undefined && v.editoptions.dataInit != undefined){
			if (typeof v.editoptions.dataInit == 'string')
				v.editoptions.dataInit = eval(v.editoptions.dataInit);
		}
		if (v.addoptions != undefined && v.addoptions.dataInit != undefined){
			if (typeof v.addoptions.dataInit == 'string')
				v.addoptions.dataInit = eval(v.addoptions.dataInit);
		}
		if (v.searchoptions!= undefined && v.searchoptions.dataInit != undefined){
			if (typeof v.searchoptions.dataInit == 'string')
				v.searchoptions.dataInit = eval(v.searchoptions.dataInit);
		}
		
    });

    config.gridOptions.gridComplete = function(){
        if (options['gridComplete']){
            var func = options['gridComplete'];
            func(gridId);
        }
    };        
    config.gridOptions.loadComplete = function(data){
        // bind the contextMenu
        if (typeof config.contextMenu == 'string' && config.contextMenu != '')
            config.contextMenu = eval(config.contextMenu);
        loadContextMenu(gridId, config);
    };
    var beforeShowForm = config.editOptions.beforeShowForm;
    if (beforeShowForm != undefined){
        if (typeof beforeShowForm == 'string')
            beforeShowForm = eval(beforeShowForm);
    };
    config.editOptions.beforeShowForm = function(formId){
        if (beforeShowForm != undefined)
            beforeShowForm(formId);
        formId.find(':text').each(function(i){
            if ($(this).val() == 'null')
                $(this).val('');
        });
    }
    
    var afterSubmit = config.editOptions.afterSubmit;
    if (afterSubmit != undefined){
        if (typeof afterSubmit == 'string')
            afterSubmit = eval(afterSubmit);
    };
    config.editOptions.reloadAfterSubmit = false;
    config.editOptions.afterSubmit = function(response, postData){
        if (afterSubmit != undefined)
            afterSubmit(response, postData);
        var ret = $.parseJSON(response.responseText);
        if (ret.code == 1){
//                    alert(ret.msg);
            return [false, ret.msg, 1];
        }
        return [true, 'Success to save the record', ret.msg];            
    };

    if (!config.gridOptions.multiselect)
        delete(config.gridOptions.buttons['tag']);

    var resizeStop = config.gridOptions.resizeStop;
    if (resizeStop == undefined){
        config.gridOptions.resizeStop = function(newWidth, index){
            saveDisplayCookie(gridId, controller, config);
        }; 
    }
    var sortable = config.gridOptions.sortable;
    if (typeof sortable == 'undefined'){
        config.gridOptions.sortable = { 
            update: function(permutation) {
                saveDisplayCookie(gridId, controller, config);
//                alert('save permutation somewhere');
            }
        };
    }
    if (typeof config.gridOptions.treeGrid != 'undefined' && config.gridOptions.treeGrid)
        config.gridOptions.rowNum = -1;
	if (config.gridOptions.inlineEdit == undefined || (config.gridOptions.inlineEdit == 'true' || config.gridOptions.inlineEdit == true)){
		config.gridOptions.ondblClickRow = function(rowid, iRow, iCol, e){
	//debug([rowid, iRow, iCol, e]);
			if(rowid && rowid!==config.lastSel){ 
				jQuery(gridId).restoreRow(config.lastSel); 
				config.lastSel=rowid; 
			}
			jQuery(gridId).editRow(rowid, true); 
		}
		config.lastSel = 0;
	}
//debug(config);
    return config;
}

var datePick = function(elem){
   jQuery(elem).datepicker('destroy').datepicker({dateFormat:'yy-mm-dd'});
};

function setupGrid(gridId, pagerId, options){
    var allOptions = handleGridOptions(gridId, options);
    allOptions.gridOptions.pager = pagerId;
//debug("in setup grid, allOptions");
//debug(allOptions.gridOptions);
//debug(allOptions);
//debug(gridId);
    $(gridId).jqGrid(allOptions.gridOptions);
//alert("wait");
    
    var navGrid = $(gridId).jqGrid('navGrid', pagerId, allOptions.navOptions, allOptions.editOptions, allOptions.addOptions,
        allOptions.delOptions, allOptions.searchOptions, allOptions.viewOptions);
    for(var key in allOptions.buttons){
//debug(allOptions.buttons[key]);    
        if (allOptions.buttons[key]['onClickButton'] == undefined){
            allOptions.buttons[key]['onClickButton'] = 'buttonActions';
        }
        if (typeof allOptions.buttons[key]['onClickButton'] == 'string'){

            try{
	            allOptions.buttons[key]['onClickButton'] = createFunction(null, allOptions.buttons[key]['onClickButton'], key, gridId, allOptions);
            }catch(e){
                alert('Fail to create function for button click');
            }
        }
        navGrid.navButtonAdd(pagerId, allOptions.buttons[key]);
    }
//debug(config.gridOptions);	 

//    $(gridId).jqGrid('sortableRows'); 
    $(gridId).jqGrid('filterToolbar');
}

function grid_init(gridId, pagerId, options){
    var db = options.config.data.db || '';
    var table = options.config.data.table || '';
    if (db != '' && table != ''){
        var js = '/js/' + db + '/' + table + '.js';
//        var js = '/../application/jqgrid/' + db + '/' + table + '/' + table + '.js';
        loadFile(js);
    }
    if (options.config.getConfig){
        $.ajax({
            url:options.config.url,
            type:'POST',
            data:options.config.data,
            dataType:'json',
            success:function(retData, textStatus){ // get the colModels
                options = $.extend(true, retData, options);
                setupGrid(gridId, pagerId, options);
            },
            error:function(XMLHttpRequest, textStatus, errorThrown){
                alert("error:" + textStatus);
            }
        });
    }
    else{
        setupGrid(gridId, pagerId, options);
    }
}

function expandSubGridRow(expandField, subgrid_id, row_id, db, table, parent_db, parent_table){
    var subgrid_table_id, pager_id; 
//    canExpand = canExpand || false;
    subgrid_table_id = subgrid_id+"_t"; 
    pager_id = "p_"+subgrid_table_id; 
    $("#"+subgrid_id).html("<table id='"+subgrid_table_id+"' class='scroll'></table><div id='"+pager_id+"' class='scroll'></div><div id='" + subgrid_table_id + "_tmpForm'></div>"); 
//debug(expandField);
//debug(subgrid_id);
//debug(row_id);
    var options = {
        config:{
            url:'jqgrid/jqgrid',
            data:{oper:'getGridOptions', table:table, db:db},
            getConfig:true
        },
        gridOptions:{
            url:'jqgrid/list',
            postData:{table:table, db:db, filters:'{"groupOp":"AND","rules":[{"field":"' + expandField + '","op":"eq","data":' + row_id + '}]}'},
            editurl:'jqgrid/jqgrid/db/' + db + '/table/' + table + '/parentid/' + row_id + '/parentdb/' + parent_db + '/parenttable/' + parent_table 
        },
        editOptions:{
        
        }
    };
//debug(options);    
    return grid_init('#' + subgrid_table_id, '#' + pager_id, options);
}            

function view_edit(db, table, id, dialog_params){
	var url = 'jqgrid/jqgrid/db/' + db + '/table/' + table + '/oper/viewEdit/element/' + id;
	dialog_params = dialog_params || {};
	dialog_params['title'] = dialog_params['title'] || 'Edit ' + table + ' Information';
	dialog_params['div_id'] = dialog_params['div_id'] || 'div_' + table + '_information';
//	dialog_params['fun_ok'] = function(){$('#' + dialog_params['div_id']).dialog('close');};
	var func_valid = dialog_params['fun_valid'] || undefined, func_comp = dialog_params['fun_comp'] || undefined;
	
	actionDialog(dialog_params, url, func_valid, func_comp);
}

function view_edit_edit(event){
	var dialog = $(event).parent().parent();
	dialog.find('#div_view_edit [editable="editable"]').each(function(i){$(this).attr('disabled', false);});
	dialog.find('#div_button_edit #btn_edit').hide();
	dialog.find('#div_button_edit #btn_clone,#btn_save,#btn_cancel').show();
	dialog.find('#div_hidden #saved').val('false');
}

function view_edit_save(event){
	var dialog = $(event).parent().parent();
	var dialog_id = dialog.attr('id');
	dialog.find('#div_view_edit [editable="editable"]').each(function(i){$(this).attr('original_value', $(this).val()).attr('disabled', true);});
	dialog.find('#div_button_edit #btn_save,#btn_clone,#btn_cancel').hide();
	dialog.find('#div_button_edit #btn_edit').show();
	dialog.find('#div_hidden #saved').val('true');
	var inputs = getAllInput(dialog_id + ' #div_view_edit');
	var db = dialog.find('#div_hidden #db').val(), table = dialog.find('#div_hidden #table').val(), id=$('#div_hidden #id').val();
	$.post('/jqgrid/jqgrid/db/' + db + '/table/' + table + '/oper/edit', inputs, function(data, textStatus){
	});
}

function view_edit_clone(event){
	var dialog = $(event).parent().parent();
	var dialog_id = dialog.attr('id');
	dialog.find('#div_view_edit [editable="editable"]').each(function(i){$(this).attr('original_value', $(this).val()).attr('disabled', true);});
	dialog.find('#div_button_edit #btn_save,#btn_clone,#btn_cancel').hide();
	dialog.find('#div_button_edit #btn_edit').show();
	dialog.find('#div_hidden #saved').val('true');
	var inputs = getAllInput(dialog_id + ' #div_view_edit');
	var db = dialog.find('#div_hidden #db').val(), table = dialog.find('#div_hidden #table').val(), id=$('#div_hidden #id').val();
	$.post('/jqgrid/jqgrid/db/' + db + '/table/' + table + '/oper/clone', inputs, function(data, textStatus){
	});
}

function view_edit_cancel(event){
	var dialog = $(event).parent().parent();
	dialog.find('#div_view_edit [editable="editable"]').each(function(i){$(this).val($(this).attr('original_value')).attr('disabled', true);});
	dialog.find('#div_button_edit #btn_cancel,#btn_save,#btn_clone').hide();
	dialog.find('#div_button_edit #btn_edit').show();
}

function displayRow(cells, rowProp){
	rowProp = rowProp || {};
	var strRowProp = [];
	for(i in rowProp){
		if (i == 'classes'){
			if (rowProp['classes'].length > 0)
				strRowProp.push(rowProp['classes'].join(' '));
		}
		else
			strRowProp.push(i + '=' + rowProp[i]);
	}
	var html = '';
	for(i in cells)
		html += displayCell(cells[i]);
	if(html.length > 0)
		html = '<tr ' + strRowProp.join(' ') + '>' + html  + '</tr>';
	return html;
}

function displayCell(cell){
    var html = '';
	for(i in cell){
		switch(i){
			case 'label':
				break;
			case 'classes':
				if (cell['classes'].length > 0)
					html = html + ' class="' + cell['classes'].join(' ') + '"';
				break;
			default:
				html = html + i + '="' + cell[i] + '" ';
		}
	}
	
    html = '<td ' + html + ">" + cell['label'] + "</td>";
    return html;
}

function select_cells_change(data){
	var option_id = $(this).children('option:selected').val();
	var option_text = $(this).children('option:selected').text().toLowerCase();
	if (option_id == '' || option_id == -1)
		return;
	var map = data['data'];
	if (map[option_id]['op'] == undefined || map[option_id]['op'] == 'add')
		$('#' + map['table'] + ' td' + map[option_id]['selector']).addClass('selected_cell');
	else
		$('#' + map['table'] + ' td' + map[option_id]['selector']).removeClass('selected_cell');
	$(this).children('#No_op').attr('selected', 'selected');
	updateOpertionWithSelectedStatus(map);
}

function operation_with_selected_change(data){
	var op_id = $(this).children('option:selected').val();
	var op_text = $(this).children('option:selected').text();
	if (op_id == '' || op_id == -1)
		return;
	var map = data['data'];
	if (map['selector'] == undefined)
		map['selector'] = ' td.selected_cell';
	$('#' + map['table'] + ' ' + map['selector']).each(function(){
		if (map[op_id]['addClass'] != undefined)
			$(this).addClass(map[op_id]['addClass']);
		if (map[op_id]['removeClass'] != undefined)
			$(this).removeClass(map[op_id]['removeClass']);
		if (map[op_id]['removeAttr'] != undefined)
			$(this).removeAttr(map[op_id]['removeAttr']);
		if (map[op_id]['addAttr'] != undefined){
			for(i in map[op_id]['addAttr']){
				if (map[op_id]['addAttr'][i]['name'] == 'label')
					$(this).html(map[op_id]['addAttr'][i]['value']);
				else
					$(this).attr(map[op_id]['addAttr'][i]['name'], map[op_id]['addAttr'][i]['value']);
			}
		}
		if(map[op_id]['label'] != undefined)
			$(this).html(map[op_id]['label']);
	});
	$('#' + map['table'] + ' td.selected_cell').removeClass('selected_cell');
	$(this).children('#No_op').attr('selected', 'selected');
	$(this).attr('disabled', 'disabled');

	updateSubmitStatus(map);
}

function updateOpertionWithSelectedStatus(data){
	$('#' + data['op']).attr('disabled', $('#' + data['table'] + ' td.selected_cell').length == 0);
}

function updateSubmitStatus(data){
	$('#' + data['submit']['id']).attr('disabled', $('#' + data['table'] + '  ' + data['submit']['selector']).length == 0);
}

function listReports(gridId, pagerId, db, table, element_id){
	var options = {
		config:{
			url:'jqgrid/jqgrid',
			data:{oper:'getGridOptions', table:'report', db:db, element:element_id},
			getConfig:true
		},
		gridOptions:{
			url:'jqgrid/list',
            postData:{table:'report', db:db, filters:'{"groupOp":"AND","rules":[{"field":"table_name","op":"eq","data":"' + table + '"}, {"field":"element_id","op":"eq","data":"' + element_id + '"}]}'},
			editurl:'jqgrid/jqgrid/db/' + db + '/table/report',
		},
		navOptions:{add:false, view:false, edit:false, search:false, del:true},
	};
//	var op = handleGridOptions(gridId, options);
	return grid_init(gridId, pagerId, options);
}