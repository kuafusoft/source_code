// JavaScript Document
(function(){
	var DB = KF_GRID_ACTIONS.qygl;
	var tool = new kf_tool();
	
	DB.hb = function(grid){
		$table.supr.call(this, grid);
	};

	var $table = DB.hb;
	tool.extend($table, gc_grid_action);
	$table.prototype.contextActions = function(action, el){
// tool.debug(action);
		return $table.supr.prototype.contextActions.call(this, action, el);
	};

	$table.prototype.information_open = function(divId, element_id, pageName){
		pageName = pageName || 'all';
		$table.supr.prototype.information_open.call(this, divId, element_id, pageName);
		var hideFields = function(cb){
			var hb_fl_id = parseInt($(cb).val());
			switch(hb_fl_id){
				case 1: //员工
					if(cb.checked){
						$('tr#ces_tr_hb_yg').show();
						$('tr#ces_tr_hb_skill').show();
					}
					else{
						$('tr#ces_tr_hb_yg').hide();
						$('tr#ces_tr_hb_skill').hide();
					}
					break;
				case 2: //客户
					if(cb.checked)
						$('tr#ces_tr_kh_wz_id').show();
					else
						$('tr#ces_tr_kh_wz_id').hide();
					break;
				case 3: //供应商
					if(cb.checked)
						$('tr#ces_tr_gys_wz_id').show();
					else
						$('tr#ces_tr_gys_wz_id').hide();
					break;
			}
		}
		//事件绑定
		//伙伴类型绑定可操作区域
		$("fieldset#fieldset_hb_fl_id input[name='hb_fl_id[]']").each(function(i){
			hideFields(this);
			$(this).unbind('change').bind('change', function(){
				hideFields(this);
			});
		});
	};
}());
