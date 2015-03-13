// JavaScript Document
function initSrs_Prj(gridId, jsonData){
    jsonData.gridOptions.subGridRowExpanded = function(subgrid_id, row_id) { 
        expandSubGridRow('prj_srs_node_info_id', subgrid_id, row_id, 'sys_req', 'prj_srs_node_info_testcase');
    };
    jsonData.addOptions.beforeShowForm = function(formId){
//debug(formId);    
//debug(jsonData);
        var editUrl = jsonData.gridOptions.editurl;
//debug(editUrl);        
        var $category = formId.find("#sys_req0_0vw_srs_node_prj0_0srs_category_id");
        var $code = formId.find("#sys_req0_0vw_srs_node_prj0_0code");
        var $prj = formId.find("#sys_req0_0vw_srs_node_prj0_0prj_id");
//        $category.combobox();
        $category.bind('change', function(){
            $.post('jqgrid/jqgrid', {db:'sys_req', table:'vw_srs_node_prj', oper:'getNextCode', element:$category.val()}, function(nextCode){
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
    jsonData.onContextMenuShow = function(el){
        var editStatus = el.children("td[aria-describedby*='0_0vw_srs_node_prj0_0edit_status']").html().toLowerCase();
        var isactive = el.children("td[aria-describedby*='0_0vw_srs_node_prj0_0isactive']").html().toLowerCase();
        el.hideContextMenuItems(['activate', 'inactivate', 'publish', 'linkcase', 'edit', 'askreview', 'review', 'linkproject']);
        if (isactive == 'active')el.showContextMenuItems(['inactivate']);
        else el.showContextMenuItems(['activate']);
        switch(editStatus){
            case 'editing': // Can Review, edit, view
                el.showContextMenuItems(['edit', 'askreview', 'publish']);
                break;
            case 'wait for review':
            case 'reviewing':
                el.showContextMenuItems(['publish', 'review']);
                break;
            case 'reviewed':
                el.showContextMenuItems(['publish']);
                break;
            case 'published':
                el.showContextMenuItems(['edit', 'linkcase', 'linkproject']);
                var prj_id = el.children("td[aria-describedby*='0_0vw_srs_node_prj0_0prj_id']").html().toLowerCase();
                if (prj_id != 'null')
                    el.showContextMenuItems(['linkcase']);
                break;
        }
    };
    jsonData.contextMenu = function(action, el){
        var handled = true;
        var rowId = $(el).attr('id');
        switch(action){
            case 'askreview':
                $('<div id="srs_askreview" title="Ask to Review" />').load('/jqgrid/jqgrid/db/sys_req/table/vw_srs_node_prj/oper/askReview/element/' + rowId, function(data){
                    var dialog = $(this).html(data)
                		.dialog({
                		    width:600,
                		    height:400,
                			autoOpen: false,
                			title: 'Ask to Review SRS',
                			modal: true,
                            close:function(event, ui){
                                $('#srs_askreview').remove();
                            },
                			buttons: {
                				Ok: function() {
                				    $.post('/jqgrid/jqgrid', {oper:'askReview', db:'sys_req', table:'vw_srs_node_prj', element:rowId, reviewer:$("#srs_reviewers").val(), project:$('#project').val()}, function(data){
                    					dialog.dialog( "close" );
    			                         $(gridId).trigger('reloadGrid');
                                    });
                				},
                				Cancel: function(){
                                    dialog.dialog("close");
                                }
                			}
                		});
                
                	dialog.dialog('open');
                });
                break;
            case 'review':
                $('<div id="srs_review" title="Review" />').load('/jqgrid/jqgrid/db/sys_req/table/vw_srs_node_prj/oper/review/element/' + rowId, function(data){
                    var dialog = $(this).html(data)
                		.dialog({
                		    width:600,
                		    height:400,
                			autoOpen: false,
                			title: 'Review SRS',
                			modal: true,
                            close:function(event, ui){
                                $('#srs_review').remove();
                            },
                			buttons: {
                				Submit: function() {
                				    var params = {oper:'review', 
                                        db:'sys_req', 
                                        table:'vw_srs_node_prj', 
                                        element:rowId, 
                                        comment:$("#srs_review_comment").val(), 
                                        result:$('#srs_review_result').val(), 
                                        submit:true
                                    };
                				    $.post('/jqgrid/jqgrid', params, function(data){
                    					dialog.dialog( "close" );
    			                         $(gridId).trigger('reloadGrid');
                                    });
                				},
                				Save: function() {
                				    var params = {oper:'review', 
                                        db:'sys_req', 
                                        table:'vw_srs_node_prj', 
                                        element:rowId, 
                                        comment:$("#srs_review_comment").val(), 
                                        result:$('#srs_review_result').val(), 
                                        submit:false
                                    };
                				    $.post('/jqgrid/jqgrid', params, function(data){
                    					dialog.dialog( "close" );
    			                         $(gridId).trigger('reloadGrid');
                                    });
                				},
                				Cancel: function(){
                                    dialog.dialog("close");
                                }
                			}
                		});
                
                	dialog.dialog('open');
                });
                break;
            case 'information':
                $('<div id="srs_information" />').load('/jqgrid/jqgrid', {oper:'information', db:'sys_req', table:'vw_srs_node_prj', element:rowId}, function(data){
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
                    $('<div id="srs_linkcase" />').load('/jqgrid/jqgrid/oper/linkcase/db/sys_req/table/vw_srs_node_prj/element/' + rowId, 
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
                                            db:'sys_req', 
                                            table:'vw_srs_node_prj', 
                                            element:rowId,
                                            cases:selected, 
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
                                            del:false,
                                        },
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
                $('<div id="srs_linkproject" />').load('/jqgrid/jqgrid/oper/linkproject/db/sys_req/table/vw_srs_node_prj/element/' + rowId, function(data){
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
                				    var i = 0;
                				    $('[name="projects"]:checked:not(:disabled)').each(function(){
                                        projects[i ++] = $(this).val();
                                    });
//debug(projects);                                    
                				    $.post('/jqgrid/jqgrid', {oper:'linkproject', db:'sys_req', table:'vw_srs_node_prj', element:rowId, projects:projects}, function(data){
                    					dialog.dialog( "close" );
                                    });
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
//debug(jsonData);

    return jsonData;
}

