// JavaScript Document
(function(){
	var DB = KF_GRID_ACTIONS.qygl;
	var tool = new kf_tool();
	
	DB.zzvw_yw_dingdan = function(grid){
		$table.supr.call(this, grid);
	};

	var $table = DB.zzvw_yw_dingdan;
	tool.extend($table, gc_grid_action);

	$table.prototype.contextActions = function(action, el){
// tool.debug(action);
		return $table.supr.prototype.contextActions.call(this, action, el);
	};

	$table.prototype.information_open = function(divId, element_id, pageName){
		pageName = pageName || 'all';
		$table.supr.prototype.information_open.call(this, divId, element_id, pageName);
		//事件绑定
		//订单类型绑定交易方
		var target = [
			{
				selector:'#' + divId + ' #hb_id', 
				type:'select', 
				field:'yw_fl_id', 
				url:'/jqgrid/jqgrid/oper/get_hb_by_yw_fl/db/qygl/table/hb'
			},
		];
		tool.linkage({selector:'#' + divId + ' #yw_fl_id'}, target);
		
		//交易方和物资之间绑定
		target = [
			{
				selector:'#' + divId + ' #wz_id', 
				type:'select', 
				field:'hb_id', 
				url:'/jqgrid/jqgrid/oper/get_wz_by_hb/db/qygl/table/zzvw_wz'
			},
		];
		tool.linkage({selector:'#' + divId + ' #hb_id'}, target, {selector:{yw_fl_id:'#' + divId + ' #yw_fl_id'}});
		
		//物资和计量单位及默认单价绑定
		$('#' + divId + " #wz_id").unbind('change').bind('change', function(event){
			var option = $(this).find("option:selected"), default_price = option.attr('default_price'), unit_name = option.attr('unit_name'), remained = option.attr('remained');
// tool.debug([default_price, unit_name, remained]);
			$('#' + divId + ' #amount_post').val(unit_name);
			$('#' + divId + ' #price').val(default_price);
			$('#' + divId + ' #remained_post').val(unit_name);
			$('#' + divId + ' #remained').val(remained);
		});
		
		//数量、单价和总价之间绑定
		var auto_generate_total = function(){
			var $source = ['#' + divId + ' #amount', '#' + divId + ' #price'];
			var $dest = '#' + divId + ' #total_money';
// tool.debug([$source, $dest]);
			tool.auto_fill_calc_result($dest, $source, '*');
		}
		$('#' + divId + ' #amount').bind('keyup', auto_generate_total);
		$('#' + divId + ' #price').bind('keyup', auto_generate_total);
		
	};
	
	$table.prototype.getGridsForInfo = function(divId){
		var grids = [
			{tab:'genzong', container:'genzong', table:'zzvw_yw_dingdan_trace', params:{real_table:'yw_dingdan_trace'}},
		];
		return grids;
	};
	
	$table.prototype.getFilterRules = function(rowId, n){
// tool.debug(n);
		if(n.tab == 'genzong'){
			return rules = [{field:'yw_dingdan_id', op:'eq', data:rowId}];
		}
		return $table.supr.prototype.getFilterRules.call(this, rowId, n);
	}
	// $table.prototype.bindEventsForInfo = function(divId, rowId){
		// var base = 'div#' + divId + ' #div_view_edit';
		// this.setLinkage(base, ['select#testcase_type_id', 'select#testcase_module_id']);
	// };
	
	$table.prototype.infoBtnActions = function(action, p){
		var gridSelector = this.getParams('gridSelector');
tool.debug([action, p]);
		switch(action){
			case 'qx': //取消订单
				// 询问是否真的取消该订单，如果是，则修改订单状态，否则，不动
				var element_id = $('#' + p.divId + ' #div_hidden #id').val();
				var buttons = {
					'取消订单':function(){
						var dialog = this;
						$.post('/jqgrid/jqgrid/oper/change_status/db/qygl/table/zzvw_yw_dingdan', {element:element_id, status:'qx'}, function(data){
tool.debug(data);
							$(dialog).dialog('close');
							$('#' + p.divId).dialog('close');
							$(gridSelector).trigger('reloadGrid');
						}, 'json');
					},
					'放弃':function(){
						$(this).dialog('close');
					}
				};
				tool.optionsDialog('真的要取消本订单？', '取消订单', buttons, 300, 200);
				break;
				
			case 'jieshu': //结束订单
				//询问是否真的已经完成，如果是，则修改订单状态，否则，不动
				var element_id = $('#' + p.divId + ' #div_hidden #id').val();
				var buttons = {
					'订单完成':function(){
						var dialog = this;
						tool.debug(dialog);
						$.post('/jqgrid/jqgrid/oper/change_status/db/qygl/table/zzvw_yw_dingdan', {element:element_id, status:'jieshu'}, function(data){
tool.debug(data);
							$(dialog).dialog('close');
							$('#' + p.divId).dialog('close');
							$(gridSelector).trigger('reloadGrid');
						}, 'json');
					},
					'放弃':function(){
						$(this).dialog('close');
					}
				};
				tool.optionsDialog('本订单已经完成？', '订单完成', buttons, 300, 200);
				break;
				
			case 'genzong':
				//跟踪包括大量内容，比如运输，入库，生产等
				
				break;
				
			default:
				$table.supr.prototype.infoBtnActions.call(this, action, p);
		}
	};
	
	$table.prototype.getParamsForDefaultAction = function(action, data){
		var $this = this;
		var params = $table.supr.prototype.getParamsForDefaultAction.call(this, action, data);
		switch(action){
			case 'cg': //采购
				params['width'] = 1000;
				params['height'] = 600;
				params['open'] = function(){
					$("#div_cg input[date='date']").each(function(i){tool.datePick(this);});
					var checkbox = {
						cg_zf_pay:'div_cg_zf_pay', 
						has_yunshu:'div_yunshu', yunshu_zf_pay:'div_yunshu_zf_pay', 
						has_zhuangxie:'div_zhuangxie', zhuangxie_zf_pay:'div_zhuangxie_zf_pay',
						has_ruku:'div_ruku'
						};
					for(var cb in checkbox){
						$('#' + cb).unbind('change').bind('change', {div_id:checkbox[cb]}, function(event){
							var checked = this.checked;
							if(checked){
								$('#' + event.data.div_id).show();
							}
							else	
								$('#' + event.data.div_id).hide();
						})
					}
					
					//绑定资金账户和余额的联动关系，并且如果选择了票据账户，则应显示可用票据，否则，隐藏之
					var zjzh = ['cg_zf', 'yunshu_zf', 'zhuangxie_zf'];
					for(var i in zjzh){
						var target = [
							{
								selector:'#div_' + zjzh[i] + '_pay' + ' #' + zjzh[i], 
								type:'text', 
								field:'id', 
								url:'/jqgrid/jqgrid/oper/getfieldvalue/db/qygl/table/zjzh/fields/remained,bizhong'
							},
							{
								selector:'#div_' + zjzh[i] + '_pay' + ' #' + zjzh[i] + '_zj_pj_id',
								base: '#div_' + zjzh[i] + '_pay' + ' #' + zjzh[i],
								type: 'select',
								field:'id',
								url: '/jqgrid/jqgrid/oper/getpj/db/qygl/table/zjzh',
								moreOp:{action:$this, fun:'moreOpForPJ'}
							}
						];
						tool.linkage({selector:'#div_' + zjzh[i] + '_pay' + ' #' + zjzh[i] + '_out_zjzh_id'}, target);

						
						//将票据和支付金额绑定，票据面值不可分割，故选中了一个票据就指定了支付金额
						$('#div_' + zjzh[i] + '_pay' + ' #' + zjzh[i] + '_zj_pj_id').unbind('change').bind('change', {zh:zjzh[i]}, function(event){
							var current = $(this).find("option:selected"), pj_id = $(current).val(), total_money = $(current).attr('total_money');
							var zh = event.data['zh'], base = '#div_' + zh + '_pay' + ' #' + zh;
// tool.debug(event.data);
							tool.debug([current, pj_id, total_money, zh]);
							$(base + '_remained').val(total_money);
							$(base + '_amount').val(total_money);//.attr('disabled', true);
							// $(base + '_cost').val(0);//.attr('disabled', true);
						});
					}
					
					//绑定数量、单价和总价之间的联动关系
					//绑定资金账户和余额的联动关系
					var divs = ['cg', 'yunshu', 'zhuangxie'], amount, price, total;
					var auto_generate_total = function(event){
						var amount = $('#' + event.data.div + '_amount').val();
						var price = $('#' + event.data.div + '_price').val();
						var total = event.data.div + '_total';
						$('#' + total).val(amount * price);
						tool.debug([amount, price, total]);
					}
					for(var i in divs){
						amount = divs[i] + '_amount';
						price = divs[i] + '_price';
						$('#' + amount).bind('keyup', {div:divs[i]}, auto_generate_total);
						$('#' + price).bind('keyup', {div:divs[i]}, auto_generate_total);
					}
					
				};
				params['validFunction'] = function(data, params){
					var real_data = data['data'];
					// 各种检查
					//对于支付部分，需要检查支付金额要小于账户余额，如果是票据账户，则支付金额应等于票据金额
					var zjzh = {cg_zf:'采购支付', yunshu_zf:'支付运费', zhuangxie_zf:'支付装卸费'};
					var remained, amount;
					for(var i in zjzh){
						remained = parseFloat(real_data[i['remained']]);
						amount = parseFloat(real_data[i['amount']]);
						cost = parseFloat(real_data[i['cost']]);
						if(amount > remained + cost){
							data['passed'].push(i);
							data['tips'].push(zjzh[i] + "账户余额不足");
						}
					}
					//需要检查运输总重量应小于或等于采购总重量
					var cg_amount = parseFloat(data['data']['cg[amount]']), 
						yunshu_amount = parseFloat(data['data']['yunshu[amount]']), 
						zhuangxie_amount = parseFloat(data['data']['zhuangxie[amount]']), ruku_detail, ruku_amount = 0;
					if(yunshu_amount > cg_amount){
						data['passed'].push('yunshu_amount');
						data['tips'].push("运输量超过了采购量");
					}
					if(zhuangxie_amount > yunshu_amount){
						data['passed'].push('zhuangxie_amount');
						data['tips'].push("装卸量超过了运输量");
					}
					ruku_detail = data['data']['ruku']['data'];
					for(i in ruku_detail){
						ruku_amount += ruku_detail[i]['amount'];
					}
					if(ruku_amount > zhuangxie_amount || ruku_amount > cg_amount || ruku_amount > yunshu_amount){
						data['passed'].push('ruku_amount');
						data['tips'].push("入库量超过了应有的数量");
					}
// tool.debug(data);
					return data['passed'].length == 0;
				};
				params['completeFunction'] = function(data, params){
					tool.debug(data);
					tool.debug(params);
				};
				break;
				
			case 'pd': //盘点
				break;
				
			case 'th': //退货
				break;
		
		}
// tool.debug(params);
		return params;
	};
	
	$table.prototype.moreOpForPJ = function(data, target){
// tool.debug(target)	;
		if(data.length > 0){
			$(target.selector).attr('disabled', false);
			$(target.base + '_amount').attr('disabled', true);
		}
		else{
			$(target.selector).attr('disabled', true);
			$(target.base + '_amount').attr('disabled', false);
		}
	};
	
	$table.prototype.sumRukuAmount = function(data){
		tool.debug(data);
	}
}());
