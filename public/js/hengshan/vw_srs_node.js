function vw_srs_node_construct(gridId, options){
    var db = "hengshan", table = "vw_srs_node";
    var prefix = db + '0_0' + table + '0_0';

    options.editOptions.beforeShowForm = function(formId){
        var $prj = formId.find('#' + prefix + "prj_id");
        $prj.attr('disabled', true);
    };

    options.addOptions.clearAfterAdd = false;
    options.addOptions.beforeShowForm = function(formId){
        var $category = formId.find('#' + prefix + "srs_category_id");
        var $code = formId.find('#' + prefix + "code");
        var $prj = formId.find('#' + prefix + "prj_id");
//debug(formId);    
//debug(jsonData);
        var editUrl = options.gridOptions.editurl;
//debug(editUrl);        
//        $category.combobox();
        $category.bind('change', function(){
            $.post('jqgrid/jqgrid', {db:db, table:table, oper:'getNextCode', element:$category.val()}, function(nextCode){
                $code.val(nextCode);
            });       
        });
        var pat = /parentid\/(.+?)\/parentdb\/(.+?)\/parenttable\/(.+)/;
        var res = editUrl.match(pat);
        if (res){
            var parentId = res[1];
            var parentDb = res[2];
            var parentTable = res[3];
            if (parentTable == 'srs_category'){
                $category.val(parentId);
                $category.attr('disabled', true);
                $category.change();
            }
            else if (parentTable == 'prj'){
                $prj.val(parentId);
                $prj.attr('disabled', true);
            }
        }
//debug(res);                
    };
    options.addOptions.afterSubmit = function(response, postdata){
        $("#" + prefix + "content").val('');
        $.post('jqgrid/jqgrid', {db:db, table:table, oper:'getNextCode', element:$("#" + prefix + "srs_category_id").val()}, function(nextCode){
            $("#" + prefix + "code").val(nextCode);
        });        
        return [true, ''];
    };

    options.onContextMenuShow = function(el){
        var isactive = el.children("td[aria-describedby*='0_0vw_srs_node0_0isactive']").html().toLowerCase();
        var linkStatusId = el.children("td[aria-describedby*='0_0vw_srs_node0_0link_status_id']").html().toLowerCase();
//debug(isactive);
        el.hideContextMenuItems(['inactivate', 'activate', 'linkcase', 'linkproject', 'comment', 'subscribe']);
		if (linkStatusId == 'add' || linkStatusId == '1'){ // NOTE: change the default data relation, then the id will be changed to text
            if (isactive == 'active')el.showContextMenuItems(['inactivate']);
            else el.showContextMenuItems(['activate']);
            el.showContextMenuItems(['linkcase', 'linkproject', 'comment', 'subscribe']);
        }
    };
    options.gridComplete = function(gridId){
        ajaxuploadItem("div.ui-pg-div:contains('Import')", 'import_srs', 
			'/jqgrid/jqgrid/oper/importsrs/db/' + db + '/table/' + table, 'json', {}, '*', 
			function(file, response){
debug(response);
//				alert(file);
				var text = '';
				var title = 'Success';
				if (typeof response['error'] != 'undefined'){
//alert("Error");				
					text = "Error:\n" + response['error'].join('\n');
					title = "ERROR/Warning";
				}
				else if (response['success']){
//alert("Success");				
					text = "Complete import the SRS";
				}
				if(typeof response['warning'] != 'undefined'){
//alert("warning");				
					text += '\nWarning:\n' + response['warning'].join('\n');
					text += '\nPlease check it';
				}
				noticeDialog(text, title);								
			});
    };
    
    options.contextMenu = function(action, el){
        var handled = true;
        var rowId = $(el).attr('id');
        switch(action){
            case 'information':
                var notice = noticeDialog("Collect information, please wait...", "loading", false);
                $('<div id="srs_information" />').load('/jqgrid/jqgrid', {oper:'information', db:db, table:table, element:rowId}, function(data){
                    var dialog = $(this).html(data)
                		.dialog({
                		    width:800,
                		    height:500,
                			autoOpen: false,
                			title: 'SRS Information',
                			modal: true,
                			buttons: {
                				Ok: function(){
                                    dialog.dialog("close");
                                }
                			},
                			open:function(event, ui){
                                $( "#srs_information_tabs" ).tabs({ selected: 'tabs-current' });        
                            },
                            close:function(event, ui){
                                $('#srs_information').remove();
                            }
                		});
                	notice.dialog("close");
                	dialog.dialog('open');
                });
                break;

            case 'linkcase':
                var dialog = $('div#srs_linkcase');
                if (dialog.length == 0){
                    $('<div id="srs_linkcase" />').load('/jqgrid/jqgrid/oper/linkcase/db/'+ db + '/table/' + table + '/element/' + rowId, 
                        function(data){
                            dialog = $(this).html(data)
                    		.dialog({
                    		    width:800,
                    			autoOpen: false,
                    			title: 'SRS Link Cases',
                    			modal: true,
                    			buttons:{
                                    'Link Cases':function(){
                                        var selected = $('#srs_linkcase_list').getGridParam('selarrrow');
//debug(selected);
                    				    var params = {
                                            oper:'linkcase', 
                                            db:db, 
                                            table:table, 
                                            element:rowId,
                                            cases:selected 
                                        };
                    				    $.post('/jqgrid/jqgrid', params, function(data){
                        					dialog.dialog( "close" );
//        			                         $(gridId).trigger('reloadGrid');
                                        });
                                    }
                                },
                    			open:function(event, ui){
                                    var tmp = {
                                        config:{
                                            url:'/jqgrid/jqgrid',
                                            data:{oper:'getGridOptions', table:'vw_testcase', db:'xiaotian'},
                                            getConfig:true
                                        },
                                        gridOptions:{
                                            url:'/jqgrid/list/db/xiaotian/table/vw_testcase',
                                            editurl:'/jqgrid/jqgrid/db/xiaotian/table/vw_testcase',
                                            rowNum:10
                                        },
                                        editOptions:{
                                        
                                        },
                                        navOptions:{
                                            view:false,
                                            edit:false,
                                            add:false,
                                            del:false
                                        }
                                    };
                                    return grid_init('#srs_linkcase_list', '#srs_linkcase_pager', tmp);
                                },
                                close:function(event, ui){
//                                    dialog.dialog('destroy');
                                    $('#srs_linkcase').remove();
                                }
                    		});
                    		dialog.dialog("open");
                    	});
                    }
                    else{
                    	dialog.dialog({
                		    width:800,
                			autoOpen: false,
                			title: 'SRS Link Cases',
                			modal: true,
                			open:function(event, ui){}
                		});
                    	dialog.dialog('open');
                    }
                break;
                
            case 'linkproject':
                $('<div id="srs_linkproject" />').load('/jqgrid/jqgrid/oper/linkproject/db/' + db + '/table/' + table + '/element/' + rowId, function(data){
                    var dialog = $(this).html(data)
                		.dialog({
                		    width:800,
                		    height:500,
                			autoOpen: false,
                			title: 'Link Projects',
                			modal: true,
                			buttons: {
                				Ok: function(){
                				    var projects = [];
                				    var unlinked = [];
                				    var i = 0;
                				    $('[name="projects"]:checked').each(function(){
                                        projects[i ++] = $(this).val();
                                    });
                                    i = 0;
                				    $('[name="projects"]:not(:checked)').each(function(){
                                        unlinked[i ++] = $(this).val();
                                    });
//debug(projects);                                    
                				    $.post('/jqgrid/jqgrid', {oper:'linkproject', db:db, table:table, element:rowId, projects:projects, unlinked:unlinked}, function(data){
                    					dialog.dialog( "close" );
        			                    $(gridId).trigger('reloadGrid');
                                    });
                                },
                                Cancel:function(){
                                    dialog.dialog('close');
                                }
                			},
                            close:function(event, ui){
                                $('#srs_linkproject').remove();
                            }
                		});
                	dialog.dialog('open');
                });
                break;

            default:
                handled = false;
                
        }
        return handled;
    };
    return options;
};

function srs_diff_complete(data){
	//$.post('/service/download', data);
    location.href = "download.php?filename=" + encodeURIComponent(data['filename']) + "&remove=0";
}

function srs_diff_validate(data){
	return true;
}

function srs_buttonActions(key, gridId, options){
    var selectedRows = $(gridId).getGridParam('selarrrow');
	switch(key){
		case 'diff':
			actionDialog({div_id:'srs_diff', width:600, height:400, title:'Diff Among Tags'}, '/jqgrid/jqgrid/db/hengshan/table/vw_srs_node/oper/diff', srs_diff_validate, srs_diff_complete);
            break;
        case 'link':
			actionDialog({div_id:'srs_link', width:600, height:400, title:'Link/unLink to Project', element:selectedRows}, '/jqgrid/jqgrid/db/hengshan/table/vw_srs_node/oper/link', srs_diff_validate);
            break;
	}
}
