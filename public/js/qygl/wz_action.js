// JavaScript Document
(function(){
	var DB = KF_GRID_ACTIONS.qygl;
	var tool = new kf_tool();
	
	DB.wz = function(grid){
		$table.supr.call(this, grid);
	};

	var $table = DB.wz;
	tool.extend($table, gc_grid_action);

	$table.prototype.contextActions = function(action, el){
// tool.debug(action);
		return $table.supr.prototype.contextActions.call(this, action, el);
	};
	
	$table.prototype.information_open = function(divId, element_id, pageName){
		pageName = pageName || 'all';
		$table.supr.prototype.information_open.call(this, divId, element_id, pageName);
		var hidefields = function(){
			var wz_fl_id = parseInt($('#' + divId + ' #wz_fl_id').val()), hide_fields = [], disp_fields = [];
			var zuhe = parseInt($('#' + divId + ' input[name="zuhe"]:checked').val());
		// $this->options['edit'] = array('wz_fl_id', 'name', 'unit_fl_id'=>array('label'=>'计量单位类型'), 
			// 'unit_id', 'default_price', 
			// 'min_kc', 'max_kc', 'pd_days', 'pd_last', 'jy_days', 'wh_days',
			// 'midu', 'tj', 'bmj', 'zuhe', 'wz_cp_zuhe', 'jszb_wz', 'gys_ids', 'kh_ids', 'pic', 'note', 'isactive'
		// );
			switch(wz_fl_id){
				case 0: //没有选中
					hide_fields = ['min_kc', 'max_kc', 'pd_days', 'pd_last', 'jy_days', 'wh_days', 
						'midu', 'tj', 'bmj', 'zuhe', 'wz_cp_zuhe', 'jszb_wz', 'defect_gx_wz', 'gx_wz_zl_detail', 'gys_ids', 'kh_ids', 'pic'];
					break;
				case 3: //产品
					disp_fields = ['min_kc', 'max_kc', 'pd_days', 'pd_last', 'jy_days', 'tj', 'bmj', 'zuhe', 'wz_cp_zuhe', 'jszb_wz', 'defect_gx_wz', 'gx_wz_zl_detail', 'kh_ids', 'pic'];
					hide_fields = ['midu', 'wh_days', 'gys_ids'];
					if(zuhe == 2)
						disp_fields.push('wz_cp_zuhe');
					else
						hide_fields.push('wz_cp_zuhe');
					break;
				case 2: //设备
				case 7: //维修用品
					disp_fields = ['min_kc', 'max_kc', 'pd_days', 'pd_last', 'jy_days', 'wh_days', 'gys_ids'];
					hide_fields = ['midu', 'tj', 'bmj', 'zuhe', 'wz_cp_zuhe', 'jszb_wz', 'defect_gx_wz', 'gx_wz_zl_detail', 'kh_ids'];
					break;
				case 4: //服务
					hide_fields = ['midu', 'tj', 'bmj', 'zuhe', 'wz_cp_zuhe', 'wh_days', 'kh_ids', 
						'jszb_wz', 'defect_gx_wz', 'gx_wz_zl_detail', 'min_kc', 'max_kc', 'remained', 'pd_days', 'pd_last', 'jy_days', 'wh_days', 'pic'];
					disp_fields = ['gys_ids'];
					break;
				case 8: //能源
				case 5: //劳保用品
				case 6: //办公用品
					hide_fields = ['midu', 'tj', 'bmj', 'zuhe', 'wz_cp_zuhe', 'jszb_wz', 'defect_gx_wz', 'gx_wz_zl_detail', 'wh_days', 'kh_ids'];
					disp_fields = ['min_kc', 'max_kc', 'pd_days', 'pd_last', 'jy_days', 'gys_ids'];
					break;
				case 1: //原材料
				case 9: // 其他
				default:
					hide_fields = ['tj', 'bmj', 'zuhe', 'wz_cp_zuhe', 'jszb_wz', 'defect_gx_wz', 'wh_days', 'gx_wz_zl_detail', 'kh_ids'];
					disp_fields = ['min_kc', 'max_kc', 'pd_days', 'pd_last', 'jy_days', 'midu', 'gys_ids'];
					break;
			}
			for(var i in disp_fields){
				$('#' + divId + ' #ces_tr_' + disp_fields[i]).show();
			}
			for(var i in hide_fields){
				$('#' + divId + ' #ces_tr_' + hide_fields[i]).hide();
			}
		};
		hidefields();
		//事件绑定
		//根据物资类型隐藏一些fields
		$('#' + divId + ' #wz_fl_id').unbind('change').bind('change', function(){
			hidefields();
		});
		//根据是否组合产品决定是否显示详细组合信息
		$('#' + divId + ' input[name="zuhe"]').unbind('change').bind('change', function(){
			var zuhe = parseInt($('#' + divId + ' input[name="zuhe"]:checked').val());
// tool.debug(zuhe);			
			if(zuhe == 2){
				$('#' + divId + ' #ces_tr_wz_cp_zuhe').show();
			}
			else
				$('#' + divId + ' #ces_tr_wz_cp_zuhe').hide();
		});
		
		//单位类型绑定单位
		var target = [
			{
				selector:'#' + divId + ' #unit_id', 
				type:'select', 
				field:'unit_fl_id', 
				url:'/jqgrid/jqgrid/oper/linkage/db/qygl/table/unit'
			},
		];
		tool.linkage({selector:'#' + divId + ' #unit_fl_id'}, target);
		
		$('#' + divId  + ' #unit_id').unbind('change').bind('change', function(){
			var unit = $(this).find("option:selected").text(), post = ['min_kc', 'max_kc', 'remained'];
			for(var i in post){
				$('#' + divId + ' #' + post[i] + '_post').html(unit);
			}
		});
	};
	
	$table.prototype.getGridsForInfo = function(divId){
		var grids = [
			{tab:'genzong', container:'genzong', table:'yw', params:{real_table:'yw', from:'wz'}},
		];
		return grids;
	};
	
	
}());
