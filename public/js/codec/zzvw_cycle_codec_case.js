function edit_cycle_detail_inform(event){
	$('#div_test_result [editable="editable"]').each(function(i){$(this).attr('disabled', false);});
	$('#div_test_result #btn_edit').hide();
	$('#div_test_result #btn_save').show();
	$('#div_test_result #btn_cancel').show();
}

function save_cycle_detail_inform(event){
alert("save");	
	$('#div_test_result [editable="editable"]').each(function(i){$(this).attr('original_value', $(this).val()).attr('disabled', true);});
	$('#div_test_result #btn_save').hide();
	$('#div_test_result #btn_edit').show();
	$('#div_test_result #btn_cancel').hide();
	var inputs = getAllInput('div_test_result');
debug(inputs);	
	$.post('/jqgrid/jqgrid/db/codec/table/zzvw_cycle_codec_case/oper/edit', inputs, function(data, textStatus){
debug(data);
debug(textStatus);
	});
}

function cancel_cycle_detail_inform(event){
	$('#div_test_result [editable="editable"]').each(function(i){$(this).val($(this).attr('original_value')).attr('disabled', true);});
	$('#div_test_result #btn_cancel').hide();
	$('#div_test_result #btn_edit').show();
	$('#div_test_result #btn_save').hide();
}
