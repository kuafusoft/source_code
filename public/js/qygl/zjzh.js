// JavaScript Document
function zjzh_construct(gridId, jsonData){
    jsonData.onContextMenuShow = function(el){
        var zj_fl = el.children("td[aria-describedby*='0_0zjzh0_0zj_fl_id']").html();
//debug(zj_fl);
        el.hideContextMenuItems(['tx']);
        if (zj_fl == '银行承兑' || zj_fl == '银行汇票')
            el.showContextMenuItems(['tx']);
    };

    jsonData.contextMenu = function(action, el){
        var handled = true;
        var rowId = $(el).attr('id');
        switch(action){
            case 'transfer':
                var dialog_params = {div_id:'zjzh_transfer', height:500};
                var url = '/jqgrid/jqgrid/db/qygl/table/zjzh/oper/transfer/element/' + rowId;
                             
                actionDialog(dialog_params, url, transfer_validation);
                break;

            case 'tx':
                $('<div id="zjzh_tx" title="tx" />').load('/jqgrid/jqgrid/db/qygl/table/zjzh/oper/tx/element/' + rowId, function(data){
                    var dialog = $(this).html(data)
                		.dialog({
                		    width:500,
                		    height:550,
                			autoOpen: false,
                			title: '承兑贴息',
                			modal: true,
                            close:function(event, ui){
                                $('#zjzh_tx').remove();
                            },
                			buttons: {
                				Ok: function() {
                				    // check the input first
                				    var transfer_amount = Number($("#amount").val());
                				    var remained = Number($("#remained").html());
                				    var cost = Number($('#cost').html());
debug(transfer_amount);
debug(remained);
                				    if (transfer_amount < 0){
                                        $("#error_warning").html("转账金额只能是正数");
                                    }
                                    else if (transfer_amount + cost > remained){
                                        $("#error_warning").html("转账金额及费用不能大于转出帐户余额");
                                    }
                                    else{
                    				    $.post('/jqgrid/jqgrid', {oper:'tx', db:'qygl', table:'zjzh', element:rowId, input_account:$("#input").val(), amount:$('#amount').val()}, function(data){
                        					dialog.dialog( "close" );
        			                         $(gridId).trigger('reloadGrid');
                                        });
                                    }
                				},
                				Cancel: function(){
                                    dialog.dialog("close");
                                }
                			}
                		});
                
                	dialog.dialog('open');
                });
                break;
            default:
                handled = false;
        }          
        return handled;
    }
    return jsonData;
}                
                
function transfer_validation(params){
    var validated = true;
    var transfer_amount = Number($("#amount").val());
    var remained = Number($("#remained").html());
    var cost = Number($('#cost').html());
//debug(transfer_amount);
//debug(remained);
//debug(cost);
    if (transfer_amount < 0){
        $("#error_warning").html("转账金额只能是正数");
        validated = false;
    }
    else if (transfer_amount + cost > remained){
        $("#error_warning").html("转账金额及费用不能大于转出帐户余额");
        validated = false;
    }
    return validated;
}                