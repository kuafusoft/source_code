// JavaScript Document
(function(){
	var DB = KF_GRID_ACTIONS.xt;
	
	DB.zzvw_cycle_detail_stream = function(grid){
		$table.supr.call(this, grid);
	};
	var $parent = DB.zzvw_cycle_detail;
	var $table = DB.zzvw_cycle_detail_stream;
	var tool = new kf_tool();
	tool.extend($table, $parent);

	$table.prototype.ready = function(base){
		$table.supr.prototype.ready.call(this, base);
		var conditionSelector = this.getParams('conditionSelector');
		this.setLinkage('div' + conditionSelector, ['select#codec_stream_type_id']);
	};
	
	gc_grid_action.prototype.getPostDataForSubgrid = function(){
		var conditionSelector = this.getParams('conditionSelector');
		var cycle_id = $(conditionSelector).parent().parent().children().find("#id").attr('value');
// tool.debug('cycle_id:' + cycle_id);
		var postData = {};
		postData['parent'] = cycle_id;
		return postData;
	}
		
	$table.prototype.addcase = function(params){
		$this = this;
// tool.debug(params);
		var url = '/jqgrid/jqgrid/db/' + params.db + '/table/' + params.table + '/oper/addcase/parent/' + params.parent;	
		var div_id = 'div_addcase_stream_req';
		var dialogParams = {
			div_id: div_id, 
			width: 600, 
			height: 200, 
			title: 'Select Case Add Type',
			close: function(){$(this).remove();},
			buttons: {
				Ok: function(){
					var type = tool.getAllInput('div#' + div_id)['data']['case_add_type'];
					switch(type){
						case 'stream_list':
							$this.addCaseForCodecCycle(params);
							$(this).dialog( "close" );
							break;
						case 'cycle_list':
							$this.addCaseFromOtherCycle(params);
							$(this).dialog( "close" );
							break;
					}
				},
				Cancel:function(event, ui){
					$(this).remove();
				}
			}
		};
		tool.actionDialog(dialogParams, url);
	}
	
	$table.prototype.gridComplete = function(){
	};
	
	$table.prototype.addCaseForCodecCycle = function(params){
// tool.debug(params);
		var db = params.db;
		// var table = 'zzvw';
		var table = params.table;//'zzvw_testcase_ver';	
		var target_table = 'codec_stream';
		var div_id = 'div_case_add_for_codec';
		
		var dialog_params = {
			div_id: 'div_case_add_for_codec',
			title: 'Add Case From Stream List And TrickModes To Current Cycle',
			height:600,
			width: 1024,
			modal: true,
			autoOpen:false,
			close: function(){$(this).remove();},
			open: function(data){
				var url = '/jqgrid/index/db/' + db + '/table/' + target_table + '/container/' + div_id + '/parent/' + params.parent;
				$.get(url, function(datas){	
					if(tool.handleRequestFail(datas))return;
					$("#" + div_id).append(datas);
				});
			}
		};
		
		var gridSelector_t = '#' + dialog_params.div_id + '_' + db + '_' + target_table + '_list';
		var detailSelector = params.tabSelector + '_' + db + '_' + table + '_list';
// tool.debug(gridSelector_t);		
		var fun_addcase = function(replaced){
			var inputs = tool.getAllInput('#' + dialog_params.div_id);
			if (inputs['passed'].length == 0){
// tool.debug(gridSelector_t);	
				var selectedRows = $(gridSelector_t).getGridParam('selarrrow');
				var postData = {
					db: db, 
					table: table, 
					oper: 'addCaseForCodec', 
					element: JSON.stringify(selectedRows), 
					testcase_id: JSON.stringify(inputs['data']['testcase_id']),
					test_env_id: inputs['data']['test_env_id'],
					parent: params.parent,
					replaced: replaced
				};
			
				$.post('/jqgrid/jqgrid', postData, function(data){
					if(tool.handleRequestFail(data))return;
					if(data == 'done'){
						var grid = grid_factory.get(db, table, {container: params.refeshTab, parent: params.parent});
						var filters = JSON.stringify({groupOp:'AND', rules:[{"field":"cycle_id","op":"eq","data":params.parent}]});
						// detail_grid.setParams($.extend(true, params, {p_id:rowId, parent:rowId, filters:filters}));
						grid.indexInDiv({filters:filters});
						alert("Add Successfully");
					}
					else
						alert("Add Error");
				});
			}
			else{
				alert(inputs['tips'].join('\n'));
			}
		};
		dialog_params['buttons'] = {
			"Add & Replace Stream-Action": function() {
				fun_addcase(1);
				//$(this).dialog( "close" );
			},
			"Add & NOT-Replace Stream-Action": function() {
				fun_addcase(0);
				//$(this).dialog( "close" );
			},
			Cancel: function() {
				$(this).dialog( "close" );
			}
		};
		var url = "/jqgrid/jqgrid/db/" + db + "/table/" + table + "/oper/addCaseForCodec/parent/" + params.parent;
		var dialog_params = $.extend(true, dialog_params, {html_type:'url', text:url});	
		return tool.actionDialog(dialog_params, url);	
	};
	
	$table.prototype.setLinkage = function(divSelector, sources, srcValue){
// tool.debug('srcValue');
// tool.debug(srcValue);
		var $this = this;
		var db = $this.getParams('db'), table = $this.getParams('table'), parent = $this.getParams('parent');
		sources = sources || ['select#os_id', 'select#board_type_id', 'select#chip_id'];
		var os_chip_board_type = {os_id:divSelector + ' select#os_id', chip_id:divSelector + ' select#chip_id', board_type_id:divSelector + ' select#board_type_id'};
		for(var i in sources){
			switch(sources[i]){
				case 'select#os_id':
					var target = [{selector:divSelector + ' select#testcase_type_id', type:'select', field:'os_ids', cond:'REGEXP', url:'/jqgrid/jqgrid/oper/linkage/db/' + db + '/table/testcase_type'},
						{selector:divSelector + ' select#prj_id', type:'select', field:'os_id', url:'/jqgrid/jqgrid/oper/linkage/db/' + db + '/table/zzvw_prj'},
						{selector:divSelector + ' select#compiler_id', type:'select', field:'os_ids', url:'/jqgrid/jqgrid/oper/linkage/db/' + db + '/table/compiler'},
						{selector:divSelector + ' select#chip_id', type:'select', field:'os_id', url:'/jqgrid/jqgrid/oper/getchip/db/' + db + '/table/zzvw_prj'},
						{selector:divSelector + ' select#board_type_id', type:'select', field:'os_id', url:'/jqgrid/jqgrid/oper/getboardtype/db/' + db + '/table/zzvw_prj'}];
					var srcDiv = {selector:divSelector + ' select#os_id'};
					var params = {selector:os_chip_board_type};
					if(srcValue != undefined && srcValue.os_id != undefined)
						var value = srcValue.os_id;
					break;
				case 'select#board_type_id':
					var target = [{selector:divSelector + ' select#prj_id', type:'select', field:'board_type_id', url:'/jqgrid/jqgrid/oper/linkage/db/' + db + '/table/zzvw_prj'},
						{selector:divSelector + ' select#chip_id', type:'select', field:'board_type_id', url:'/jqgrid/jqgrid/oper/getchip/db/' + db + '/table/zzvw_prj'},
						{selector:divSelector + ' select#os_id', type:'select', field:'board_type_id', url:'/jqgrid/jqgrid/oper/getos/db/' + db + '/table/zzvw_prj'}];
					var srcDiv = {selector:divSelector + ' select#board_type_id'};
					var params = {selector:os_chip_board_type};
					if(srcValue != undefined && srcValue.board_type_id != undefined)
						var value = srcValue.board_type_id;
					break;
				case 'select#chip_id':
					var target = [{selector:divSelector + ' select#prj_id', type:'select', field:'chip_id', url:'/jqgrid/jqgrid/oper/linkage/db/' + db + '/table/zzvw_prj'},
						{selector:divSelector + ' select#board_type_id', type:'select', field:'chip_id', url:'/jqgrid/jqgrid/oper/getboardtype/db/' + db + '/table/zzvw_prj'},
						{selector:divSelector + ' select#os_id', type:'select', field:'chip_id', url:'/jqgrid/jqgrid/oper/getos/db/' + db + '/table/zzvw_prj'}];
					var srcDiv = {selector:divSelector + ' select#chip_id'};
					var params = {selector:os_chip_board_type};
					if(srcValue != undefined && srcValue.chip_id != undefined)
						var value = srcValue.chip_id;
					break;
				case 'select#prj_id':	
					var target = [{selector:divSelector + ' select#testcase_type_id', type:'select', url:'/jqgrid/jqgrid/oper/getCycleType/db/' + db + '/table/' + table + '/parent/' + parent},
						{selector:divSelector + ' select#cycle_id', type:'select', url:'/jqgrid/jqgrid/oper/getCycle/db/' + db + '/table/' + table + '/parent/' + parent}];
					var srcDiv = {selector:divSelector + ' select#prj_id'};
					var params = {};
					if(srcValue != undefined && srcValue.prj_id != undefined){
						var value = srcValue.prj_id;
						target = [{selector:divSelector + ' select#testcase_type_id', type:'select', url:'/jqgrid/jqgrid/oper/getCycleType/db/' + db + '/table/' + table + '/parent/' + parent},
							{selector:divSelector + ' select#cycle_id', type:'select', url:'/jqgrid/jqgrid/oper/getCycle/db/' + db + '/table/' + table + '/parent/' + parent},
							{selector:divSelector + ' select#creater_id', type:'select', url:'/jqgrid/jqgrid/oper/getCreater/db/' + db + '/table/' + table + '/parent/' + parent},
							{selector:divSelector + ' select#prj_id', type:'select', url:'/jqgrid/jqgrid/oper/getPrj/db/' + db + '/table/' + table + '/parent/' + parent}
						];
					}
					break;
				case 'select#cycle_id':
					//var module_priority_testor = {testcase_module_id:divSelector + ' select#testcase_module', testcase_priority_id:divSelector + ' select#testcase_priority', tester_id:divSelector + ' select#tester_id'};
					var target = [{selector:divSelector + ' select#codec_stream_format_id', type:'select', url:'/jqgrid/jqgrid/oper/getModule/db/' + db + '/table/' + table + '/parent/' + parent},
						{selector:divSelector + ' select#tester_id', type:'select', url:'/jqgrid/jqgrid/oper/getTesters/db/' + db + '/table/zzvw_cycle'},
						{selector:divSelector + ' select#testcase_priority_id', type:'select', url:'/jqgrid/jqgrid/oper/getPriority/db/' + db + '/table/' + table + '/parent/' + parent},
						{selector:divSelector + ' select#codec_stream_type_id', type:'select', url:'/jqgrid/jqgrid/oper/getStreamType/db/' + db + '/table/' + table + '/parent/' + parent},
					];
					var srcDiv = {selector:divSelector + ' select#cycle_id'};
					var params = {};
					if(srcValue != undefined && srcValue.cycle_id != undefined)
						var value = srcValue.cycle_id;
					break;
				case 'select#codec_stream_type_id':
					var target = [{selector:divSelector + ' select#codec_stream_format_id', type:'select', field:'codec_stream_type_id', url:'/jqgrid/jqgrid/oper/getStreamFormat/db/xt/table/' + table + '/parent/' + parent}];
					var srcDiv = {selector:divSelector + ' select#codec_stream_type_id'};
					var params = {};
					if(srcValue != undefined && srcValue.codec_stream_type_id != undefined)
						var value = srcValue.codec_stream_type_id;
					break;
				case 'select#creater_id':
					var target = [{selector:divSelector + ' select#cycle_id', type:'select', field:'creater_id', url:'/jqgrid/jqgrid/oper/getCreaterCycle/db/' + db + '/table/' + table + '/parent/' + parent}];
					var srcDiv = {selector:divSelector + ' select#creater_id'};
					var params = {selector:{prj_id:divSelector + ' select#prj_id'}};
					if(srcValue != undefined && srcValue.creater_id != undefined)
						var value = srcValue.creater_id;
					break;	
				case 'select#testcase_module_id':
					var target = [{selector:divSelector + ' select#testcase_testpoint_id', type:'select', field:'testcase_module_id', url:'/jqgrid/jqgrid/oper/linkage/db/xt/table/testcase_testpoint'}];
					var srcDiv = {selector:divSelector + ' select#testcase_module_id'};
					var params = {};
					if(srcValue != undefined && srcValue.testcase_module_id != undefined)
						var value = srcValue.testcase_module_id;
					break;
				case 'select#testcase_type_id':
					var target = [{selector:divSelector + ' select#testcase_module_id', type:'select', field:'testcase_type_ids', url:'/jqgrid/jqgrid/oper/linkage/db/' + db + '/table/testcase_module'}];
					var srcDiv = {selector:divSelector + ' select#testcase_type_id'};
					var params = {};
					if(srcValue != undefined && srcValue.testcase_type_id != undefined){
						var value = srcValue.testcase_type_id;
						if(srcValue.field == 'testcase_type_id')
							target = [{selector:divSelector + ' select#testcase_module_id', type:'select', field:'testcase_type_id', url:'/jqgrid/jqgrid/oper/getCaseModule/db/' + db + '/table/' + table}];
					}
					break;
			}
// tool.debug('srcDiv:' + sources[i]);
			tool.linkage(srcDiv, target, params);
// tool.debug('os_id:' + value);
			if(value != undefined){
				$this.linkage(srcDiv, target, value, params);
				value = undefined;
			}
		}
	};
}());
