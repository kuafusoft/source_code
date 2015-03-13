var $prompted = [];
function rollup_personnel_construct(gridId, options){
    var db = "rollup", table = "personnel";
    var prefix = db + '0_0' + table + '0_0';

    options.gridOptions.subGridRowExpanded = function(subgrid_id, row_id) { 
        expandSubGridRow('personnel_id', subgrid_id, row_id, 'rollup', 'rollup');
    };
    
    options.gridOptions.onSortCol = function(index, iCol, sortorder){
        /*
        如果是日期统计列，则提示仅对当前页内的数据进行排序。
        */
        if (index.search('0_0') == -1){
            if (typeof $prompted[index] == 'undefined')
                $prompted[index] = false;
            if (!$prompted[index])
                alert("ONLY SORT DATA IN CURRENT PAGE!!!");
            $prompted[index] = true;
        }
    };

    options.gridComplete = function(gridId){
        ajaxuploadItem("div.ui-pg-div:contains('Import')", 'rollup_import_personnel', 
			'/jqgrid/jqgrid/oper/import/db/' + db + '/table/' + table, false, {}, '*', 
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
					text = "Complete import the Rollup information";
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

function rollup_export_complete(data){
	//$.post('/service/download', data);
    location.href = "download.php?filename=" + encodeURIComponent(data['filename']) + "&remove=0";
}

function srs_diff_validate(data){
	return true;
}

function srs_buttonActions(key, gridId, options){
	switch(key){
		case 'export':
			actionDialog({div_id:'srs_diff', width:600, height:400, title:'Diff Among Tags'}, '/jqgrid/jqgrid/db/hengshan/table/vw_srs_node/oper/diff', srs_diff_validate, srs_diff_complete);
            break;
	}
}
