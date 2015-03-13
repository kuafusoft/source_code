// JavaScript Document
function initPrj_Srs(gridId, jsonData){
    jsonData.gridOptions.subGridRowExpanded = function(subgrid_id, row_id) { 
        expandSubGridRow('prj_srs_node_info_id', subgrid_id, row_id, 'srs2', 'prj_srs_node_info_testcase');
    };
/*    
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
*/    
/*
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
                
            default:
                handled = false;
                
        }
        return handled;
    }
*/    
//debug(jsonData);

    return jsonData;
}

