// JavaScript Document
function codec_stream_construct(gridId, jsonData){
    jsonData.contextMenu = function(action, el){
        var handled = true;
        var rowId = $(el).attr('id');
        switch(action){
            case 'ns_trickmode':
                var dialog_params = {div_id:'stream_ns', title:'Manage the Not-Supported Trickmode', height:500, width:800, open:f_open};
                var url = '/jqgrid/jqgrid/db/codec/table/stream/oper/ns_trickmode/element/' + rowId;
                             
                actionDialog(dialog_params, url);
                break;

            case 'information':
                var dialog_params = {div_id:'stream_information', title:'Stream Information', height:500, width:800, open:f_open};
                var url = '/jqgrid/jqgrid/db/codec/table/stream/oper/information/element/' + rowId;
                             
                actionDialog(dialog_params, url);
                break;

            default:
                handled = false;
        }          
        return handled;
    }
    return jsonData;
}                
                
function displayNSList(data, classes, rowProp){
	var cols = 4;
	var cells = [];
	var currentCol = 0;
	classes = classes || ['added_cell'];
debug(data);
	$.each(data, function(i, cell){
		if ($('#table_ns_list td#' + cell['id']).length == 0)
			cells.push({label:cell['name'], id:cell['id'], classes:classes, onclick:'selectCell(this)'});
		currentCol ++;
		if (currentCol == cols){
			$('#table_ns_list').append(displayRow(cells, rowProp));
			cells = [];
			currentCol = 0;
		}
	});
	if (currentCol != 0 && currentCol != 4)
		$('#table_ns_list').append(displayRow(cells, rowProp));
}

function open_stream_inform(event, ui, url){
	var id = $('#information_tabs #element_id').val();
	inform_open(event, ui, url);
	$.post('jqgrid/jqgrid/db/codec/table/stream/oper/ns_trickmode/element/' + id, function(data){
		//fill in the not supported trickmode list
		displayNSList(data, ['added_cell']);
	}, 'json');

	// get the history
	var db = 'codec', table = 'zzvw_cycle_codec_case';
	var options = {
		config:{
			url:'jqgrid/jqgrid',
			data:{oper:'getGridOptions', table:table, db:db},
			getConfig:true
		},
		gridOptions:{
			url:'jqgrid/list',
			postData:{table:table, db:db, filters:'{"groupOp":"AND","rules":[{"field":"codec0_0zzvw_cycle_codec_case0_0stream_id","op":"eq","data":' + id + '}]}'},
			editurl:'jqgrid/jqgrid/db/' + db + '/table/' + table
		},
		editOptions:{
		
		}
	};
	grid_init('#table_stream_history', '#div_stream_pager', options);
	
	var select_cell_map = {table:'table_ns_list', op:'operation_with_selected', 
		none:{selector:'', op:'remove'}, all:{selector:'', op:'add'}, 'new':{selector:'.new_cell'}, removed:{selector:'.removed_cell'}
		};
	var op_with_selected_map = {table:'table_ns_list', op:'submit_to', remove:{addClass:'removed_cell'}, unremove:{removeClass:'removed_cell'},
		submit:{id:'submit_to', selector:'td.new_cell,td.removed_cell'}};
	$('#select_cells').bind('change', select_cell_map, select_cells_change);
	$('#operation_with_selected').bind('change', op_with_selected_map, operation_with_selected_change);

    $('#add_ns_trickmode').click(function(){
        var player_id = $('#player_id').val();
        var trickmode_id = $('#trickmode_id').val();
        var players;
        var trickmodes;
        var player_trickmode;
        var player_trickmode_id;
		var cells = [];

        if (player_id == undefined || player_id == '0' || player_id == ''){
            players = $('#player_id').children('option');
        }
        else
            players = $('#player_id').children('option:selected');
            
        if (trickmode_id == undefined || trickmode_id == '0' || trickmode_id == '')
            trickmodes = $('#trickmode_id').children('option');
        else
            trickmodes = $('#trickmode_id').children('option:selected');
//debug(players);
        players.each(function(){
            var player = $(this);
            if (player.val() == '')
                return;
            trickmodes.each(function(){
                if ($(this).val() == '')
                    return;
				cells.push({id:player.val() + '_' + $(this).val(), name:player.text() + ':' + $(this).text()});
			});
		});
		displayNSList(cells, ['new_cell']);
		updateSubmitStatus({table:'table_ns_list', submit:{id:'submit_to', selector:'td.new_cell,td.removed_cell'}});
    });    
	$('#submit_to').click(function(){
		$(this).attr('disabled', true);
		var newCells = [], removedCells = [];
		$.each($('#table_ns_list td'), function(i, n){
			var id = $(n).attr('id');
			if ($(n).hasClass('new_cell'))
				newCells.push(id);
			if ($(n).hasClass('removed_cell'))
				removedCells.push(id);
		});
		if (newCells.length > 0 || removedCells.length > 0){
		//debug(newCells);
			$.post('jqgrid/jqgrid/db/codec/table/stream/oper/information/element/' + id, {added:newCells, removed:removedCells, act_ns_trickmode:'true'}, function(data){
		//debug(data);
				$('#table_ns_list').empty();
				displayNSList(data, ['added_cell']);
			}, 'json');
		}
	});
    
}

function selectCell(event){
	$(event).toggleClass('selected_cell');
	updateOpertionWithSelectedStatus({table:'table_ns_list', op:'operation_with_selected'});
}

function fun_comp_stream(){
	
}
                              