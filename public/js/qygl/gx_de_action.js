// JavaScript Document
//工序定额管理
(function(){
	var DB = KF_GRID_ACTIONS.qygl;
	var tool = new kf_tool();
	DB.gx_de = function(grid){
		$table.supr.call(this, grid);
	};

	var $table = DB.gx_de;
	tool.extend($table, gc_grid_action);

	$table.prototype.contextActions = function(action, el){
// tool.debug(action);
		return $table.supr.prototype.contextActions.call(this, action, el);
	};

// defined('YW_FL_CG') || define('YW_FL_CG', 1); //下采购单
// defined('YW_FL_YUNRU') || define('YW_FL_YUNRU', 2); //运入
// defined('YW_FL_XIEZAI') || define('YW_FL_XIEZAI', 3); //卸载
// defined('YW_FL_ZHUANGZAI') || define('YW_FL_ZHUANGZAI', 17); //装载
// defined('YW_FL_RUKU') || define('YW_FL_RUKU', 4); //入库
// defined('YW_FL_SCDJ') || define('YW_FL_SCDJ', 5);//生产登记
// defined('YW_FL_ZJOUT') || define('YW_FL_ZJOUT', 6); //资金转出
// defined('YW_FL_ZJIN') || define('YW_FL_ZJIN', 7); //资金转入
// defined('YW_FL_ZHUANZHANG') || define('YW_FL_ZHUANZHANG', 8); //转账
// defined('YW_FL_TUIHUO') || define('YW_FL_TUIHUO', 9); //退货
// defined('YW_FL_JIESHOUTUIHUO') || define('YW_FL_JIESHOUTUIHUO', 10); //接收退货
// defined('YW_FL_YIKU') || define('YW_FL_YIKU', 11); //移库
// defined('YW_FL_JIESHOUDINGDAN') || define('YW_FL_JIESHOUDINGDAN', 12); //接收订单
// defined('YW_FL_CHUKU') || define('YW_FL_CHUKU', 13); //出库
// defined('YW_FL_YUNCHU') || define('YW_FL_YUNCHU', 14); //运出
// defined('YW_FL_PANKU') || define('YW_FL_PANKU', 15); //盘库
// defined('YW_FL_TX') || define('YW_FL_TX', 16); //贴息
// defined('YW_FL_JIERU') || define('YW_FL_JIERU', 18); //借入款项
// defined('YW_FL_HUANKUAN') || define('YW_FL_HUANKUAN', 19); //还款
// defined('YW_FL_JIECHU') || define('YW_FL_JIECHU', 20); //借出款项

	$table.prototype.information_open = function(divId, element_id, pageName){
		var $this = this;
		pageName = pageName || 'all';
		$table.supr.prototype.information_open.call(this, divId, element_id, pageName);
		var base = '#' + divId + ' #div_view_edit > fieldset > table.ces > tbody',
			input_temp = '#' + divId + ' #gx_de_input #gx_de_input_temp', 
			output_temp = '#' + divId + ' #gx_de_output #gx_de_output_temp';
		//事件绑定
		
		//物资变化应绑定计量基数单位变化
		$(base + ' >#ces_tr_wz_id #wz_id').bind('change', function(){
			var wz_id = $(this).val(), o = $(this).find("option:selected");
			$(base + " > #ces_tr_calc_base #calc_base_post").html(o.attr('unit'));
			// 需要和工序结合设置输入和输出的内容
			$this.setIO(divId, base, $this);
		});
		//工艺类型绑定
		$(base + '>#ces_tr_gx_id #gx_id').bind('change', function(){
			// 需要和物资结合设置输入和输出的内容
			$this.setIO(divId, base, $this);
		});
		//如果输入或输出部分选中的wz是原料的话，那么原料项应和物资项同
		$(input_temp + ' #wz_id').bind('change', function(){
			var o_wz = $(this).find('option:selected'), unit = o_wz.attr('unit'), wz_fl_id = o_wz.attr('wz_fl_id');
			$(input_temp + ' #amount_post').html(unit);
			if(wz_fl_id == 1){ //原料
				$(input_temp + ' #yl_id').val($(this).val());
				$(input_temp + ' #has_shell').val(2);
			}
		});
		$(output_temp + ' #wz_id').bind('change', function(){
			var o_wz = $(this).find('option:selected'), unit = o_wz.attr('unit'), wz_fl_id = o_wz.attr('wz_fl_id');
			$(output_temp + ' #amount_post').html(unit);
			if(wz_fl_id == 1){ //原料
				$(output_temp + ' #yl_id').val($(this).val());
				$(output_temp + ' #has_shell').val(2);
			}
		});
		
	};
	
	$table.prototype.setIO = function(divId, base, $this){
tool.debug(base);	
		var o_gx = $(base + '>#ces_tr_gx_id #gx_id').find('option:selected'), gx_fl_id = parseInt(o_gx.attr('gx_fl_id')) || 0, yl_id = parseInt(o_gx.attr('wz_id')) || 0,
			wz_id = parseInt($(base + '>#ces_tr_wz_id #wz_id').val()) || 0, o_wz = $(base + '>#ces_tr_wz_id #wz_id').find('option:selected'),
			unit = o_wz.attr('unit'), zuhe = o_wz.attr('zuhe');
		var input_temp = '#' + divId + ' #gx_de_input #gx_de_input_temp', output_temp = '#' + divId + ' #gx_de_output #gx_de_output_temp';
		var input_add = $(input_temp + ' #gx_de_input_add'), output_add = $(output_temp + ' #gx_de_output_add');
tool.debug([gx_fl_id, wz_id]);	
		if(gx_fl_id != 0 && wz_id != 0){
tool.debug("here");			
			switch(gx_fl_id){
				case 1: //置换：所用的置换材料和产品的体积相关
					var tj = parseFloat(o_wz.attr('tj'));
					var input_amount = tj * 1.03; //3%的损耗
					var output_amount = tj * 0.97;
					if(yl_id != 0){
						$(input_temp + ' #wz_id').val(yl_id);
						$(input_temp + ' #yl_id').val(yl_id);
						$(input_temp + ' #has_shell').val(2);
						$(input_temp + ' #amount').val(input_amount);
						input_add.click();
					}
										
					$(output_temp + ' #wz_id').val(wz_id);
					$(output_temp + ' #yl_id').val(yl_id);
					$(output_temp + ' #amount').val(1);
					$(output_temp + ' #amount_post').html(unit);
					output_add.click();
					
					$(output_temp + ' #amount').val(output_amount);
					break;
					
				case 2: //组合：多个零件组合成一个产品
					//输入好多个，输出一个
					//组合关系从产品描述里得到，保存在zuhe里
					if(zuhe){
						zuhe = JSON.parse(zuhe);
						for(var i in zuhe){
							var item = zuhe[i], input_wz_id = item['input_wz_id'], input_amount = item['amount'];
							$(input_temp + ' #wz_id').val(input_wz_id);
							$(input_temp + ' #amount').val(input_amount);
							$(input_temp + ' #yl_id').val(yl_id);
							$(input_temp + ' #amount_post').html(unit);
							input_add.click();
						}
					}
						
					$(output_temp + ' #wz_id').val(wz_id);
					$(output_temp + ' #amount').val(1);
					$(output_temp + ' #yl_id').val(yl_id);
					$(output_temp + ' #amount_post').html(unit);
					output_add.click();
					break;
					
				case 3: //分解：和相应的组合工序对应
					//输入一个，输出好多个
					$(input_temp + ' #wz_id').val(wz_id);
					$(input_temp + ' #amount').val(1);
					$(input_temp + ' #yl_id').val(yl_id);
					$(input_temp + ' #amount_post').html(unit);
					input_add.click();
					
					if(zuhe){
						zuhe = JSON.parse(zuhe);
						for(var i in zuhe){
							var item = zuhe[i], input_wz_id = item['input_wz_id'], input_amount = item['amount'];
							$(output_temp + ' #wz_id').val(input_wz_id);
							$(output_temp + ' #amount').val(input_amount);
							$(output_temp + ' #yl_id').val(yl_id);
							$(output_temp + ' #amount_post').html(unit);
							output_add.click();
						}
					}
					break;
					
				case 4: //涂裹：生成一个外壳，所用涂料数量和产品表面积相关
					var bmj = parseFloat(o_wz.attr('bmj'));
					var input_amount = bmj * 1.03; //3%的损耗
					
					$(input_temp + ' #amount').val(input_amount);
					$(output_temp + ' #amount').val(output_amount);
					break;
					
				case 5: //加工:一个进，一个出，没有数量上的变化
					$(input_temp + ' #wz_id').val(wz_id);
					$(input_temp + ' #amount').val(1);
					$(input_temp + ' #yl_id').val(yl_id);
					$(input_temp + ' #amount_post').html(unit);
					input_add.click();
					
					$(output_temp + ' #wz_id').val(wz_id);
					$(output_temp + ' #amount').val(1);
					$(output_temp + ' #yl_id').val(yl_id);
					$(output_temp + ' #amount_post').html(unit);
					output_add.click();
					break;
			}
		}
	};
	
	$table.prototype.opAfterTemp = function(data, target, more){
// tool.debug(data);
		var divId = more.divId;
		var yw_fl_id = parseInt($('#' + divId + ' #yw_fl_id').val());
		$('#' + divId + ' #div_view_edit > fieldset > table.ces > tbody > #ces_tr_hb_id #hb_id_label').html('交易伙伴');
		if(data.html == ''){
			$('#' + divId + ' #ces_tr_detail_ids').hide();
			$('#' + divId + ' #div_view_edit > fieldset > table.ces > tbody > #ces_tr_price').hide();
			$('#' + divId + ' #div_view_edit > fieldset > table.ces > tbody > #ces_tr_amount').hide();
			$('#' + divId + ' #div_view_edit > fieldset > table.ces > tbody > #ces_tr_helper_price').hide();
			$('#' + divId + ' #div_view_edit > fieldset > table.ces > tbody > #ces_tr_helper_id').hide();
		}
		else{
			this.setLinkage(divId, yw_fl_id, more.action);
		}
	}
	
	$table.prototype.setLinkage = function(divId, yw_fl_id, context){
tool.debug(["setLinkage", divId, yw_fl_id]);
		if(yw_fl_id == 0)
			$('#' + divId + ' #ces_tr_detail_ids').hide();
		else
			$('#' + divId + ' #ces_tr_detail_ids').show();
		$('#' + divId + ' #div_view_edit > fieldset > table.ces > tbody > #ces_tr_price').hide();
		$('#' + divId + ' #div_view_edit > fieldset > table.ces > tbody > #ces_tr_amount').hide();
		$('#' + divId + ' #div_view_edit > fieldset > table.ces > tbody > #ces_tr_helper_price').hide();
		$('#' + divId + ' #div_view_edit > fieldset > table.ces > tbody > #ces_tr_helper_id').hide();
		
// tool.debug([yw_fl_id, divId]);	

		switch(yw_fl_id){ //不同的业务，有不同的界面，需要采用不用的事件绑定
			case 1: //采购
			case 12: //接订单
				eventsBindForCG(divId, yw_fl_id, context);
				break;
			case 2://收货
			case 14: //发货
				eventsBindForYunshu(divId, yw_fl_id, context);
				break;
			case 6: //资金转出
			case 7: //资金转入
				eventsBindForZJ_Jinchu(divId, yw_fl_id, context);
				break;
			case 5: //生产数据登记
				eventsBindForSCDJ(divId, yw_fl_id, context);
				break;
		}
		
	};
	
	var eventsBindForSCDJ = function(divId, yw_fl_id, context){
		$('#' + divId + ' #div_view_edit > fieldset > table.ces > tbody #hb_id_label').html('员工');
		var temp = '#' + divId + ' #scdj';
		//需要建立物资和缺陷之间的联动关系
		$(temp + ' #wz_id').unbind('change').bind('change', function(){
			var gx_id = $('#' + divId + ' #div_view_edit > fieldset > table.ces > tbody > #gx_id').val();
			var wz_id = $(this).val();
		});
	};
	
	var eventsBindForZJ_Jinchu = function(divId, yw_fl_id, context){
		var temp = '#' + divId + ' #zj_jinchu', zjzh_id = (yw_fl_id == 6 ? 'out_zjzh_id' : 'in_zjzh_id');
		$(temp + ' #' + zjzh_id).unbind('change').bind('change', function(){
			var zjzh = $(this).val(), o = $(this).find("option:selected"), zj_fl_id = o.attr('zj_fl_id'), remained = o.attr('remained');
			tool.debug([zjzh, zj_fl_id, remained]);
			if(zj_fl_id == 1){ //现金
				$(temp + ' #ces_tr_zj_pj_id').hide();
				$(temp + ' #ces_tr_pj_code').hide();
				$(temp + ' #ces_tr_expire_date').hide();
				$(temp + ' #amount').attr('disabled', false);
			}
			else{
				$(temp + ' #ces_tr_zj_pj_id').show();
				$(temp + ' #ces_tr_pj_code').show();
				$(temp + ' #ces_tr_expire_date').show();
				$(temp + ' #amount').attr('disabled', true);
			}
			
		});
		if(yw_fl_id == 6){//支付
			$(temp + ' #zj_pj_id').unbind('change').bind('change', function(){//如果用票据支付，则票据不可分割
				var o = $(this).find('option:selected');
				if(o.attr('total_money'))
					$(temp + ' #amount').val(o.attr('total_money'));
			});
		}
	};
	
	var eventsBindForCG = function(divId, yw_fl_id, context){ //订单类事件绑定
		//交易方和物资之间绑定
		//对于订单类业务而言，不需要显示Price 和 amount项目
		$('#' + divId + ' #div_view_edit > fieldset > table.ces > tbody > #ces_tr_price').hide();
		$('#' + divId + ' #div_view_edit > fieldset > table.ces > tbody > #ces_tr_amount').hide();
		$('#' + divId + ' #div_view_edit > fieldset > table.ces > tbody > #ces_tr_helper_price').hide();
		$('#' + divId + ' #div_view_edit > fieldset > table.ces > tbody > #ces_tr_helper_id').hide();
		if(yw_fl_id == 1){ //采购
			$('#' + divId + ' #hb_id_label').html('供应商');
		}
		else{ //销售
			$('#' + divId + ' #hb_id_label').html('客户');
		}
		
		var prefix = 'dingdan', temp = '#' + divId + ' #' + prefix + '_temp';
		target = [
			{
				selector:temp + ' #wz_id', 
				type:'select', 
				field:'hb_id', 
				url:'/jqgrid/jqgrid/oper/get_wz_by_hb/db/qygl/table/wz'
			},
		];
		tool.linkage({selector:'#' + divId + ' #hb_id'}, target, {selector:{yw_fl_id:'#' + divId + ' #yw_fl_id'}});
		//物资和计量单位及默认单价绑定
		$(temp + ' #wz_id').unbind('change').bind('change', function(event){
			var option = $(this).find("option:selected"), default_price = option.attr('default_price'), 
				unit_name = option.attr('unit_name'), remained = option.attr('remained');
// tool.debug([default_price, unit_name, remained]);
			$(temp + ' #amount_post').val(unit_name);
			$(temp + ' #price').val(default_price);
			$(temp + ' #price_post').val('元/' + unit_name);
			$(temp + ' #remained_post').val(unit_name);
			$(temp + ' #remained').val(remained);
		});
		
		//数量、单价和总价之间绑定
		var auto_generate_total = function(){
			var $source = [temp + ' #amount', temp + ' #price'];
			var $dest = temp + ' #total_money';
			tool.auto_fill_calc_result($dest, $source, '*', 2);
		}
		$(temp + ' #amount').bind('keyup', auto_generate_total);
		$(temp + ' #price').bind('keyup', auto_generate_total);
	};
	
	var eventsBindForYunshu = function(divId, yw_fl_id, context){ //运输类事件绑定
tool.debug('events for yunshu');
		//对于运输类业务而言，需要显示Price 和 amount项目
		$('#' + divId + ' #div_view_edit > fieldset > table.ces > tbody > #ces_tr_price').show();
		$('#' + divId + ' #div_view_edit > fieldset > table.ces > tbody > #ces_tr_amount').show();
		$('#' + divId + ' #div_view_edit > fieldset > table.ces > tbody > #ces_tr_helper_price').show();
		$('#' + divId + ' #div_view_edit > fieldset > table.ces > tbody > #ces_tr_helper_id').show();
		
		$('#' + divId + ' #div_view_edit > fieldset > table.ces > tbody > #ces_tr_hb_id #hb_id_label').html('承运人');
		$('#' + divId + ' #div_view_edit > fieldset > table.ces > tbody > #ces_tr_helper_id #helper_id_label').html('装卸人');
		$('#' + divId + ' #div_view_edit > fieldset > table.ces > tbody > #ces_tr_helper_price #helper_price_label').html('装卸单价');
		
		//应自动填充默认运输及装卸单价
		$.post('/jqgrid/jqgrid/oper/get_wz_by_id/db/qygl/table/wz', {id:[1,2]}, function(data){ //
// tool.debug(data);
			var default_price = data[1]['default_price'];
			$('#' + divId + ' #div_view_edit > fieldset > table.ces > tbody > #ces_tr_price #price').val(default_price);
			var helper_price = data[2]['default_price']; //装卸单价
			$('#' + divId + ' #div_view_edit > fieldset > table.ces > tbody > #ces_tr_helper_price #helper_price').val(helper_price);
		}, 'json');
		
		var prefix = yw_fl_id == 2 ? 'ruku' : 'chuku', temp = '#' + divId + ' #' + prefix + '_temp';
		target = [
			{
				selector:temp + ' #dingdan_id', 
				type:'select', 
				field:'hb_id', 
				url:'/jqgrid/jqgrid/oper/get_dingdan_by_hb/db/qygl/table/dingdan/dingdan_status_id/1' //获取未完成的订单
			},
		];
		tool.linkage({selector:temp + ' #hb_id'}, target, {selector:{yw_fl_id:'#' + divId + ' #yw_fl_id'}});
		
		if(yw_fl_id == 2){//入库
			target = [
				{
					selector:temp + ' #defect_id', 
					type:'select', 
					field:'dingdan_id', 
					url:'/jqgrid/jqgrid/oper/get_defect_by_dingdan/db/qygl/table/defect_gx_wz',
					moreOp:{action:context, fun:'moreOpForDingdanChange', temp:temp}
				},
			];
			tool.linkage({selector:temp + ' #dingdan_id'}, target, {selector:{yw_fl_id:'#' + divId + ' #yw_fl_id'}});
		}
		else{ //出库
			target = [
				{
					selector:temp + ' #pici_detail_id', 
					type:'select', 
					field:'dingdan_id', 
					url:'/jqgrid/jqgrid/oper/get_pici_by_dingdan/db/qygl/table/pici/gx/last', //根据订单得到最后的产品批次
					moreOp:{action:context, fun:'moreOpForDingdanChangeForChuku', temp:temp}
				},
			];
			tool.linkage({selector:temp + ' #dingdan_id'}, target, {selector:{yw_fl_id:'#' + divId + ' #yw_fl_id'}});
			//绑定批次和数量之间的关系
			$(temp + ' #pici_detail_id').unbind('change').bind('change', function(){
				var pici_detail = $(temp + ' #pici_detail_id').find("option:selected");
		// tool.debug(dingdan);
				if($(temp + ' #pici_detail_id').val() != 0){
					var remained = parseFloat(pici_detail.attr('remained'));
					$(temp + ' #amount').val(remained);
				}
				else{
					$(temp + ' #amount').val(0);
				}
			});
		}
	};

	$table.prototype.moreOpForDingdanChange = function(data, target, more){
		tool.debug(data);
		tool.debug(target);
		tool.debug(more);
		var dingdan = $(more.temp + ' #dingdan_id').find("option:selected");
// tool.debug(dingdan);
		if($(more.temp + ' #dingdan_id').val() != 0){
			var remained = parseFloat(dingdan.attr('amount')) - parseFloat(dingdan.attr('completed_amount'));
			var unit = dingdan.attr('unit');
			$(more.temp + ' #amount').val(remained);
			$(more.temp + ' #amount_post').html(unit);
		}
		else{
			$(more.temp + ' #amount').val(0);
			$(more.temp + ' #amount_post').html('?');
		}
		
	};

	$table.prototype.moreOpForDingdanChangeForChuku = function(data, target, more){
		var dingdan = $(more.temp + ' #dingdan_id').find("option:selected");
// tool.debug(dingdan);
		if($(more.temp + ' #dingdan_id').val() != 0){
			var unit = dingdan.attr('unit');
			$(more.temp + ' #amount_post').html(unit);
		}
		else{
			$(more.temp + ' #amount_post').html('?');
		}
	};
	// $table.prototype.getGridsForInfo = function(divId){
		// var grids = [
			// {tab:'genzong', container:'genzong', table:'zzvw_yw_dingdan_trace', params:{real_table:'yw_dingdan_trace'}},
		// ];
		// return grids;
	// };
	
	// $table.prototype.getFilterRules = function(rowId, n){
// // tool.debug(n);
		// if(n.tab == 'genzong'){
			// return rules = [{field:'yw_dingdan_id', op:'eq', data:rowId}];
		// }
		// return $table.supr.prototype.getFilterRules.call(this, rowId, n);
	// }
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
