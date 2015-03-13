function category_construct(gridId, options){
    options.gridOptions.subGrid = true;
    options.gridOptions.subGridRowExpanded = function(subgrid_id, row_id) { 
        expandSubGridRow('srs_category_id', subgrid_id, row_id, 'sys_req', 'vw_prj_srs_node', 'sys_req', 'srs_category');
    };
    options.onContextMenuShow = function(el){
        var isactive = el.children("td[aria-describedby*='0_0srs_category0_0isactive']").html().toLowerCase();
//debug(isactive);
        el.hideContextMenuItems(['inactivate', 'activate']);
        if (isactive == 'active')el.showContextMenuItems(['inactivate']);
        else el.showContextMenuItems(['activate']);
    };
    options.gridComplete = function(gridId){
        ajaxuploadItem("div.ui-pg-div:contains('Import SRS')", 'import_srs', 
			'/jqgrid/jqgrid/oper/importsrs/db/sys_req/table/srs_category', 'json', {}, '*', 
			function(file, response){
				debug(response);
//				alert(file);
				var text = '';
				var title = 'Success';
				if (response['success']){
					text = "Complete import the SRS";
				}
				else if (response['error']){
					text = "Error:\n" + response['error'].join('\n');
					title = "ERROR/Warning";
				}
				if(response['warning']){
					text += '\nWarning:\n' + response['warning'].join('\n');
					text += '\nPlease check it';
				}
				noticeDialog(text, title);								
			});
    };
    return options;
};