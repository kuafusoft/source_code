// JavaScript Document
function codec_cycle_construct(gridId, jsonData){
    jsonData.gridOptions.subGridRowExpanded = function(subgrid_id, row_id) { 
        expandSubGridRow('cycle_id', subgrid_id, row_id, 'codec', 'zzvw_cycle_codec_case');
    };
	jsonData.gridOptions.information_height = $(document).height();
	jsonData.gridOptions.information_width = $(document).width();
    jsonData.contextMenu = function(action, el){
        var handled = true;
        var rowId = $(el).attr('id');
        switch(action){
            case 'stream_trickmode':
				var url = '/jqgrid/jqgrid/db/codec/table/cycle/oper/stream_trickmode/element/' + rowId;
				$('<div id="dialog_stream_trickmode">').load(url, function(data){
					var dialog = $(this).html(data).dialog({
						title:'Manage the Stream & Trickmode',
						height:600,
						width:1000,
						modal:true,
						autoOpen: false,
						beforeClose:function(event, ui){
							// check if there updated cells not submitted
							var exit_without_submit = false;
							if ($('#table_stream_trickmode td.updated_cell,td.removed_cell,td.new_cell,').length > 0){
								exit_without_submit = confirm("There are some cells updated not submitted yet!! Do you really want to exit?");
								return exit_without_submit;
								var dialog = $( '<div id="#dialog-confirm">There are some cells updated not submitted yet!! Do you really want to exit?</div>' ).dialog({
									resizable: false,
									height:340,
									modal: true,
									autoOpen: false,
									buttons: {
										"Yes": function() {
											$( this ).dialog( "close" );
											exit_without_submit = true;
										},
										No: function() {
											$( this ).dialog( "close" );
										}
									}
								});
								dialog.dialog('open');
//alert("exit_without_submit=" + exit_without_submit);								
								return exit_without_submit;
							}
							else
								return true;
						},
						close:function(event, ui){
							$(this).remove();
						},
						open:function(event, ui){
							cycle_open(event, ui, url);
						}
					});
					dialog.dialog('open');
				});
                break;
				
			case 'cycle_clone':
				var url = '/jqgrid/jqgrid/db/codec/table/cycle/oper/cycle_clone/element/' + rowId;
                var dialog_params = {div_id:'dialog_clone', title:'Clone the Cycle', height:500, width:600};
                             
                actionDialog(dialog_params, url, undefined, fun_comp_clone);
				break;
				
			case 'information':
				var dialog_params = {div_id:'div_information', title:'Detail Information', height:540, width:1000, open:open_cycle_inform};
				var url = '/jqgrid/jqgrid/db/codec/table/cycle/oper/information/element/' + rowId;
				actionDialog(dialog_params, url, undefined, fun_comp_clone);
				break;

            default:
                handled = false;
        }          
        return handled;
    }
    return jsonData;
}                

function fun_comp_clone(data){
	$('#codec_cycle_list').trigger('reloadGrid');
}

