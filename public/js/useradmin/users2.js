// JavaScript Document
var useradmin = useradmin || {db:'useradmin'};
useradmin.users = useradmin.users || {table:'users'};

useradmin.users.newElement = function(gridId, db, tb, parentId){
	var url = '/jqgrid/jqgrid/db/' + db + '/table/' + tb + '/oper/newElement/id/0/parentId/' + parentId;
	var params = XT.getDefaultParamsForNewElement(gridId, db, tb, parentId);
	params['width'] = 800;
	params['height'] = 400;
	params['open'] = function(){
		$('input#username').unbind('keyup').bind('keyup', function(event){
		XT.debug(event);
			$('input#email').val($('input#username').val() + '@freescale.com');
		});
	};
	return XT.actionDialog(params, url);
};

useradmin.users.buttonActions = function(key, gridId, options){
    var db = 'useradmin', table = 'users';
	var ret = true;
	var selectedRows = $(gridId).getGridParam('selarrrow');
	if (key == 'columns'){
		return false;
	}
	if (XT.checkSelectedRows(selectedRows, 1))
		return false;
	return true;
};


