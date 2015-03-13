// JavaScript Document
//工序定额管理
(function(){
	var DB = KF_GRID_ACTIONS.qygl;
	var tool = new kf_tool();
	DB.gx = function(grid){
		$table.supr.call(this, grid);
	};

	var $table = DB.gx;
	tool.extend($table, gc_grid_action);

	$table.prototype.contextActions = function(action, el){
// tool.debug(action);
		return $table.supr.prototype.contextActions.call(this, action, el);
	};

	$table.prototype.information_open = function(divId, element_id, pageName){
		var $this = this;
		pageName = pageName || 'all';
		$table.supr.prototype.information_open.call(this, divId, element_id, pageName);
		var base = '#' + divId + ' #div_view_edit > fieldset > table.ces > tbody',
			input_temp = '#' + divId + ' #gx_input #gx_input_temp', 
			output_temp = '#' + divId + ' #gx_output #gx_output_temp';
			
		//事件绑定
		
		//如果输入或输出部分选中的wz是原料的话，那么原料项应和物资项同
		$(input_temp + ' #wz_id').bind('change', function(){
			var input_wz_id = $(this).val(), o_wz = $(this).find('option:selected'), unit = o_wz.attr('unit'),
				gx_fl_id = parseInt($(base + ' #gx_fl_id').val()), yl_id = $(base + ' #wz_id').val();
			$(input_temp + ' #amount_post').html(unit);
			//如果是置换类工序，并且输入的材料是产品材料的话，那么应计算用量比例
			switch(gx_fl_id){
				case 1: //置换：所用的置换材料和产品的体积相关
					if(input_wz_id == yl_id){
						$(input_temp + ' #calc_method_id').val(2); //按体积计算
						$(input_temp + ' #amount').val(1.01); //1%损耗
					}
					break;
					
				case 2: //组合：多个零件组合成一个产品
					//输入好多个，输出一个
					//组合关系从产品描述里得到，保存在zuhe里
					break;
					
				case 3: //分解：和相应的组合工序对应
					//输入一个，输出好多个
					break;
					
				case 4: //涂裹：生成一个外壳，所用涂料数量和产品表面积相关
					break;
					
				case 5: //加工:一个进，一个出，没有数量上的变化
					break;
			}
		});
		$(output_temp + ' #wz_id').bind('change', function(){
			var o_wz = $(this).find('option:selected'), unit = o_wz.attr('unit');
			$(output_temp + ' #amount_post').html(unit);
		});
		
	};
}());