function open_cycle_inform(event, ui, url){
	inform_open(event, ui, url);
	// bind some events when the dialog open
	var select_cell_map = {table:'table_stream_trickmode', op:'operation_with_selected', 
		none:{selector:'', op:'remove'}, all:{selector:'', op:'add'}, 'new':{selector:'.new_cell'}, removed:{selector:'.removed_cell'},
		0:{selector:'.test_result_not_tested'}, '1':{selector:'.test_result_pass'}, '2':{selector:'.test_result_fail'}
		};
	var op_with_selected_map = {table:'table_stream_trickmode', op:'submit_to', selector:' td.selected_cell.added_cell[iscase="1"]',
		remove:{addClass:'removed_cell'}, unremove:{removeClass:'removed_cell'},
		0:{addClass:'test_result_not_tested updated_cell', removeClass:'test_result_pass test_result_fail', addAttr:[{name:'label', value:'NT'}, {name:'new_result', value:0}]}, 
		1:{addClass:'test_result_pass updated_cell', removeClass:'test_result_not_tested test_result_fail', addAttr:[{name:'label', value:'Pass'}, {name:'new_result', value:1}]}, 
		2:{addClass:'test_result_fail updated_cell', removeClass:'test_result_not_tested test_result_pass', addAttr:[{name:'label', value:'Fail'}, {name:'new_result', value:2}]}, 
		submit:{id:'submit_to', selector:'td.new_cell,td.removed_cell,td.updated_cell'}};
	
	$('#select_cells').bind('change', select_cell_map, select_cells_change);
	$('#operation_with_selected').bind('change', op_with_selected_map, operation_with_selected_change);
	
	$('#input_op #submit_to').click(function(){
		updateCycleDetail(url);
		$('#submit_to').attr('disabled', true);
    });
    $('#input_op #add_streamtrickmode').click(function(){
		getSOP(event, ui, url, true);
    });
    $('#input_op #add_trickmode').click(function(){
		getTrickmode(event, ui, url);
    });

	$('#codec0_0zzvw_cycle_detail0_0stream').bind('keypress', function(event){ 
		return triggerButton(event, '#btn_filter'); 
	});
	$('#information_tabs #codec_cycle #div_report_op #generate_report').bind('click', function(event){
		$.post('jqgrid/jqgrid/oper/report/db/codec/table/cycle/table_name/cycle/element/' + $('#element_id').val(), function(data){
			debug(data);
            location.href = "download.php?filename=" + encodeURIComponent(data['file_name']) + "&remove=0";
			$('#information_tabs #codec_cycle #table_reports').trigger('reloadGrid');
	
		}, 'json');
	});
    displayDetail();
	listReports('#information_tabs #codec_cycle #table_reports', '#div_reports_pager', 'codec', 'cycle', $('#element_id').val());
}
	
function getSOP(event, ui, url){ // get stream/output/player
	var db = 'codec', table = 'stream';
	var gridId = 'cycle_stream_list', pagerId = 'cycle_stream_pager';
	
	var html =  '<div id="other_info">'  + 
					'<label>Select Video Output<select id="output_id"></select></label>' + 
					'<label>Select Audio Output<select id="audio_output_id"></select></label>' + 
					'<label>Select Player<select id="player_id"></select></label>' + 
				'</div>' + 
				'<table id="' + gridId + '" class="scroll" style="width:100%"></table>' + 
				'<div id="' + pagerId + '"></div>' + 
				'<div id="cycle_stream_list_tmpForm"></div>';

	var dialog = $('<div id="select_sop" />').html(html).dialog({
		modal: true,
		autoOpen: false,
		width:900,
		height:500,
		title: 'Select stream',

		open:function(event, ui){
			var options = {
				config:{
					url:'jqgrid/jqgrid',
					data:{oper:'getGridOptions', table:table, db:db},
					getConfig:true
				},
				gridOptions:{
					url:'jqgrid/list', // /db/' + db + '/table/' + table,
					postData:{table:table, db:db},
					shrinkToFit:false,
					autowidth:true,
					editurl:'jqgrid/jqgrid/db/' + db + '/table/' + table
				}
			};
			grid_init('#' + gridId, '#' + pagerId, options);
			// fill the all select options
			$.post('jqgrid/jqgrid', {db:db, table:'output', oper:'getSelectList', selectTag:0}, function(data){
				$('#output_id').append(data);
			});
			$.post('jqgrid/jqgrid', {db:db, table:'audio_output', oper:'getSelectList', selectTag:0}, function(data){
				$('#audio_output_id').append(data);
			});
			$.post('jqgrid/jqgrid', {db:db, table:'player', oper:'getSelectList', selectTag:0}, function(data){
//debug(data);
				$('#player_id').append(data);
			});
			
		},
		close:function(event, ui){
			$(this).remove();
		},
		buttons: {
			"Add To Cycle":function(){
				var streams = [];
				var selected = $('#' + gridId).getGridParam('selarrrow');
				var player = {id:$('#player_id').children('option:selected').val(), name:$('#player_id').children('option:selected').text()};
				var output = {id:$('#output_id').children('option:selected').val(), name:$('#output_id').children('option:selected').text()};
				var audio_output = {id:$('#audio_output_id').children('option:selected').val(), name:$('#audio_output_id').children('option:selected').text()};
		//debug(selected);
				if (selected.length == 0){
					alert("Please select stream first");
					return false;
				}        
				else{
					$.each(selected, function(i, n){
						var row = $('#' + gridId).getRowData(n);
						streams.push({id:n, name:row['codec0_0stream0_0name'], note:row['codec0_0stream0_0note']});
					});
					addSOPs('#table_stream_trickmode', streams, player, output, audio_output);
				}
				$(this).dialog('close');
				updateSubmitStatus({table:'table_stream_trickmode', submit:{id:'submit_to', selector:'td.new_cell,td.removed_cell,td.updated_cell'}});
			},
			Cancel:function(){
				$(this).dialog('close');
			}
		}
				
	});
	dialog.dialog('open');    
	
}

