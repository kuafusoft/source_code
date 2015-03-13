// JavaScript Document
(function(){
	var DB = KF_GRID_ACTIONS.qygl;
	var tool = new kf_tool();
	
	DB.zzvw_wz_yl = function(grid){
		$table.supr.call(this, grid);
	};

	var $table = DB.zzvw_wz_yl;
	tool.extend($table, gc_grid_action);

	$table.prototype.contextActions = function(action, el){
// tool.debug(action);
		return $table.supr.prototype.contextActions.call(this, action, el);
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
