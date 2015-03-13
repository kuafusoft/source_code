// JavaScript Document
(function(){
	var DB = KF_GRID_ACTIONS.mcu;
	var tool = new kf_tool();
	
	DB.zzvw_peripheral_ver = function(grid){
		$table.supr.call(this, grid);
	};

	var $table = DB.zzvw_peripheral_ver;
	tool.extend($table, gc_grid_action);

	var db = 'mcu', table = 'zzvw_peripheral_ver';
	$table.prototype.gencode = function(peripheral_ver_id, device_ver_id){
		var wait_dialog = tool.waitingDialog();
		$.post('/jqgrid/jqgrid/oper/gencode/db/' + db + '/table/' + table, {element:JSON.stringify(peripheral_ver_id), device_ver_id:device_ver_id}, function(data){
			wait_dialog.dialog('close');
			if(tool.handleRequestFail(data))return;
// tool.debug(data);			
			location.href = "/download.php?filename=" + encodeURIComponent(data) + "&remove=1";
		});
	};

	$table.prototype.buttonActions = function(key, options){
		var gridId = this.getParams('gridSelector');
		var p_id = this.getParams('p_id');
		var selectedRows = $(gridId).getGridParam('selarrrow');
		var tc_id = $('#div_hidden #id').val();
		var baseId = "information_tabs_xt_testcase_" + tc_id;
		switch(key){
			case 'gencode':
				if (tool.checkSelectedRows(selectedRows, 1)){
					this.gencode(selectedRows, p_id);
				}
				break;
			default:
				$table.supr.prototype.buttonActions.call(this, key, options);
		}
	};
})();