function getTrickmode(event, ui, url){
	var db = 'codec', table = 'trickmode';
	var gridId = 'cycle_trickmode_list', pagerId = 'cycle_trickmode_pager';
	
	var html =  '<table id="' + gridId + '" class="scroll" style="width:100%"></table>' + 
				'<div id="' + pagerId + '"></div>' + 
				'<div id="cycle_trickmode_list_tmpForm"></div>';

	var dialog = $('<div id="select_trickmode" />').html(html).dialog({
		modal: true,
		autoOpen: false,
		width:800,
		height:500,
		title: 'Select Trickmode',

		open:function(event, ui){
			var options = {
				config:{
					url:'jqgrid/jqgrid',
					data:{oper:'getGridOptions', table:table, db:db},
					getConfig:true
				},
				gridOptions:{
					url:'jqgrid/list', // /db/' + db + '/table/' + table,
					postData:{table:table, db:db},
					shrinkToFit:false,
					autowidth:true,
					editurl:'jqgrid/jqgrid/db/' + db + '/table/' + table
				}
			};
			grid_init('#' + gridId, '#' + pagerId, options);
		},
		close:function(event, ui){
			$(this).remove();
		},
		buttons: {
			"Add To Cycle":function(){
				var trickmodes = [];
				var selected = $('#' + gridId).getGridParam('selarrrow');
		//debug(selected);
				if (selected.length == 0){
					alert("Please select trickmodes first");
					return false;
				}        
				else{
					$.each(selected, function(i, n){
		//debug([i, n]);            
						var row = $('#' + gridId).getRowData(n);
						trickmodes.push({id:n, label:row['codec0_0trickmode0_0name'], title:row['codec0_0trickmode0_0note']});
					});
					addTrickmodes('#table_stream_trickmode', trickmodes);
				}
				$(this).dialog('close');
				updateSubmitStatus({table:'table_stream_trickmode', submit:{id:'submit_to', selector:'td.new_cell,td.removed_cell,td.updated_cell'}});
			},
			Cancel:function(){
				$(this).dialog('close');
			}
		}
				
	});
	dialog.dialog('open');    
}
    
function resetFilter(){
	
	var elements = ['container', 'priority', 'v4cc', 'a_codec', 'player', 'output', 'audio_output'];
	var ele;
	$('#codec0_0zzvw_cycle_detail0_0stream').val('');

	for(e in elements){
		ele = '#codec0_0zzvw_cycle_detail0_0' + elements[e] + '_id';
		$(ele).find('option:eq(0)').attr('selected', 'selected');
	}
	displayDetail();
	
}

function updateFilter(data){
//debug(data);
	
	var elements = ['container', 'priority', 'v4cc', 'a_codec', 'player', 'output', 'audio_output'];
	var ele;
	for(e in elements){
		ele = '#codec0_0zzvw_cycle_detail0_0' + elements[e] + '_id';
		$(ele).empty();
		$.each(data[elements[e]], function(i, item){
			if (item['id'] != null)
				$(ele).append("<option value='" + item['id'] + "'>" + item['name'] + "</option>");
		})
	}
	
}

