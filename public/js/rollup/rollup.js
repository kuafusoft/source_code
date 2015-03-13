function rollup_rollup_construct(gridId, options){
    var db = "rollup", table = "rollup";
    var prefix = db + '0_0' + table + '0_0';

    options.addOptions.clearAfterAdd = false;
    options.addOptions.beforeShowForm = function(formId){
        var $personnel = formId.find("#rollup0_0rollup0_0personnel_id");
        var editUrl = options.gridOptions.editurl;
debug(editUrl);        
//        $personnel.combobox();
        var pat = /parentid\/(.+?)\/parentdb\/(.+?)\/parenttable\/(.+)/;
        var res = editUrl.match(pat);
        if (res){
debug(res);
            var parentId = res[1];
            $personnel.val(parentId);
            $personnel.attr('disabled', true);
        }
//debug(res);                
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
