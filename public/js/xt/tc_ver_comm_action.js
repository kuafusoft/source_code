// JavaScript Document
function tc_ver_comm_action(grid_action){
	this.grid_action = grid_action;

	this.refreshTabs = function(tc_id, ver_id){
		this.grid_action.updateInformationPage(ver_id, tc_id, 'view_edit');
		
		var grid = grid_factory.get('xt', 'testcase_ver', {container:'edit_history_' + tc_id});
		var gridId = grid.getParams('gridSelector');
		$(gridId).trigger('reloadGrid');
	};

	this._ask2review = function(tc_id, ver_ids){
		var $this = this, div_id = 'div_ask2review_' + tc_id;
		var url = '/jqgrid/jqgrid/oper/ask2review/db/xt/table/testcase_ver/element/' + tc_id + '/ver/' + ver_ids;
		var checkReviewer = function(data){
			var reviewers = data['data']['reviewers'].length;
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
			postData:{element:JSON.stringify(ver_ids)},
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
		
		tool.actionDialog(dialog_params, url, checkReviewer, function(){$this.refreshTabs(tc_id, ver_ids);});
	};

	//from: testcase, 则ver_id存放prj_id
	this._publish = function(tc_id, ver_id, from){
		from = from || 'testcase_ver';
		var $this = this;
		var url = '/jqgrid/jqgrid/oper/publish/db/xt/table/testcase_ver/ver/' + ver_id + '/from/' + from;
		var checkNote = function(data){
			return (data['note'] != '');
		};
		var complete = function(){
			if (from == 'testcase_ver')
				$this.refreshTabs(tc_id, ver_id);
			else{
				var gridId = $this.grid_action.getParams('gridSelector');
				$(gridId).trigger('reloadGrid');
			}
		}
		var params = {
			height: 400,
			width: 600,
			title: 'Publish',
			div_id: 'div_testcase_publish',
			postData:{element:JSON.stringify(tc_id)},
			open: function(){
				$('#note').focus();
			}
		}
		tool.actionDialog(params, url, checkNote, complete);
	};

	this._abort = function(tc_id, ver_id){
tool.debug("abort :");
	tool.debug([tc_id, ver_id]);	
		// ver_id = JSON.stringify(ver_id);
tool.debug(ver_id);		
		//询问是否确认要Abort当前Version
		var $this = this;
		var url = '/jqgrid/jqgrid/db/xt/table/testcase/oper/abort/element/' + tc_id + '/ver/' + JSON.stringify(ver_id);
		var dialog_params = {
			div_id:'div_abort_' + tc_id,
			width:400,
			height:300,
			postData:{ver:JSON.stringify(ver_id)},
			fun_complete: function(data){
tool.debug(data);
				if(data > 0){
					$this.refreshTabs(tc_id, data);
				}
				else{ //没有Version了
					// window.close();
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
	
	this._coverSRS = function(testcase_id){
		var url = '/jqgrid/jqgrid/db/xt/table/testcase/oper/coverSRS/element/' + testcase_id;
		var checkSRS = function(data){
			return (data['prj_ids'].length > 0 && data['srs_node_ids'].length > 0);
		};
		var complete = function(data, params){
		};
		tool.actionDialog({width:800, height:600, 'title':'Cover SRS', 'div_id':'div_cover_srs'}, url, checkSRS, complete);
	};
	
}