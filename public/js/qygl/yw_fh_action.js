// JavaScript Document
(function(){
	var DB = KF_GRID_ACTIONS.qygl;
	var tool = new kf_tool();
	DB.yw_fh = function(grid){
		$table.supr.call(this, grid);
	};

	var $table = DB.yw_fh;
	tool.extend($table, gc_grid_action);

	$table.prototype.information_open = function(divId, element_id, pageName, display_status){
		var $this = this;
		pageName = pageName || 'all';
		$table.supr.prototype.information_open.call(this, divId, element_id, pageName, display_status);
		if(display_status != 1) // NOT VIEW
			eventsBindForFH(divId, this, false);
	};
	
	var eventsBindForFH = function(divId, context, first){ //订单类事件绑定
		//交易方和物资之间绑定
		var prefix = 'zzvw_xs_pici', temp = '#' + divId + ' #' + prefix + '_temp';
		var sc_pici_id = $(temp + ' #sc_pici_id');
		target = [
			{
				selector:temp + ' #dingdan_xs_id', 
				type:'select', 
				field:'dingdan_xs_id', 
				url:'/jqgrid/jqgrid/oper/get_dingdan_xs_by_kh/db/qygl/table/dingdan_xs/status/1' //正在执行中的订单
			}
		];
		tool.linkage({selector:temp + ' #kh_id'}, target);
		//订单和计量单位及默认单价绑定
		$(temp + ' #dingdan_xs_id').unbind('change').bind('change', function(event){
			var option = $(this).find("option:selected"), unit_name = option.attr('unit_name'), wz_id = option.attr('wz_id');
// tool.debug([default_price, unit_name, remained, temp]);
			$(temp + ' #amount_post').html(unit_name);
			$(temp + ' #wz_id').val(wz_id);
			//还要更新可用的产品批次
			$.post('/jqgrid/jqgrid/db/qygl/table/zzvw_sc_pici/oper/get_pici_by_wz/wz_id/' + wz_id, function(data){
tool.debug(data);
				var currentVal = oldVal = sc_pici_id.val();
				sc_pici_id.find('option').remove();
				tool.generateOptions(sc_pici_id, data, 'id', 'name', true, currentVal);
			}, 'json');
		});
		sc_pici_id.unbind('change').bind('change', function(event){
			var option = $(this).find("option:selected"), remained = option.attr('remained');
			$(temp + ' #amount').val(remained);
			$(temp + ' #amount').attr('max', remained);
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
							$.post('/jqgrid/jqgrid/oper/change_status/db/qygl/table/yw_xs', {id:element, status:'jh'}, function(data){
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
							$.post('/jqgrid/jqgrid/oper/change_status/db/qygl/table/yw_xs', {id:element, status:'jieshu'}, function(data){
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
