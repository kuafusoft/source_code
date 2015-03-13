// JavaScript Document
(function(){
	var DB = KF_GRID_ACTIONS.xt;
	
	DB.tc_ver = function(grid){
		$table.supr.call(this, grid);
	};

	var $table = DB.tc_ver;
	var tool = new kf_tool();
	tool.extend($table, gc_grid_action);
	
	var refreshTabs = function(grid_action, tc_id, ver_id){
		grid_action.updateInformationPage(ver_id, tc_id, 'view_edit');
		var grid = grid_factory.get('xt', 'testcase_ver', {container:'edit_history_' + tc_id});
		var gridId = grid.getParams('gridSelector');
		$(gridId).trigger('reloadGrid');
	};
	
	$table.prototype._ask2review = function(tc_id, ver_ids){
		var $this = this, div_id = 'div_ask2review_' + tc_id;
		var url = '/jqgrid/jqgrid/oper/ask2review/db/xt/table/testcase_ver/element/' + tc_id + '/ver/' + ver_ids;
		var checkReviewer = function(data){
			var reviewers = data['reviewers'].length;
			if (!(reviewers)){
				alert("Please select at least one reviewer");
			}
			return (reviewers);
		};
		
		var dialog_params = {
			div_id: div_id,
			title: 'Ask to Review Testcases',
			width: 800,
			height: 500,
			open:function(){
				XT.datePick("#" + div_id + " #deadline");
				$("div#reviewer_groups input[name='reviewer_groups']").each(function(i){
					$(this).unbind('change').bind('change', function(){
						var groups = [];
						$("div#reviewer_groups input[name='reviewer_groups']").each(function(){if (this.checked) groups.push(this.value);});
						$.post('/useradmin/getuserlist', {groups_id:groups, role_id:4, isactive:true}, function(data){
							tool.generateCheckbox("div#reviewers", 'reviewers', data);
						}, 'json');
						
					});
				});
			},
		};
		
		tool.actionDialog(dialog_params, url, checkReviewer, function(){refreshTabs($this, tc_id, ver_ids);});
	};
	
	$table.prototype._publish = function(tc_id, ver_id){
		var url = '/jqgrid/jqgrid/oper/publish/db/xt/table/testcase_ver/element/' + tc_id + '/ver/' + ver_id;
		var checkNote = function(data){
			return (data['note'] != '');
		};
		var params = {
			param: param,
			height: 400,
			width: 600,
			title: 'Publish',
			div_id: 'testcase_publish',
			open: function(){
				$('#note').focus();
			}
		}

		tool.actionDialog(params, url, checkNote, function(){refreshTabs($this, tc_id, ver_id);});
	};
	
	$table.prototype._abort = function(tc_id, ver_id){
		//询问是否确认要Abort当前Version
		var $this = this;
		var url = '/jqgrid/jqgrid/db/xt/table/testcase/oper/abort/element/' + tc_id + '/ver/' + ver_id;
		var dialog_params = {
			div_id:'div_abort_' + tc_id,
			width:400,
			height:300,
			fun_complete: function(data){
				if(data > 0){
					refreshTabs($this, tc_id, data);
				}
				else{ //没有Version了
					window.close();
				}
			},
			buttons:{
				Yes:function(){
					tool.defaultForActionDialog(dialog_params, true);
				},
				No: function(){
					$(this).dialog('close');
				}
			}
		};
		tool.actionDialog(dialog_params, url);
	};
}());
