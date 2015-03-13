function issue_buttonAction(key, gridId, options){
    var selectedRows = $(gridId).getGridParam('selarrrow');
    switch(key){
        case 'assign':
            $('<div id="issue_assign" title="Assign Issue Owner" />').load('/jqgrid/jqgrid/db/issue/table/issue/oper/assign', function(data){
                var dialog = $(this).html(data)
            		.dialog({
            		    width:600,
            		    height:400,
            			autoOpen: false,
            			title: 'Assign Issue Owner',
            			modal: true,
                        close:function(event, ui){
                            $('#issue_assign').remove();
                        },
            			buttons: {
            				Ok: function() {
            				    $.post('/jqgrid/jqgrid', {oper:'assign', db:'issue', table:'issue', element:selectedRows, assigned_to:$('#user').val()}, function(data){
                					dialog.dialog( "close" );
			                         $(gridId).trigger('reloadGrid');
                                });
            				},
            				Cancel: function(){
                                dialog.dialog("close");
                            }
            			}
            		});
            
            	dialog.dialog('open');
            });            
            break;
        case 'setstate':
            $('<div id="issue_setstate" title="Set Issue State" />').load('/jqgrid/jqgrid/db/issue/table/issue/oper/setstate', function(data){
                var dialog = $(this).html(data)
            		.dialog({
            		    width:600,
            		    height:400,
            			autoOpen: false,
            			title: 'Set Issue State',
            			modal: true,
                        close:function(event, ui){
                            $('#issue_setstate').remove();
                        },
            			buttons: {
            				Ok: function() {
            				    $.post('/jqgrid/jqgrid', {oper:'setstate', db:'issue', table:'issue', element:selectedRows, state:$('#state').val()}, function(data){
                					dialog.dialog( "close" );
			                         $(gridId).trigger('reloadGrid');
                                });
            				},
            				Cancel: function(){
                                dialog.dialog("close");
                            }
            			}
            		});
            
            	dialog.dialog('open');
            });            
            break;
    }
}