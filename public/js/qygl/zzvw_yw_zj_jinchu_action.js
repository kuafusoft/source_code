// 资金进出管理
(function(){
	var DB = KF_GRID_ACTIONS.qygl;
	var tool = new kf_tool();
	DB.zzvw_yw_zj_jinchu = function(grid){
		$table.supr.call(this, grid);
	};

	var $table = DB.zzvw_yw_zj_jinchu;
	tool.extend($table, gc_grid_action);

	$table.prototype.information_open = function(divId, element_id, pageName, display_status){
		var $this = this;
		pageName = pageName || 'all';
		$table.supr.prototype.information_open.call(this, divId, element_id, pageName, display_status);
		if(display_status != 1) // NOT VIEW
			eventsBindForZJ_JinChu(divId, this, false);
	};
	
	var eventsBindForZJ_JinChu = function(divId, context, first){ //订单类事件绑定
		$('#' + divId + ' #zj_cause_id').bind('change', function(event){
			var option = $(this).find("option:selected"), zj_direct_id = option.attr('zj_direct_id'), zj_cause_id = $(this).val();
			var disp_fields = [], hidden_fields = [];
			switch(zj_direct_id){
				case 1://支付
					disp_fields = ['out_zjzh_id'];
					hidden_fields = ['in_zjzh_id'];
					break;
				case 2://回款
					disp_fields = ['in_zjzh_id'];
					hidden_fields = ['out_zjzh_id'];
					break;
				case 3://有进有出
					disp_fields = ['out_zjzh_id', 'in_zjzh_id'];
					hidden_fields = [];
					break;
			}
			for(var i in disp_fields)
				$('#' + divId + ' #' + disp_fields[i]).show();
			for(var i in hidden_fields)
				$('#' + divId + ' #' + hidden_fields[i]).hide();
		});
		$('#' + divId + ' #zj_fl_id').bind('change', function(event){
			var option = $(this).find("option:selected"), is_pj = option.attr('is_pj'), zj_fl_id = $(this).val(), zj_cause_id = $('#' + divId + ' #zj_cause_id').val();
			var disp_fields = [], hidden_fields = [];
			switch(is_pj){
				case 1://票据
					disp_fields = ['out_zjzh_id'];
					hidden_fields = ['in_zjzh_id'];
					break;
				case 2://现金
					disp_fields = ['in_zjzh_id'];
					hidden_fields = ['out_zjzh_id'];
					break;
			}
			for(var i in disp_fields)
				$('#' + divId + ' #' + disp_fields[i]).show();
			for(var i in hidden_fields)
				$('#' + divId + ' #' + hidden_fields[i]).hide();
		});
		
		//交易方和物资之间绑定
		var prefix = 'zzvw_pici_sh', temp = '#' + divId + ' #' + prefix + '_temp';
		target = [
			{
				selector:temp + ' #item_id', 
				type:'select', 
				field:'item_id', 
				url:'/jqgrid/jqgrid/oper/get_dingdan_by_hb/db/qygl/table/dingdan/yw_fl_id/1/status/1' //正在执行中的订单
			}
		];
		tool.linkage({selector:temp + ' #hb_id'}, target);
		//订单和计量单位及默认单价绑定
		$(temp + ' #item_id').unbind('change').bind('change', function(event){
			var option = $(this).find("option:selected"), unit_name = option.attr('unit_name'), wz_id = option.attr('wz_id');
// tool.debug([default_price, unit_name, remained, temp]);
			$(temp + ' #amount_post').html(unit_name);
			$(temp + ' #wz_id').val(wz_id);
		});
		
		//数量、单价和总价之间绑定
		var auto_generate_total = function(){
			var $source = [temp + ' #amount', temp + ' #price'];
			var $dest = temp + ' #total_money';
			tool.auto_fill_calc_result($dest, $source, '*', 2);
		}
		// $(temp + ' #amount').bind('keyup', auto_generate_total);
		// $(temp + ' #price').bind('keyup', auto_generate_total);
	};
	
	$table.prototype.buttonActions = function(action, p){
		p = p || {};
		var db = this.getParams('db'), table = this.getParams('table');
		var $this = this;
		var gridId = this.getParams('gridSelector');
		var selectedRows = $(gridId).getGridParam('selarrrow');
		var element = JSON.stringify(selectedRows);
		switch(action){
			case 'jh': //重启订单
				if (tool.checkSelectedRows(selectedRows, 1)){
					// 询问是否真的重启该订单，如果是，则修改订单状态，否则，不动
					var buttons = {
						'重启订单':function(){
							var dialog = this;
							$.post('/jqgrid/jqgrid/oper/change_status/db/qygl/table/yw_cg', {id:element, status:'jh'}, function(data){
	// tool.debug(data);
								$(dialog).dialog('close');
								$(gridId).trigger('reloadGrid');
							}, 'json');
						},
						'放弃':function(){
							$(this).dialog('close');
						}
					};
					tool.optionsDialog('真的要重启本订单？', '重启订单', buttons, 300, 200);
				}
				break;
					
			case 'js': //结束订单
				if (tool.checkSelectedRows(selectedRows, 1)){
					//询问是否真的已经完成，如果是，则修改订单状态，否则，不动
					var buttons = {
						'结束订单':function(){
							var dialog = this;
							tool.debug(dialog);
							$.post('/jqgrid/jqgrid/oper/change_status/db/qygl/table/yw_cg', {id:element, status:'jieshu'}, function(data){
	// tool.debug(data);
								$(dialog).dialog('close');
								$(gridId).trigger('reloadGrid');
							}, 'json');
						},
						'放弃':function(){
							$(this).dialog('close');
						}
					};
					tool.optionsDialog('结束选中的订单？', '结束订单', buttons, 300, 200);
				}
				break;
					
			case 'genzong':
					//跟踪包括大量内容，比如运输，入库，生产等
					
				break;
					
			default:
				$table.supr.prototype.buttonActions.call(this, action, p);
		}
	};
}());