//Get the cycle stream-trickmode list and display them
/*每个Cell都应该有一下属性：
id: cycle_detail里的id
case_id:cycle_detail里的case_id，也是codec_case里的id
sop_id:codec_case里的stream_output_player_id
tm_id:codec_case里的trickmode_id
comp_id:sop_id的分解,stream_id:output_id:audio_output_id:player_id
*/
function displayDetail(){
	var cycle_id = $('#element_id').val(), html, detail, cell;
	var postData = {db:'codec', table:'zzvw_cycle_codec_case', _search:true, 
		codec0_0zzvw_cycle_codec_case0_0name:$('#codec0_0zzvw_cycle_detail0_0stream').val(),
		codec0_0zzvw_cycle_codec_case0_0cycle_id:cycle_id
		};
	var elements = ['container', 'priority', 'v4cc', 'a_codec', 'player', 'output', 'audio_output'];
	var ele, target;
	for(e in elements){
		ele = 'codec0_0zzvw_cycle_codec_case0_0' + elements[e] + '_id';
		target = '#codec0_0zzvw_cycle_detail0_0' + elements[e] + '_id';
		postData[ele] = $(target).val();
	}

//debug(postData);

	$.post('/jqgrid/jqgrid/oper/getDetail', postData, function(data){
//debug(data);
		html = '<tr sop_id="0" class="header">';
		cell = {sopt:'s', classes:['sop', 'header'], style:'width:300px', label:'Stream', iscase:'0'};
		html = html + displayCell(cell);
		cell['sopt'] = 'o';
		cell['style'] = "width:60px";
		cell['label'] = 'Video';
		html = html + displayCell(cell);
		cell['sopt'] = 'ao';
		cell['label'] = 'Audio';
		html = html + displayCell(cell);
		cell['sopt'] = 'p';
		cell['label'] = 'Player';
		html = html + displayCell(cell);
		$.each(data['trickmodes'], function(i, trickmode){
			cell = {sopt:'t', label:trickmode['name'], title:trickmode['note'], tm_id:trickmode['id'], onclick:'colClick(this)', ondblclick:'dbl_click(this)', classes:['added_cell', 'header']};
//debug(trickmode);			
			html = html + displayCell(cell);
		});
		html = html + '</tr>';
		
		$.each(data['stream_output_player'], function(i, stream_output_player){
			html = html + '<tr sop_id="' + stream_output_player['sop_id'] + '" comp_id="' + stream_output_player['comp_id'] + '">';
			// insert stream cell
			cell = {iscase:'0', sop_id:stream_output_player['sop_id'], comp_id:stream_output_player['comp_id'], sopt:'s', label:stream_output_player['stream'], title:stream_output_player['note'], classes:['added_cell', 'header'], onclick:'rowClick(this)', ondblclick:'dbl_click(this)'};
			html = html + displayCell(cell);
			// insert video output cell
			cell['label'] = stream_output_player['output'];
			cell['sopt'] = 'p';
			cell['title'] = '';
			cell['onclick'] = '';
			cell['ondblclick'] = '';
			html = html + displayCell(cell);
			// insert audio output cell
			cell['label'] = stream_output_player['audio_output'];
			cell['sopt'] = 'ao';
			html = html + displayCell(cell);
			// insert player cell
			cell['label'] = stream_output_player['player'];
			cell['sopt'] = 'p';
			html = html + displayCell(cell);
			// insert trickmode cells
			$.each(data['trickmodes'], function(j, trickmode){
				cell['tm_id'] = trickmode['id'];
				cell['sopt'] = 't';
				cell['label'] = 'NT'; //Not tested
				cell['classes'] = [];
				cell['onclick'] = 'cellClick(this)';
				cell['iscase'] = '1';
				if (data['detail'][stream_output_player['sop_id']][trickmode['id']] != undefined){
					detail = data['detail'][stream_output_player['sop_id']][trickmode['id']];
					cell['id'] = detail['id'];
					cell['case_id'] = detail['case_id'];
					cell['resulttype_id'] = detail['resulttype_id'];
					cell['label'] = detail['resulttype'];
					if (detail['comment'] != '' && detail['comment'] != null)
						cell['title'] = detail['comment'];
					else
						cell['title'] = 'Click to select/unselect, Double Click to Comment';
					cell['classes'].push('added_cell');
					cell['ondblclick'] = 'dbl_click(this)';
					if (cell['resulttype_id'] > 0){
						cell['classes'].push('test_result_' + cell['label'].toLowerCase());
					}
					else{
						cell['classes'].push('test_result_not_tested');
					}
				}
				else{
					cell['label'] = 'X';
					cell['ondblclick'] = '';
					cell['case_id'] = '';
					if (stream_output_player['ns'] && stream_output_player['ns'][trickmode['id']]){
						cell['classes'].push("not_supported");
						cell['title'] = "The stream DO NOT Support the trickmode";
					}
					else{
						cell['classes'].push("not_added_cell");
						cell['title'] = 'Click to add/remove the stream/trickmode';
					}
				}
				html = html + displayCell(cell);
			});
			html = html + '</tr>';
		});
		$('#table_stream_trickmode').html(html);
	}, 'json');
}

