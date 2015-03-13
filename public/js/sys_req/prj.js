// JavaScript Document
function ajaxuploadMenuItem(id, element){
    var item = $("li#" + id);
    var dialog;
    
    new AjaxUpload(item, {
        // Location of the server-side upload script
        // NOTE: You are not allowed to upload files to another domain
        action: '/jqgrid/jqgrid/oper/importsrs/db/sys_req/table/prj/element/' + element,
        // File upload name
        name: id,
        // Additional data to send
        data: {
/*
            controller:'jqgrid',
            action:'jqgrid',
            oper:'importsrs',
            db:'sys_req',
            table:'prj',
            element:element
*/            
        },
        // Submit file after selection
        autoSubmit: true,
        // The type of data that you're expecting back from the server.
        // HTML (text) and XML are detected automatically.
        // Useful when you are using JSON data as a response, set to "json" in that case.
        // Also set server response type to text/html, otherwise it will not work in IE6
        responseType: false,
        // Fired after the file is selected
        // Useful when autoSubmit is disabled
        // You can return false to cancel upload
        // @param file basename of uploaded file
        // @param extension of that file
        onChange: function(file, extension){
            if (extension != 'xls'){
                alert("Please select an xls file");
                return false;
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
            		.html('Uploading the log file ' + file)
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
debug(response);            
            noticeDialog(response, "completed");
        }
    });   

}

function prj_construct(gridId, options){
    options.editOptions.beforeShowForm = function(formId){
        debug(formId);
        $('#sys_req0_0prj0_0platform_id').combobox();
        $('#sys_req0_0prj0_0os_id').combobox();
    }
    options.gridOptions.subgrid = true;
    options.gridOptions.subGridRowExpanded = function(subgrid_id, row_id) { 
        expandSubGridRow('prj_id', subgrid_id, row_id, 'sys_req', 'vw_prj_srs_node', 'sys_req', 'prj');
    };
/*
    options.editOptions.afterSubmit = function(response, postData){
        debug(response);
        debug(postData);
    };
*/    
    options.onContextMenuShow = function(el){
        var editStatus = el.children("td[aria-describedby*='0_0prj0_0edit_status_id']").html().toLowerCase();
        var prjStatus = el.children("td[aria-describedby*='0_0prj0_0prj_status_id']").html().toLowerCase();
        var isactive = el.children("td[aria-describedby*='0_0prj0_0isactive']").html().toLowerCase();
debug([editStatus, prjStatus, isactive]);
        el.hideContextMenuItems(['edit', 'inactivate', 'activate', 'complete', 'publish', 'importsrs', 'exportsrs', 'importsrscasemapfile', 'exportsrscasemapfile', 'testreport']);
        if (editStatus == 'editing')
            el.showContextMenuItems(['publish']);
        else
            el.showContextMenuItems(['exportsrs', 'exportsrscasemapfile', 'testreport']);
        if (prjStatus == 'ongoing' && editStatus == 'published')el.showContextMenuItems(['complete', 'importsrs', 'importsrscasemapfile']);
        if (prjStatus != 'completed')el.showContextMenuItems(['edit']);
        if (isactive == 'active')el.showContextMenuItems(['inactivate']);
        else el.showContextMenuItems(['activate']);
debug(el);
        // ajaxupload the importsrs item
//        ajaxuploadMenuItem('importsrs', $(el).attr('id'));
        ajaxuploadItem('li#importsrs', 'importsrs', '/jqgrid/jqgrid/oper/importsrs/db/sys_req/table/prj/element/' + $(el).attr('id'), {}, 'xls');
    };
    options.contextMenu = function(action, el){
        var handled = true;
        var rowId = $(el).attr('id');
        switch(action){
            case 'cloneit':
                $('<div id="sys_req_cloneit" title="Clone the project" />').load('/jqgrid/jqgrid/db/sys_req/table/prj/oper/cloneit/element/' + rowId, function(data){
                    var dialog = $(this).html(data)
                		.dialog({
                		    width:600,
                		    height:400,
                			autoOpen: false,
                			title: 'Clone the project',
                			modal: true,
                			buttons: {
                				Ok: function() {
                				    $.post('/jqgrid/jqgrid', {oper:'cloneit', db:'sys_req', table:'prj', element:rowId, name:$("#clone_prj_name").val()}, function(data, textStatus){
//debug(data);
//debug(textStatus);
                                        if (data != ''){
                                            noticeDialog(data, "Fail to clone the project");
                                        }
                                        else{
                        					dialog.dialog( "close" );
                        					$('#sys_req_cloneit').remove();
                                			$(gridId).trigger('reloadGrid');
                                		}
                                    });
                				},
                				Cancel: function(){
                                    dialog.dialog("close");
                					$('#sys_req_cloneit').remove();
                                }
                			}
                		});
                
                	dialog.dialog('open');
                });
                break;
            case 'importsrs':
                $('<div id="sys_req_importsrs" title="Import the SRS file for the project" />').load('/jqgrid/jqgrid/db/sys_req/table/prj/oper/importsrs/element/' + rowId, function(data){
                    var dialog = $(this).html(data)
                		.dialog({
                		    width:600,
                		    height:400,
                			autoOpen: false,
                			title: 'Import SRS File for the project',
                			modal: true,
                			buttons: {
                				Ok: function() {
                				    $.post('/jqgrid/jqgrid', {oper:'importsrs', db:'sys_req', table:'prj', element:rowId}, function(data, textStatus){
//debug(data);
//debug(textStatus);
                                        if (data != ''){
                                            noticeDialog(data, "Fail to import the SRS File");
                                        }
                                        else{
                        					dialog.dialog( "close" );
                        					$('#sys_req_importsrs').remove();
                                			$(gridId).trigger('reloadGrid');
                                		}
                                    });
                				},
                				Cancel: function(){
                                    dialog.dialog("close");
                					$('#sys_req_importsrs').remove();
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


    return options;
}