// JavaScript Document
var hengshan = hengshan || {db:'hengshan'};
hengshan.prj = hengshan.prj || {table:'prj'};
hengshan.prj.construct = function(gridId, options){
    options.editOptions.beforeShowForm = function(formId){
        debug(formId);
        $('#hengshan0_0prj0_0platform_id').combobox();
        $('#hengshan0_0prj0_0os_id').combobox();
    }
    options.gridOptions.subgrid = true;
    options.gridOptions.subGridRowExpanded = function(subgrid_id, row_id) {
        XT.expandSubGridRow('prj_id', subgrid_id, row_id, 'hengshan', 'vw_srs_node', 'hengshan', 'prj');
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
        XT.ajaxuploadItem('li#importsrs', 'importsrs', '/jqgrid/jqgrid/oper/importsrs/db/hengshan/table/prj/element/' + $(el).attr('id'), {}, 'xls');
    };
    options.contextMenu = function(action, el){
        var handled = true;
        var rowId = $(el).attr('id');
        switch(action){
            case 'cloneit':
                $('<div id="hengshan_cloneit" title="Clone the project" />').load('/jqgrid/jqgrid/db/hengshan/table/prj/oper/cloneit/element/' + rowId, function(data){
                    var dialog = $(this).html(data)
                		.dialog({
                		    width:600,
                		    height:400,
                			autoOpen: false,
                			title: 'Clone the project',
                			modal: true,
                			buttons: {
                				Ok: function() {
                				    $.post('/jqgrid/jqgrid', {oper:'cloneit', db:'hengshan', table:'prj', element:rowId, name:$("#clone_prj_name").val()}, function(data, textStatus){
                                        if (data != ''){
                                            noticeDialog(data, "Fail to clone the project");
                                        }
                                        else{
                        					dialog.dialog( "close" );
                        					$('#hengshan_cloneit').remove();
                                			$(gridId).trigger('reloadGrid');
                                		}
                                    });
                				},
                				Cancel: function(){
                                    dialog.dialog("close");
                					$('#hengshan_cloneit').remove();
                                }
                			}
                		});
                
                	dialog.dialog('open');
                });
                break;
            case 'importsrs':
                $('<div id="hengshan_importsrs" title="Import the SRS file for the project" />').load('/jqgrid/jqgrid/db/hengshan/table/prj/oper/importsrs/element/' + rowId, function(data){
                    var dialog = $(this).html(data)
                		.dialog({
                		    width:600,
                		    height:400,
                			autoOpen: false,
                			title: 'Import SRS File for the project',
                			modal: true,
                			buttons: {
                				Ok: function() {
                				    $.post('/jqgrid/jqgrid', {oper:'importsrs', db:'hengshan', table:'prj', element:rowId}, function(data, textStatus){
//debug(data);
//debug(textStatus);
                                        if (data != ''){
                                            noticeDialog(data, "Fail to import the SRS File");
                                        }
                                        else{
                        					dialog.dialog( "close" );
                        					$('#hengshan_importsrs').remove();
                                			$(gridId).trigger('reloadGrid');
                                		}
                                    }, 'json');
                				},
                				Cancel: function(){
                                    dialog.dialog("close");
                					$('#hengshan_importsrs').remove();
                                }
                			}
                		});
                
                	dialog.dialog('open');
                });
                break;
            case 'exportsrs':
                var dialog = XT.noticeDialog("Generating the report......", "Waiting");
			    $.post('/jqgrid/jqgrid', {oper:'exportsrs', db:'hengshan', table:'prj', element:rowId}, function(data, textStatus){
debug(data);
debug(textStatus);
                    dialog.dialog("close");
                    location.href = "download.php?filename=" + encodeURIComponent(data['filename']) + "&remove=0";
                }, 'json');
                break;
            default:
                handled = false;
        }
        return handled;
    }


    return options;
};

hengshan.prj.buttonActions = function(key, gridId, options){
    var db = 'hengshan', table = 'prj';
	switch(key){
		case 'prj_diff':
            var selectedRows = $(gridId).getGridParam('selarrrow');
            if (selectedRows.length <= 1){
                alert("Please select at least 2 records first");
                return;
            }
            var dialog = XT.noticeDialog("Generating the report......", "Waiting", false);
            $.post('/jqgrid/jqgrid', {db:db, table:table, element:selectedRows, oper:'prj_diff'}, 

                function(data, status){
                    dialog.dialog("close");
//debug(data);                
                    location.href = "download.php?filename=" + encodeURIComponent(data['filename']) + "&remove=0";
//                    location.href = "/jqgrid/jqgrid/db/" + db + "/table/" + table + "/oper/download/filename/" + encodeURIComponent(data['filename']);
        		}, 
                'json'
        	);
            break;
	}
	return false;

};


