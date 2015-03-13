// JavaScript Document
function codec_rel_construct(gridId, jsonData){
    jsonData.gridOptions.subGridRowExpanded = function(subgrid_id, row_id) { 
        expandSubGridRow('rel_id', subgrid_id, row_id, 'codec', 'cycle');
    };
    jsonData.contextMenu = function(action, el){
        var handled = true;
        var rowId = $(el).attr('id');
        switch(action){
            case 'report':
				var dialog_params = {
					title:'Generate the test report',
					height:300,
					width:600
				};
                var url = '/jqgrid/jqgrid/db/codec/table/rel/oper/report/element/' + rowId;
                actionDialog(dialog_params, url);
                break;

            default:
                handled = false;
        }          
        return handled;
    };
    return jsonData;
}                

function open_rel_inform(event, ui, url){
	inform_open(event, ui, url);
	$('#information_tabs #codec_rel #div_report_op #generate_report').bind('click', function(event){
		$.post('jqgrid/jqgrid/oper/report/db/codec/table/rel/table_name/rel/element/' + $('#element_id').val(), function(data){
			debug(data);
            location.href = "download.php?filename=" + encodeURIComponent(data['file_name']) + "&remove=0";
			$('#information_tabs #codec_rel #table_reports').trigger('reloadGrid');
	
		}, 'json');
	});
//    displayDetail();
	listReports('#information_tabs #codec_rel #table_reports', '#div_reports_pager', 'codec', 'rel', $('#element_id').val());
}
