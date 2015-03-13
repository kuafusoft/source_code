// JavaScript Document
(function(){
	var DB = KF_GRID_ACTIONS.xt;
	
	DB.zzvw_prj = function(grid){
		$table.supr.call(this, grid);
	};

	var $table = DB.zzvw_prj;
	var tool = new kf_tool();
	tool.extend($table, gc_grid_action);

	$table.prototype.linkage = function(div){
		var generatePrjName = function(event){
			var div = event.data.div;
			var os = $(div + " #os_id").find("option:selected").text(); 
			var board_type = $(div + " #board_type_id").find("option:selected").text(); 
			var chip = $(div + " #chip_id").find("option:selected").text(); 
			$(div + " #name").val(chip + '-' + board_type +'-' + os);
			// check if the name is unique
			tool.checkElement($(div + ' #name'), {db:'xt', table:'zzvw_prj'});
		};
		$(div + " #os_id").unbind('change', $table.prototype.generatePrjName).bind('change', {div:div}, generatePrjName);
		$(div + " #board_type_id").unbind('change', $table.prototype.generatePrjName).bind('change', {div:div}, generatePrjName);
		$(div + " #chip_id").unbind('change', $table.prototype.generatePrjName).bind('change', {div:div}, generatePrjName);

		//os和chip_type联动
		var os_board_type = {os_id:div + ' select#os_id', board_type_id:div + ' select#board_type_id'};
		var target = [{selector:div + ' select#chip_type_id', type:'select', field:'os_ids', cond:'REGEXP', url:'/jqgrid/jqgrid/oper/linkage/db/xt/table/chip_type'}];
		tool.linkage({selector:div + ' select#os_id'}, target, os_board_type);
		// chip_type和board_type、chip联动
		target = [{selector:div + ' select#chip_id', type:'select', field:'chip_type_id', url:'/jqgrid/jqgrid/oper/linkage/db/xt/table/chip'},
			{selector:div + ' select#board_type_id', type:'select', field:'chip_type_id', url:'/jqgrid/jqgrid/oper/linkage/db/xt/table/board_type'}];
		tool.linkage({selector:div + ' select#chip_type_id'}, target);
	};
	
	$table.prototype.information_open = function(divId, element_id, pageName){
		$table.supr.prototype.information_open.call(this, divId, element_id, pageName);
		var div = "#information_tabs_xt_zzvw_prj_" + element_id + " #div_view_edit";
		$table.prototype.linkage(div);
	};

	$table.prototype.buttonActions = function(key, options){
		var ret = true;
		var db = 'xt', table = 'zzvw_prj';
		var gridId = this.getParams('gridSelector');
		var selectedRows = $(gridId).getGridParam('selarrrow');
		switch(key){
			case 'complete':
			case 'uncomplete':
				if (tool.checkSelectedRows(selectedRows, 1)){
					ret = false;
				}
				break;
				
			case 'export':
				if (tool.checkSelectedRows(selectedRows, 1)){
					var dialog_params = {
						div_id:'export_options',
						width:800,
						height:600,
						title:'Export',
						open:function(){
							$('#export_options input:radio[name="export_type"]').change(function(event){
								if ($(this).is(':checked')){
									switch($(this).val()){
										case 'last_result':
											$('#export_options #div_for_last_result_report_options').show();
											break;
										default:
											$('#export_options #div_for_last_result_report_options').hide();
											break;
									}
								}
							});
							$('#export_options input:[name="include_coverage[]"]').change(function(event){
								if ($(this).is(':checked'))
									$('#export_options #div_coverage').show();
								else
									$('#export_options #div_coverage').hide();
							});
							$('#export_options input:[date="date"]').each(function(i){
								tool.datePick(this);
							});
						}
					};
					var checkRel = function(data){
						var passed = true;
						var tips = [];
						if (data.export_type == 'last_result'){
							passed = data.rel_ids.length > 0;
							if (!passed)
								tips.push('Please select Release first');
							if (data.include_coverage[0] == '1'){
								if (!data.coverage_begin){
									passed = false;
									tips.push("Please select coverage begin date");
								}
							}
							
						}
						if (tips.length)
							alert(tips.join("\n"));//"Please select Release first");
						return passed;
					};
					var url = '/jqgrid/jqgrid/db/' + db + '/table/' + table + '/oper/export/element/' + selectedRows;
					tool.actionDialog(dialog_params, url, checkRel, function(data){
						location.href = "/download.php?filename=" + encodeURIComponent(data) + "&remove=1";
					});
				}
				break;
					
			default:
				$table.supr.prototype.buttonActions.call(this, key, options);
		}
	};
})();
