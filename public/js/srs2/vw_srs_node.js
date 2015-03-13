// JavaScript Document
function initSrs(gridId, jsonData){
    jsonData.gridOptions.subGridRowExpanded = function(subgrid_id, row_id) { 
        expandSubGridRow('srs_node_info_id', subgrid_id, row_id, 'srs2', 'prj_srs_node_info');
    };
    jsonData.addOptions.beforeShowForm = function(formId){
        var $category = $("#srs20_0vw_srs_node0_0srs_category_id");
        var $code = $("#srs20_0vw_srs_node0_0code");
        $category.bind('change', function(){
            $.post('jqgrid/jqgrid', {db:'srs2', table:'vw_srs_node', oper:'getNextCode', element:$category.val()}, function(nextCode){
                $code.val(nextCode);
            });       
        });
    };
    jsonData.onContextMenuShow = function(el){
        var editStatus = el.children("td[aria-describedby='srs2_vw_srs_node_list_srs20_0vw_srs_node0_0edit_status_id']").html();
//debug(editStatus);
        switch(editStatus.toLowerCase()){
            case 'editing': // Can Review, edit, view
                el.hideContextMenuItems(['publish', 'linkcase']);
                el.showContextMenuItems(['edit', 'askreview']);
                break;
            case 'reviewing':
                el.hideContextMenuItems(['publish', 'linkcase', 'edit', 'askreview']);
                break;
            case 'reviewed':
                el.hideContextMenuItems(['edit', 'askreview']);
                el.showContextMenuItems(['publish', 'linkcase']);
                break;
            case 'published':
                el.hideContextMenuItems(['edit', 'publish', 'askreview']);
                el.showContextMenuItems(['linkcase']);
                break;
        }
    };
    
    jsonData.contextMenu = function(action, el){
        var handled = true;
        var rowId = $(el).attr('id');
        switch(action){
            case 'askreview':
                $('<div id="srs_review" title="Ask to Review" />').load('/jqgrid/jqgrid/db/srs2/table/vw_srs_node/oper/askReview/element/' + rowId, function(data){
                    var dialog = $(this).html(data)
                		.dialog({
                		    width:600,
                		    height:400,
                			autoOpen: false,
                			title: 'Review SRS',
                			modal: true,
                			buttons: {
                				Ok: function() {
                				    $.post('/jqgrid/jqgrid', {oper:'askReview', db:'srs2', table:'vw_srs_node', element:rowId, comment:$("textarea#note").text()}, function(data){
                    					dialog.dialog( "close" );
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
            case 'history':
                $('<div id="srs_history" />').load('/jqgrid/jqgrid', {oper:'history', db:'srs2', table:'vw_srs_node', element:rowId}, function(data){
                    var dialog = $(this).html(data)
                		.dialog({
                		    width:800,
                			autoOpen: false,
                			title: 'SRS History',
                			modal: true,
                			buttons: {
                				Ok: function(){
                                    dialog.dialog("close");
                                }
                			}
                		});
                
                	dialog.dialog('open');
                });
                break;
            case 'linkcase':
                var dialog = $('div#srs_linkcase');
                if (dialog.length == 0){
                    $('<div id="srs_linkcase" />').load('/jqgrid/jqgrid/oper/linkcase/db/srs2/table/vw_srs_node/element/' + rowId, 
                        function(data){
                            dialog = $(this).html(data)
                    		.dialog({
                    		    width:800,
                    			autoOpen: false,
                    			title: 'SRS Link Cases',
                    			modal: true,
                    			open:function(event, ui){
                                    var db = 'xiaotian', table = 'vw_testcase';
                                    var options = {
                                        config:{
                                            url:'jqgrid/jqgrid',
                                            data:{oper:'getGridOptions', table:table, db:db},
                                            getConfig:true
                                        },
                                        gridOptions:{
                                            url:'jqgrid/list',
                                            postData:{table:table, db:db},
                                            editurl:'jqgrid/jqgrid/db/' + db + '/table/' + table,
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
                                        construct:function(gridId, jsonData){
                                            jsonData.buttons = {
                                                add:{
                                                    caption:'Add',
                                                    title:'Add selected cases',
                                                    onClickButton:'linkCasesToSrs'
                                                }
                                            }
                                            return jsonData;
                                        }
                                    };
                                    return grid_init('#srs_linkcase_list', '#srs_linkcase_pager', options);
                                },
                                close:function(event, ui){
                                    dialog.dialog('destroy');
                                }
                    		});
debug(dialog);                    		
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
debug(dialog);                        
                    	dialog.dialog('open');
                    }
                    
                break;
                
            default:
                handled = false;
                
        }
        return handled;
    }
//debug(jsonData);
    return jsonData;
}

function linkCasesToSrs(action, gridId, options){
    debug(action);
    debug(gridId);
    debug(options);
    var selectedRows = $(gridId).getGridParam('selarrrow');
    debug(selectedRows);
    if (selectedRows.length == 0){
        alert("Please select a record first");
        return;
    }
    $.post('jqgrid/jqgrid', {db:'srs2', table:'vw_srs_node', oper:'linkcase', srs_node_info_id:$("#srs_node_info_id").val(), element:JSON.stringify(selectedRows)}, 
        function(data, status){
            if (data == 1004){ // not found the action
                alert("Sorry, this feature " + action + " is not implemented yet");
            }
            else
    			$(gridId).trigger('reloadGrid');
		}
	);
        
}
