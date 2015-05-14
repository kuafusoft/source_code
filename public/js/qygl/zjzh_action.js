// JavaScript Document
(function(){
	var DB = KF_GRID_ACTIONS.qygl;
	var tool = new kf_tool();
	DB.zjzh = function(grid){
		$table.supr.call(this, grid);
	};

	var $table = DB.zjzh;
	tool.extend($table, gc_grid_action);

	function transfer_validation(params){
		var validated = true;
		var transfer_amount = Number($("#amount").val());
		var remained = Number($("#remained").html());
		var cost = Number($('#cost').html());
	//debug(transfer_amount);
	//debug(remained);
	//debug(cost);
		if (transfer_amount < 0){
			$("#error_warning").html("转账金额只能是正数");
			validated = false;
		}
		else if (transfer_amount + cost > remained){
			$("#error_warning").html("转账金额及费用不能大于转出帐户余额");
			validated = false;
		}
		return validated;
	}                
	
	$table.prototype.buttonActions = function(action, p){
		p = p || {};
		var db = this.getParams('db'), table = this.getParams('table');
		var $this = this;
		var gridId = this.getParams('gridSelector');
		var selectedRows = $(gridId).getGridParam('selarrrow');
		var element = JSON.stringify(selectedRows);
		switch(action){
			case 'huabo': //资金划拨
                var dialog_params = {div_id:'zjzh_huabo', height:500};
                var url = '/jqgrid/jqgrid/db/qygl/table/zjzh/oper/huabo';
                             
                tool.actionDialog(dialog_params, url, transfer_validation);
				break;
			default:
				$table.supr.prototype.buttonActions.call(this, action, p);
		}
	};
}());