function addSOPs(table_id, streams, player, output, audio_output){
	var cols = $(table_id + " tr").eq(0).children('td'), cell = {classes:['new_cell']};
//debug(cols);    
    $.each(streams, function(i, stream){
//debug(stream);
		cell['comp_id'] = stream['id'] + ':' + player['id'] + ':' + output['id'] + ':' + audio_output['id'];
		cell['onclick'] = '';
//debug([i, n]);
        if($(table_id + ' tr[comp_id="' + cell['comp_id'] + '"]').length > 0)
            return;
		html = '<tr comp_id="' + cell['comp_id'] + '">';

		$.each(cols, function(j, col){
			cell['sopt'] = $(col).attr('sopt');
			cell['iscase'] = '0';
			switch(cell['sopt']){
				case 's': // stream
					cell['label'] = stream['name'];
//					cell['onclick'] = 'rowClick(this)';
					cell['title'] = 'Double Click to add comment';
					break;
				case 'o': // Video output
					cell['label'] = output['name'];
					cell['title'] = '';
					break;
				case 'ao': // audio outpout
					cell['label'] = audio_output['name'];
					cell['title'] = '';
					break;
				case 'p': // player
					cell['label'] = player['name'];
					cell['title'] = '';
					break;
				default:
					cell['onclick'] = 'cellClick(this)';
					cell['iscase'] = '1';
					cell['tm_id'] = $(col).attr('tm_id');
					cell['label'] = 'New';
					cell['title'] = 'Click to toggle between REMOVE and RESUME';
					break;
			}
			html = html + displayCell(cell);
		});
		html = html + "</tr>";
		$(table_id).append(html);
    });
}

function addTrickmodes(table_id, trickmodes){
	var cell = {classes:['new_cell']}, col, lastCol;
    $.each(trickmodes, function(i, trickmode){
        if ($(table_id + " tr").eq(0).children("td[tm_id='" + trickmode['id'] + "']").length > 0)
            return;
		$.each($(table_id + ' tr'), function(i, row){
			col = $(row).children('td:first');
			lastCol = $(row).children('td:last');
			cell['tm_id'] = trickmode['id'];
			cell['label'] = trickmode['label'];
			cell['title'] = trickmode['title'];
			cell['comp_id'] = $(col).attr('comp_id');
			cell['onclick'] = '';//'colClick(this)';
			cell['iscase'] = '0';
			if ($(col).attr('sop_id') != undefined)
				cell['sop_id'] = $(col).attr('sop_id');
			if ($(col).attr('case_id') != undefined)
				cell['case_id'] = $(col).attr('case_id');
			if (i > 0){
				cell['label'] = 'New';
				cell['iscase'] = '1';
				cell['onclick'] = 'cellClick(this)';
				cell['title'] = 'Click to toggle between REMOVE AND RESUME';
			}
			lastCol.after(displayCell(cell));
		});
    });
}

function rowClick(e){
    var className = 'selected_cell';
    var tag = $(e).hasClass(className);
    
    $(e).toggleClass(className);
    $.each($(e).siblings(), function(i, n){
        if (tag)
            $(n).removeClass(className);
        else if($(n).hasClass('added_cell') || $(n).hasClass('new_cell'))
            $(n).addClass(className);
    });
	updateOpertionWithSelectedStatus({table:'table_stream_trickmode', op:'operation_with_selected'});
}

function cellClick(e){
	var className = 'selected_cell';
    var tag = $(e).hasClass(className);

	if ($(e).hasClass('added_cell')){
		$(e).toggleClass('selected_cell');
	}
	else if (!$(e).hasClass('not_supported')){
		$(e).toggleClass('new_cell');
		if ($(e).hasClass('new_cell')){
			$(e).html('New');
		}
		else
			$(e).html('X');
		updateSubmitStatus({table:'table_stream_trickmode', submit:{id:'submit_to', selector:'td.new_cell,td.removed_cell,td.updated_cell'}});
	}
	updateOpertionWithSelectedStatus({table:'table_stream_trickmode', op:'operation_with_selected'});
}

