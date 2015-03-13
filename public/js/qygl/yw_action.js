// JavaScript Document
(function(){
	var DB = KF_GRID_ACTIONS.qygl;
	var tool = new kf_tool();
	DB.yw = function(grid){
		$table.supr.call(this, grid);
	};

	var $table = DB.yw;
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
		var yw_fl_id = parseInt($('#' + divId + ' #yw_fl_id').val());
		//事件绑定
		//业务类型绑定交易方和不同业务的模板
		var target = [
			{
				selector:'#' + divId + ' #div_view_edit > fieldset > table.ces > tbody > #ces_tr_hb_id #hb_id', 
				type:'select', 
				field:'yw_fl_id', 
				url:'/jqgrid/jqgrid/oper/get_hb_by_yw_fl/db/qygl/table/hb'
			},
			{
				selector:'#' + divId + ' #div_view_edit > fieldset > table.ces > tbody > #ces_tr_helper_id #helper_id', 
				type:'select', 
				field:'yw_fl_id', 
				url:'/jqgrid/jqgrid/oper/get_helper_by_yw_fl/db/qygl/table/hb'
			},
			{
				selector:'#' + divId + ' tr#ces_tr_detail_ids > td.cont-td', 
				type:'div', 
				field:'yw_fl_id', 
				url:'/jqgrid/jqgrid/oper/get_wz_item_temp_by_yw_fl/db/qygl/table/yw/id/' +element_id,
				moreOp:{action:this, fun:'opAfterTemp', divId:divId}
			},
		];
		tool.linkage({selector:'#' + divId + ' #yw_fl_id'}, target);
		if(yw_fl_id == 5){// 生产登记，需要重新生成detail界面
			$.post('/jqgrid/jqgrid/oper/get_wz_item_temp_by_yw_fl/db/qygl/table/yw/value/5/id/' + element_id, function(data){
				$('#' + divId + ' tr#ces_tr_detail_ids > td.cont-td').html(data.html);
				$this.opAfterTemp(data, target, $this);
			}, 'json')
		}
		else{
			this.setLinkage(divId, yw_fl_id, this, true);
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
			this.setLinkage(divId, yw_fl_id, more.action, false);
		}
	}
	
	$table.prototype.setLinkage = function(divId, yw_fl_id, context, first){
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
				eventsBindForCG(divId, yw_fl_id, context, first);
				break;
			case 2://收货
			case 14: //发货
				eventsBindForYunshu(divId, yw_fl_id, context, first);
				break;
			case 6: //资金转出
			case 7: //资金转入
				eventsBindForZJ_Jinchu(divId, yw_fl_id, context, first);
				break;
			case 5: //生产数据登记
				eventsBindForSCDJ(divId, yw_fl_id, context, first);
				break;
		}
		
	};
	
	var getSCDJDetail = function(divId, gx_id){
		$.post('/jqgrid/jqgrid/oper/scdj_detail/db/qygl/table/scdj/gx/' + gx_id, function(data){
			var input_temp = '#' + divId + ' #ces_tr_detail_ids #fieldset_scdj_input';
			var output_temp = '#' + divId + ' #ces_tr_detail_ids #scdj_output_temp';

			tool.debug(data);
			$(input_temp + ' #scdj_input').html(data);
			$(input_temp).show();
			//增加event bind
			//如果输入产品有变化，则输出产品也跟着变化
			$(input_temp + ' #scdj_input').unbind('change').bind('change', function(){
				var input_wz_id = $(this).val();
				updateOutputWZList(output_temp, gx_id, input_wz_id);
			});
		});
	};
	
	var getSCDJDefect = function(output_temp, gx_id, wz_id){
		$.post('/jqgrid/jqgrid/oper/getdefectbygxwz/db/qygl/table/defect_gx_wz/gx/' + gx_id + '/wz/' + wz_id, function(data){
			var defect = $(output_temp + " #defect_id");
			tool.debug(data);
			defect.find('option').remove();
			tool.generateOptions(defect, data, 'id', 'name', true);
		}, 'json');
	};
	
	var getSCDJWeizhiPriceByZl = function(output_temp, gx_id, wz_id, defect_id){
		$.post('/jqgrid/jqgrid/oper/getdetail/db/qygl/table/gx_wz_zl_detail/gx/' + gx_id + '/wz/' + wz_id + '/defect/' + defect_id, function(data){
			var ck_weizhi_id = data.ck_weizhi_id || 0, price = data.price || 0;
			if(ck_weizhi_id > 0)
				$(output_temp + ' #ck_weizhi_id').val(ck_weizhi_id);
			if(price > 0)
				$(output_temp + ' #price').val(price);
		}, 'json');
	};
	
	var updateOutputWZList = function(output_temp, gx_id, input_wz_id){
		$.post('/jqgrid/jqgrid/oper/getoutputwz/db/qygl/table/wz/gx/' + gx_id + '/wz/' + input_wz_id, function(data){
			var wz = $(output_temp + " #wz_id");
			tool.debug(data);
			wz.find('option').remove();
			tool.generateOptions(wz, data, 'id', 'name', true);
		}, 'json');
	};

	var eventsBindForSCDJ = function(divId, yw_fl_id, context, first){
		if(first){
		
		}
		$('#' + divId + ' #div_view_edit > fieldset > table.ces > tbody #hb_id_label').html('员工');
		$('#' + divId + ' #div_view_edit > fieldset > table.ces > tbody > #ces_tr_helper_id').show();
		$('#' + divId + ' #div_view_edit > fieldset > table.ces > tbody > #ces_tr_helper_id #helper_id_label').html('检验员');
		
		var main_temp = '#' + divId + ' #ces_tr_detail_ids #scdj', 
			input_temp = '#' + divId + ' #ces_tr_detail_ids #fieldset_scdj_input', 
			output_temp = '#' + divId + ' #ces_tr_detail_ids #scdj_output_temp';
		// $(main_temp + ' table.ces').after('<div id="scdj_input" ></div>');
		//需要建立物资和缺陷之间的联动关系
		$(output_temp + ' #wz_id').unbind('change').bind('change', function(){
			var gx_id = parseInt($(main_temp + ' #gx_id').val());
			var wz_id = parseInt($(this).val());
			var defect_id = parseInt($(output_temp + ' #defect_id').val());
			if(gx_id > 0 && wz_id > 0){
				// getSCDJDetail(divId, gx_id, wz_id);
				getSCDJDefect(output_temp, gx_id, wz_id);
				if(defect_id > 0){
					getSCDJWeizhiPriceByZl(output_temp, gx_id, wz_id, defect_id);
				}
				
			}
		});
		$(output_temp + ' #defect_id').unbind('change').bind('change', function(){
			var gx_id = parseInt($(main_temp + ' #gx_id').val());
			var defect_id = parseInt($(this).val());
			var wz_id = parseInt($(output_temp + ' #wz_id').val());
			if(gx_id > 0 && wz_id > 0 && defect_id > 0){
				getSCDJWeizhiPriceByZl(output_temp, gx_id, wz_id, defect_id);
			}
		});
		$(main_temp + ' #gx_id').unbind('change').bind('change', function(){
			// var wz_id = parseInt($(main_temp + ' #wz_id').val());
			var gx_id = parseInt($(this).val());
			var gx_fl_id = parseInt($(this).find("option:selected").attr('gx_fl_id'));
			if(gx_id > 0 && gx_fl_id == 3){ //分解工序，则需要人工指定输入的产品和数量
				getSCDJDetail(divId, gx_id);
				
				// getSCDJDefect(output_temp, gx_id, wz_id);
			}
			else{
				$(input_temp).hide();
				//需要更新输出物资列表
				updateOutputWZList(output_temp, gx_id, 0);
			}
		});
	};
	
	var eventsBindForZJ_Jinchu = function(divId, yw_fl_id, context, first){
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
	
	var eventsBindForCG = function(divId, yw_fl_id, context, first){ //订单类事件绑定
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
	
	var eventsBindForYunshu = function(divId, yw_fl_id, context, first){ //运输类事件绑定
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
