//自定义jqgrid的formatter

jQuery.extend($.fn.fmatter , {

	tool : new kf_tool(),

    viewLink:function(cellval, opts, rwd){
        if (cellval){
            var gridId = opts.gid || $('.ui-jqgrid-bdiv table').attr('id');
            return "<a href=\"javascript:XT.grid_view('" + gridId + "'," + opts.rowId + ", {width:500})\">" + cellval + "</a>";
        }
        return $.fn.fmatter.showlink(cellval, opts);
    },

    infoLink:function(cellval, opts, rwd){
        if (cellval){
			var gridId = opts.gid || $('.ui-jqgrid-bdiv table').attr('id');
			var postData = $('#' + gridId).getGridParam('postData');
//debug(postData);			
			var parent_id = postData['parent'] || 0;
//debug(parent_id);			
            return "<a href=\"/jqgrid/jqgrid/newpage/1/oper/information/db/" + postData['db'] + "/table/" + postData['table'] + "/element/" + opts.rowId + "/parent/" + parent_id + "\" target=\"_blank\">" + cellval + "</a>";
        }
        return $.fn.fmatter.showlink(cellval, opts);
    },

    infoLink_ver:function(cellval, opts, rwd){
        if (cellval){
			var gridId = opts.gid || $('.ui-jqgrid-bdiv table').attr('id');
			var postData = $('#' + gridId).getGridParam('postData');
			var container = $('#' + gridId).getGridParam('container');
			var parent_id = postData['parent'] || 0;
//debug(parent_id);			
            return "<a href=\"/jqgrid/jqgrid/newpage/1/container/" + container + "/oper/information/db/" + postData['db'] + "/table/" + postData['table'] + "/element/" + opts.rowId + "/parent/" + parent_id + "/ver/" + rwd['ver_ids'] + "\" target=\"_blank\">" + cellval + "</a>";
        }
        return $.fn.fmatter.showlink(cellval, opts);
    },

    infoLink_dialog:function(cellval, opts, rwd){
        if (cellval){
			var gridId = opts.gid || $('.ui-jqgrid-bdiv table').attr('id');
			var postData = $('#' + gridId).getGridParam('postData');
			var container = $('#' + gridId).getGridParam('container');
			var db = postData.db, table = postData.table;
//debug(postData);			
			var parent_id = postData['parent'] || 0;
//debug(parent_id);			
            var ret = "<a href=\"javascript:XT.information('" + db + "','" + table + "', " + opts.rowId + ", '" + container + "')\">" + cellval + "</a>";
			return ret;
        }
        return $.fn.fmatter.showlink(cellval, opts);
    },
	
	resultLink:function(cellval, opts, rwd){
		var gridId = opts.gid || $('.ui-jqgrid-bdiv table').attr('id');	
		var postData = $('#' + gridId).getGridParam('postData');
		var container = $('#' + gridId).getGridParam('container');
		var db = postData.db, table = postData.table, parent = postData.parent;
		parent = parent || 0;
		var results = tool.str2Array(opts.colModel.editoptions.value);
// tool.debug(opts.colModel.editoptions.value);
// tool.debug(results);
		var div_id = "ce_result_type_" + opts.rowId;
		var options = '';
		var result = '';
		var total;
		$.each(results, function(i, val) {
			if(cellval == i)
				result = val;
			if((cellval == (parseFloat(100) + parseFloat(i)) ) && (i != 1)){
				total = 'Has ' + val;
			}
			if(i == 0 && val == ' ')
				options += '<option selected="selected" value="'+ i + '">' + val + '</option>';
			else
				options += '<option value="'+ i + '">' + val + '</option>';
		});
		if(rwd.isTester == false)
			return result;
		if(typeof total != 'undefined'){
			if(cellval == 100)
				total = 'Has Blank';
			return total;
		}
		if(cellval == 112)
			return "Fail";
		if(cellval > 0){
			//将函数改掉
			return "<a href='javascript:XT.resultInfo(" + '"' + db + '", "' + table + '", "' + container + '", "' + opts.rowId + '", "#' + gridId + '", "#' + div_id + '", "' + cellval + '", "' + parent + '")'+ "'>" + result + '</a>';
		}
		//onchange,id,name的修改
		var func = "XT.inputResult('" + db + "', '" + table + "', '" + container + "', '" + opts.rowId + "', '#" + gridId + "', '#" + div_id + "', '" + parent + "')";
		return '<select id="' + div_id + '" style="width: 100%;" name="result_type" onchange="' + func + '">' + options +'</select>';//id和name需要修改
	},
	
	bResultLink:function(cellval, opts, rwd){
		var gridId = opts.gid || $('.ui-jqgrid-bdiv table').attr('id');	
		var postData = $('#' + gridId).getGridParam('postData');
		var container = $('#' + gridId).getGridParam('container');
		var db = postData.db, table = postData.table, parent = postData.parent;
		parent = parent || 0;
		var results = tool.str2Array(opts.colModel.searchoptions.value);
		var result = '';
		$.each(results, function(i, val) {
			if(cellval == i)
				result = val;
		});
		if(rwd.isTester == false)
			return result;
		return "<a href='javascript:XT.buildResult(" + '"' + db + '", "' + table + '", "' + container + '", "' + opts.rowId + '", "#' + gridId + '", "' + cellval + '", "' + parent + '")' + "'>" + result + '</a>';
    },
	
	testorLink:function(cellval, opts, rwd){
		var gridId = opts.gid || $('.ui-jqgrid-bdiv table').attr('id');
		var postData = $('#' + gridId).getGridParam('postData');
		var container = $('#' + gridId).getGridParam('container');
		var db = postData.db, table = postData.table, parent = postData.parent;
		parent = parent || 0;
		var testor = tool.str2Array(opts.colModel.searchoptions.value);
		var div_id = "ce_testor_id_" + opts.rowId;
		var options = '';
		var result = '';
		$.each(testor, function(i, val) {
			if(cellval == i)
				result = val;
			if(i == 0 && val == ' ')
				options += '<option selected="selected" value="'+ i + '">' + val + '</option>';
			else
				options += '<option value="'+ i + '">' + val + '</option>';
		});
		if(rwd.isTester == false)
			return result;
		if(cellval > 0){
			return result;//再想想
		}
		var func = "XT.setOneTester('" + db + "', '" + table + "', '" + container + "', '" + opts.rowId + "', '#" + gridId + "', '#" + div_id + "', '" + parent + "')";
	    return '<select id="' + div_id + '" style="width: 100%;" name="testor_id" onchange="' + func + '">' + options +'</select>';//id和name需要修改
    },

	updateViewEditPage:function(cellval, opts, rwd){
		if (cellval){
			var gridId = opts.gid || $('.ui-jqgrid-bdiv table').attr('id');
			var postData = $('#' + gridId).getGridParam('postData');
			var container = $('#' + gridId).getGridParam('container');
			var parent_id = postData['parent'] || 0, db = postData['db'], table = postData['table'];
//debug(parent_id);			
            return "<a href=\"javascript:XT.updateViewEditPage('" + db + "','" + table + "', '" + container + "', " + opts.rowId + ", " + parent_id + ")\">Version " + cellval + "</a>";
		}
		else
			return '';
	},
		
	step_number:function(cellval, opts, rwd){
		if (cellval){
			return "Step < " + cellval + " >";
		}
		else
			return '';
	},
		
	ids:function(cellval, opts, rwd){
		if (cellval){
			var ret = [];
			var cells = cellval.split(',');
			var reg = />\d+</;
			$.each(cells, function(i, id){
				// 需要解决关键字高亮的问题，id的形式可能为纯粹的id, 也可能是<span....>id</span>
				// if (id[0] == '<'){
					// var m = id.match(reg);
					// ret.push("<span style='color:#FF0000;background-color:#CCCCCC'>" + $.fn.fmatter.select_showlink(m[0].substr(1), opts, rwd) + "</span>");
				// }
				// else
					ret.push($.fn.fmatter.select_showlink(id,opts, rwd));
			});
			return ret.join('<br />');
		}
		return $.fn.fmatter.defaultFormat(cellval,opts);
	},
		
    downloadLink:function(cellval, opts, rwd){
        if (cellval){
			var gridId = opts.gid || $('.ui-jqgrid-bdiv table').attr('id');
            return "<a href=\"javascript:download('" + cellval + "')\">" + cellval + "</a>";
        }
        return $.fn.fmatter.showlink(cellval, opts);
    },

    editLink:function(cellval, opts, rwd){
        if (cellval){
            var gridId = opts.gid || $('.ui-jqgrid-bdiv table').attr('id');
            return "<a href=\"javascript:XT.grid_edit('" + gridId + "'," + opts.rowId + ", {width:500})\">" + cellval + "</a>";
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
		if (cellval == undefined || cellval == '')
			return ' ';
		if (cellval == 0)
			return ' ';

		var foptions = opt.colModel.formatoptions, db = foptions.db, table = foptions.table, ret = [];
		var label = $.fn.fmatter.select(cellval,opt, rwd);
		if (foptions.newpage == true){
			var gridId = opt.gid || $('.ui-jqgrid-bdiv table').attr('id');		
			var container = $('#' + gridId).getGridParam('container');
			ret.push("<a href=\"/jqgrid/jqgrid/newpage/1/container/");
			ret.push(container);
			ret.push("/oper/information/db/");
			ret.push(db);
			ret.push("/table/");
			ret.push(table);
			ret.push("/element/");
			ret.push(cellval);
			
			ret = "<a href=\"/jqgrid/jqgrid/newpage/1/container/" + container + "/oper/information/db/" + db + "/table/" + table + "/element/" + cellval;
			if (foptions.addParams != undefined){
				$.each(foptions.addParams, function(i,n){
					ret.push("/" + i + "/" + rwd[n]);
				})
			}
			ret.push("\" target=\"_blank\">" + label + "</a>");
			return ret.join('');
        }
		ret.push("<a href=\"javascript:XT.information('");
		ret.push(db);
		ret.push("','");
		ret.push(table);
		ret.push("', ");
		ret.push(cellval);
		ret.push(")\">");
		ret.push(label);
		ret.push("</a>");
		return ret.join('');
    },
    text_link : function(cellval, opt, rwd) {
		if (cellval == undefined || cellval == '')
			return ' ';
		if (cellval == 0)
			return ' ';
		var foptions = opt.colModel.formatoptions, db = foptions.db, table = foptions.table, data_field=foptions.data_field, ret;
		if (foptions.newpage == true){
			var gridId = opt.gid || $('.ui-jqgrid-bdiv table').attr('id');		
			var container = $('#' + gridId).getGridParam('container');
			// var postData = $('#' + gridId).getGridParam('postData');
			ret = "<a href=\"/jqgrid/jqgrid/newpage/1/container/" + container + "/oper/information/db/" + db + "/table/" + table + "/element/" + rwd[data_field] + "/parent/" + rwd.id;
			if (foptions.addParams != undefined){
				$.each(foptions.addParams, function(i,n){
					ret += "/" + i + "/" + rwd[n];
				})
			}
			ret += "\" target=\"_blank\">" + cellval + "</a>";
			return ret;
        }
		var ret = $.fn.fmatter.select(cellval,opt, rwd);
		return ret = "<a href=\"javascript:XT.information('" + db + "','" + table + "', " + rwd[data_field] + ")\">" + cellval + "</a>";
    },
	log_link : function(cellval, opts, rwd) {
		if (cellval == undefined || cellval == '')
			return ' ';
		if (cellval == 0)
			return ' ';
		var gridId = opts.gid || $('.ui-jqgrid-bdiv table').attr('id');		
		var postData = $('#' + gridId).getGridParam('postData');
		var container = $('#' + gridId).getGridParam('container');
		var db = postData.db, table = postData.table, parent = postData.parent;
		var cvs = cellval.split(";");
		var ret = '';
		$.each(cvs, function(i, val){
			var fileInfo = val.split(" ");
			if(ret == '')
				ret += "<a title='download file' href=\"javascript:XT.log_download('" + db + "','" + table + "', '" + container + "', " + rwd.id + ", '" + fileInfo[0] + "')\">" + val + "bytes</a>";
			else
				ret += "<br /><a title='download file' href=\"javascript:XT.log_download('" + db + "','" + table + "', '" + container + "', " + rwd.id + ", '" + fileInfo[0] + "')\">" + val + "bytes</a>";
			ret += "<a> </a><a  title='delete file' href=\"javascript:XT.log_delete('" + db + "','" + table + "', '" + container + "', '#" + gridId + "', " + rwd.id + ", '" + val + "')\"><img src='/css/images/delete.png'></a>";
		});
		return ret;
	},
	
	jira_link : function(cellval, opts, rwd) {
		if (cellval == undefined || cellval == '')
			return ' ';
		if (cellval == 0)
			return ' ';
		var gridId = opts.gid || $('.ui-jqgrid-bdiv table').attr('id');		
		var postData = $('#' + gridId).getGridParam('postData');
		var container = $('#' + gridId).getGridParam('container');
		var db = postData.db, table = postData.table, parent = postData.parent;
		var cvs = cellval.split(",");
		var ret = '';
		$.each(cvs, function(i, val){
			if(ret == '')
				ret += '<a title="jira bug id" href="http://sw-jira.freescale.net/browse/' + val + '"  target="_blank">' + val +'</a>';
			else
				ret += '<br /><a title="jira bug id" href="http://sw-jira.freescale.net/browse/' + val + '"  target="_blank">' + val +'</a>';
		});
		return ret;
	},
	
	dp_log_link : function(cellval, opts, rwd) {
		if (cellval == undefined || cellval == '')
			return ' ';
		if (cellval == 0)
			return ' ';
		var ret = '<a title="dp log" href="http://dapeng/dapeng/showMcuautoRequestDetail/' + cellval + '/None/None/None/" target="_blank">dp log</a>';
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
    },
	// multi_row_edit:function(cellval, opt, rwd, cnt){
		// var ret = [];
// // tool.debug("Start>>>>");
// // tool.debug(cellval);
// // tool.debug(opt);
		// for(var i in cellval){
			// var row = [];
// // tool.debug(cellval[i]);
			// opt.colModel.formatoptions.subformat = opt.colModel.formatoptions.subformat || 'normal';
// // tool.debug(opt.colModel.formatoptions.subformat);	
// // tool.debug(opt.colModel.formatoptions);	
// // tool.debug(opt.colModel.temp);	
			// switch(opt.colModel.formatoptions['subformat']){
				// case 'nv':
					// var fs = ['name_field', 'value_field'], cont = [];//["(" + i + ")"];
					// for(var t in fs){
						// var f = opt.colModel.formatoptions[fs[t]], field = opt.colModel.temp[f], formatter = field.formatter;
						// var value = cellval[i][f];
// // tool.debug(">>>");						
// // tool.debug([f, field, formatter]);
// // tool.debug(value);
// // tool.debug("<<<");						
						// if($.fn.fmatter[formatter])
							// cont.push($.fn.fmatter[formatter](value, {colModel:field}));
						// else
							// cont.push(value);
					// }
					// row.push('<li>' + cont.join(':') + '</li>');
					// break;
				// case 'normal':
					// var cont = [];
					// for(var j in opt.colModel.temp){
						// var field = opt.colModel.temp[j];
						// var formatter = field.formatter;
						// var value = cellval[i][j];
						// var field_opt = {colModel:field};
						// var label = field.label;
		// // tool.debug([label, formatter, value]);
						// if(value != undefined){
							// if($.fn.fmatter[formatter]) 
								// value = $.fn.fmatter[formatter](value, {colModel:field});
							// cont.push('[<span class="multi_row_label">' + label + '</span>]:<span class="multi_row_value">' + value + '</span>');
						// }
					// }
					// // row.push(cont.join(' '));
					// // row.push("(" + i + ")" + cont.join(', '));
					// row.push("<li>" + cont.join(', ') + "</li>");
					// break;
				// default:
					// row.push(cellval[i]);
			// }
			// ret.push("<ul>" + row.join(' ') + "</ul>");
			// // ret.push(row.join(', '));
		// }
// // tool.debug(ret);
// // tool.debug("end<<<<<<");		
		// return ret.join('<BR />');
	// },
	multi_row_edit:function(cellval, opt, rwd, cnt){
		var ret = ['<ul class="multi_row_ul">'], rows = [], temp = opt.colModel.formatoptions.temp || null, labels = {};
		opt.colModel.formatoptions.subformat = opt.colModel.formatoptions.subformat || 'normal';
		for(var i in cellval){
			rows[i] = {};
			for(var j in cellval[i]){
				var value = cellval[i][j];
				if(opt.colModel.temp[j]){
					var field = opt.colModel.temp[j];
					var formatter = field.formatter;
					labels[j] = field.label;
					if(value != undefined && $.fn.fmatter[formatter])
						value = $.fn.fmatter[formatter](value, {colModel:field});
				}
				rows[i][j] = value;
			}
			// for(var j in opt.colModel.temp){
				// var field = opt.colModel.temp[j];
				// var formatter = field.formatter;
				// var value = cellval[i][j];
				// labels[j] = field.label;
				// if(value != undefined && $.fn.fmatter[formatter])
					// value = $.fn.fmatter[formatter](value, {colModel:field});
				// rows[i][j] = value;
			// }
		}
// tool.debug(rows);		
		var showRow = function(row, labels){
			var cont = '';
			switch(opt.colModel.formatoptions.subformat){
				case 'temp':
					var temp = opt.colModel.formatoptions.temp;
					cont = sprintf(temp, row);
					break;
				case 'nv':
					var v_name = row[opt.colModel.formatoptions['name_field']], v = row[opt.colModel.formatoptions['value_field']];
					cont = v_name + ':' + v;
					break;
				case 'normal':
				default:
					var c = [];
					for(i in row){
						c.push('[<span class="multi_row_label">' + labels[i] + '</span>]:<span class="multi_row_value">' + row[i] + '</span>');
					}
					cont = c.join(', ');
					break;
			}
			return '<li>' + cont + '</li>';
		};
		
		for(var i in rows){
			ret.push(showRow(rows[i], labels));
		}
		ret.push('</ul>');
		return ret.join('');
	},	
	embed_table:function(cellval, opt, rwd, cnt){
		var ret = [];
// tool.debug("embed_table, Start, cellval = >>>>");
// tool.debug(cellval);
// tool.debug("<<<<<<<<");
// tool.debug(opt);
		var row = [];
// tool.debug(cellval[i]);
		opt.colModel.formatoptions.subformat = opt.colModel.formatoptions.subformat || 'normal';
// tool.debug(opt.colModel.formatoptions.subformat);	
// tool.debug(opt.colModel.formatoptions);	
// tool.debug(opt.colModel.temp);	
		switch(opt.colModel.formatoptions['subformat']){
			case 'nv':
				var fs = ['name_field', 'value_field'], cont = [];
				for(var t in fs){
					var f = opt.colModel.formatoptions[fs[t]], field = opt.colModel.temp[f], formatter = field.formatter;
					var value = cellval[i][f];
					if($.fn.fmatter[formatter])
						value = $.fn.fmatter[formatter](value, {colModel:field});
// tool.debug([f, field, formatter]);
// tool.debug(value);						
					cont.push(value);
				}
				row.push(cont.join(':'));
				break;
			case 'normal':
// tool.debug(cellval);				
				for(var j in opt.colModel.temp){
					var field = opt.colModel.temp[j];
					var formatter = field.formatter;
					var value = cellval[j];
					var field_opt = {colModel:field};
					var label = field.label;
	// tool.debug([label, formatter, value]);
					if(value != undefined){
						var cont = value;
						if($.fn.fmatter[formatter]) cont = $.fn.fmatter[formatter](value, {colModel:field});
						row.push(label + ':' + cont);
					}
				}
				break;
			default:
				row.push(cellval);
		}
		ret.push(row.join(', '));
// tool.debug(ret);
// tool.debug("end<<<<<<");		
		return ret.join('<BR />');
	}
	
});

jQuery.extend($.fn.fmatter.resultLink, {
    unformat:function(cellval, options, pos, cnt){
        var results = options.colModel.searchoptions.value;
		var cellv = 0;
		$.each(results, function(i, val) {
			if(val == cellval)
				 cellv = i;
		});
		return cellv;
    }
});
jQuery.extend($.fn.fmatter.testorLink, {
    unformat:function(cellval, options, pos, cnt){
        var results = options.colModel.searchoptions.value;
		var cellv = 0;
		$.each(results, function(i, val) {
			if(val == cellval)
				 cellv = i;
		});
		return cellv;
    }
});
jQuery.extend($.fn.fmatter.bResultLink, {
    unformat:function(cellval, options, pos, cnt){
        var results = options.colModel.searchoptions.value;
		var cellv = 0;
		$.each(results, function(i, val) {
			if(val == cellval)
				 cellv = i;
		});
		return cellv;
    }
});