function colClick(e){
	var className = 'selected_cell';
    var tag = $(e).hasClass(className);
    
    var tm_id = $(e).attr('tm_id');
    var table = $(e).parents('table');
    $.each($(table).find('td[tm_id="' + tm_id + '"]'), function(i, n){
        if (tag)
            $(n).removeClass(className);
        else if ($(n).hasClass('added_cell'))
            $(n).addClass(className);
    });
	updateOpertionWithSelectedStatus({table:'table_stream_trickmode', op:'operation_with_selected'});
}

function dbl_click(e){
	var sopt = $(e).attr('sopt');
	var iscase = $(e).attr('iscase');
	var dialog_params = {'width':800};
	var url;
	var fun_comp;
	if (iscase == '1'){ // edit the test result
		dialog_params['fun_comp'] = function(data){
//debug(data);			
			$(e).attr('resulttype_id', data['resulttype_id']);
			if (data['resulttype_id'] == 0)
				$(e).html('NT');
			else
				$(e).html(data['resulttype']);
			$(e).attr('newResulttype_id', 0);
			$(e).removeClass('test_result_not_tested test_result_pass test_result_fail');
			if (data['resulttype_id'] == 0)
				$(e).addClass('test_result_not_tested');
			else
				$(e).addClass('test_result_' + data['resulttype'].toLowerCase());
		};
		view_edit('codec', 'zzvw_cycle_codec_case', $(e).attr('id'), dialog_params);
	}
	else if (sopt == 's'){ // stream, edit the sop note
		dialog_params['open'] = function(event, ui, url){
			var disabledFields = ['stream_id', 'output_id', 'audio_output_id', 'player_id'];
			for(i in disabledFields){
				$('#codec0_0stream_output_player0_0' + disabledFields[i]).removeAttr('editable');
			}
		};
		dialog_params['fun_comp'] = function(data){
			$(e).attr('title', data['note']);
		};
		view_edit('codec', 'stream_output_player', $(e).attr('sop_id'), dialog_params);
	}
	else if (sopt == 't'){ // trickmode, edit the trickmode note
		dialog_params['open'] = function(event, ui, url){
			var disabledFields = ['name', 'isactive'];
			for(i in disabledFields){
				$('#codec0_0trickmode0_0' + disabledFields[i]).removeAttr('editable');
			}
		};
		dialog_params['fun_comp'] = function(data){
			$(e).attr('title', data['note']);
		};
		view_edit('codec', 'trickmode', $(e).attr('tm_id'), dialog_params);
	}
}

function updateCycleDetail(url){
	var newCells = [], removedCells = [], updatedCells = [];
	$.each($('#table_stream_trickmode td[iscase="1"]'), function(i, n){
		var id;
		if($(n).attr('case_id') != undefined && $(n).attr('case_id') != '')
			id = $(n).attr('case_id');
		else if($(n).attr('sop_id') != undefined)
			id = $(n).attr('sop_id') + ':' + $(n).attr('tm_id');
		else
			id = $(n).attr('comp_id') + ':' + $(n).attr('tm_id');
		if ($(n).hasClass('new_cell'))
			newCells.push(id);
		if ($(n).hasClass('removed_cell'))
			removedCells.push(id);
		if ($(n).hasClass('updated_cell')){
			id = id + ':' + $(n).attr('new_result');
			updatedCells.push(id);
		}
	});
	if (newCells.length > 0 || removedCells.length > 0 || updatedCells.length > 0)
//debug(newCells);
		$.post(url, {new_cells:newCells, removed_cells:removedCells, updated_cells:updatedCells, act_ns_trickmode:'true'}, function(data){
//debug(data);
		//update the table
			updateFilter(data);
			updateCycleStatistics(data);
			displayDetail();
		}, 'json');
}
	
function updateCycleStatistics(data){
	var html = "Streams: [" + data['streamCount'] + "], Trickmodes: [" + data['trickmodeCount'] + "]";
	var resultCount, resulttype_id;
	var resulttypes = data['resulttypes'];
//debug(resulttypes);
	for(i in data['resultCounts']){
		resultCount = data['resultCounts'][i];
		resulttype_id = resultCount['resulttype_id'];
		if (resulttype_id == 0 || resulttype_id == null || resulttype_id == undefined)
			html += ", Not Tested:[" + resultCount['resulttype_count'] + "]";
		else
			html += ", " + resulttypes[resulttype_id]['name'] + ":[" + resultCount['resulttype_count'] + "]";
	}
//debug(html);
	$('#cycle_result').html(html);
}

function submit_cr(event){
	
}
