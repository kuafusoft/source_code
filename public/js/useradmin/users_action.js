(function(){
	var DB = KF_GRID_ACTIONS.useradmin;
	
	DB.users = function(params){
		$table.supr.call(this, params);
	};

	var $table = DB.users;
	var tool = new kf_tool();
	tool.extend($table, gc_grid_action);

	$table.prototype.bindEventsForInfo = function(divId, rowId){
		var $this = this;
		$('#' + divId + ' #username').attr('disabled', rowId != 0);
		$('#' + divId + ' #company_id').unbind('change').bind('change', function(event){
// tool.debug(event);
			$.post('/jqgrid/jqgrid/db/useradmin/table/users/oper/getemailpostfix', {id:$('#' + divId + ' #company_id').val()}, function(data){
// tool.debug(data);
				if(data.length > 0){
					var email = $('#' + divId + ' #email').val();
					var comp = email.split('@');
// tool.debug(comp);					
					if(comp.length == 2){
						$('#' + divId + ' #email').val(comp[0] + '@' + data);
					}
					else{
						$('#' + divId + ' #email').val('@' + data);
					}
				}
			})
		});
		//将username和email联动
		$('#' + divId + ' #username').unbind('keyup').bind('keyup', function(event){
			var email = $('#' + divId + ' #email').val();
			var comp = email.split('@');
			if(comp.length == 2){
				$('#' + divId + ' #email').val($('#' + divId + ' #username').val() + '@' + comp[1]);
			}
		});
	};

	$table.prototype.buttonActions = function(action, options){
		var db = this.getParams('db'), table = this.getParams('table');
		var $this = this;
		var gridId = this.getParams('gridSelector');
		var selectedRows = $(gridId).getGridParam('selarrrow');
		var element = JSON.stringify(selectedRows);
		switch(action){
			case 'resetpassword':
				if (tool.checkSelectedRows(selectedRows, 1)){
					var url = '/jqgrid/jqgrid/db/' + db + '/table/' + table + '/oper/resetpassword';//'/element/' + selectedRows;
					tool.actionDialog({div_id:'resetpassword_dialog', width:400, height:300, title:'Reset Password', postData:{element:element}}, url, undefined, function(){
						tool.noticeDialog("The password has been reset to 123456", 'Success');
					});
				};
				break;
			case 'lock':
				if (tool.checkSelectedRows(selectedRows, 1)){
					var url = '/jqgrid/jqgrid/db/' + db + '/table/' + table + '/oper/lock';//'/element/' + selectedRows;
					tool.actionDialog({div_id:'lockuser_dialog', width:400, height:300, title:'Reset Password', postData:{element:element}}, url, undefined, function(){
						$(gridId).trigger('reloadGrid');
						tool.noticeDialog("The users has been locked", 'Success');
					});
				};
				break;
			case 'unlock':
				if (tool.checkSelectedRows(selectedRows, 1)){
					var url = '/jqgrid/jqgrid/db/' + db + '/table/' + table + '/oper/unlock';//'/element/' + selectedRows;
					tool.actionDialog({div_id:'unlockuser_dialog', width:400, height:300, title:'Reset Password', postData:{element:element}}, url, undefined, function(){
						$(gridId).trigger('reloadGrid');
						tool.noticeDialog("The users has been unlocked", 'Success');
					});
				};
				break;
				
			default:
				return $table.supr.prototype.buttonActions.call(this, action, options);
		}
	};
}());
