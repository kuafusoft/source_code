// JavaScript Document
(function(){
	var DB = KF_GRID_ACTIONS.qygl;
	var tool = new kf_tool();
	DB.zzvw_yw_scdj = function(grid){
		$table.supr.call(this, grid);
	};

	var $table = DB.zzvw_yw_scdj;
	tool.extend($table, gc_grid_action);

	$table.prototype.information_open = function(divId, element_id, pageName, display_status){
		var $this = this;
		pageName = pageName || 'all';
		$table.supr.prototype.information_open.call(this, divId, element_id, pageName, display_status);
		if(display_status != 1) // NOT VIEW
			eventsBindForSCDJ(divId, this, false);
	};
	
	var eventsBindForSCDJ = function(divId, context, first){ //生产登记事件绑定
		var prefix = 'zzvw_pici_scdj', temp = '#' + divId + ' #' + prefix + '_temp';
		//员工和工序互动
		$('#' + divId + ' #hb_id').bind('change', function(event){
			var hb_id = $(this).val();
			$.post('/jqgrid/jqgrid/oper/get_gx_by_hb/db/qygl/table/zzvw_yg/hb_id/' + hb_id, function(data){
				if(data != '0')
					$(temp +' #gx_id').val(data);
			})
		})
		//订单和计量单位及默认单价绑定
		var show_detail = function(){
			var gx_id = $(temp + ' #gx_id').val(), wz_id = $(temp + ' #wz_id').val(), defect_id = $(temp + ' #defect_id').val() || 1;
			$.post("/jqgrid/jqgrid/oper/get_wz_detail/db/qygl/table/zzvw_wz/wz_id/" + wz_id + '/gx_id/' + gx_id + '/defect_id/' + defect_id, function(data){
				$(temp + ' #price').val(data.price);
				$(temp + ' #ck_weizhi_id').val(data.ck_weizhi_id);
			}, 'json');
		};

		$(temp + ' #wz_id').unbind('change').bind('change', function(event){
			var option = $(this).find("option:selected"), unit_name = option.attr('unit_name'), gx_id = $(temp + ' #gx_id').val();
// tool.debug([default_price, unit_name, remained, temp]);
			$(temp + ' #amount_post').html(unit_name);
			show_detail();
			//关联可用的缺陷列表
			tool.bindOptions({url:'/jqgrid/jqgrid/oper/get_defect_list/db/qygl/table/zzvw_wz/', data:{target:$(temp + ' #defect_id'), wz_id:$(this).val(), gx_id:gx_id, blankItem:true, currentVal:1}});
			// $.post('/jqgrid/jqgrid/oper/get_defect_list/db/qygl/table/zzvw_wz/', {wz_id:$(this).val(), gx_id:gx_id}, function(data){
				// $(temp + ' #defect_id').find('option').remove();
				// tool.generateOptions($(temp + ' #defect_id'), data, 'id', 'name', true, 1);
			// }, 'json');
		});
		
		$(temp + ' #gx_id').unbind('change').bind('change', function(event){
			show_detail();
		});
		
		$(temp + ' #defect_id').unbind('change').bind('change', function(event){
			show_detail();
		});
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
