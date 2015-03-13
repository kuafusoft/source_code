// JavaScript Document
function codec_cycle_construct(gridId, jsonData){
    jsonData.contextMenu = function(action, el){
        var handled = true;
        var rowId = $(el).attr('id');
        switch(action){
            case 'stream_trickmode':
                var dialog_params = {div_id:'dialog_stream_trickmode', title:'Manage the Stream & Trickmode', 
                    height:500, width:1200, open:cycle_open, fun_ok:'updateStreamTrickmodes'};
                var url = '/jqgrid/jqgrid/db/codec/table/cycle/oper/stream_trickmode/element/' + rowId;
                             
                actionDialog(dialog_params, url);
                break;

            case 'information':
                var dialog_params = {div_id:'stream_information', title:'Stream Information', height:500, width:800, open:cycle_open};
                var url = '/jqgrid/jqgrid/db/codec/table/stream/oper/information/element/' + rowId;
                             
                actionDialog(dialog_params, url);
                break;

            default:
                handled = false;
        }          
        return handled;
    }
    jsonData.gridOptions.ondblClickRow = function(rowid, iRow, iCol, e){
//debug([rowid, iRow, iCol, e]);
        if(rowid && rowid!==jsonData.lastSel){ 
            jQuery(gridId).restoreRow(jsonData.lastSel); 
            jsonData.lastSel=rowid; 
        }
        jQuery(gridId).editRow(rowid, true); 
    }
    jsonData.lastSel = 0;
    return jsonData;
}                
                
function cycle_open(event, ui){
    $( "#stream_trickmode_tabs" ).tabs({ selected: 'tabs-current' });    
    $('#mode_radio').buttonset();    
    var db = 'codec', table = 'stream';
    var gridId = '#stream_trickmode_tabs #cycle_stream_list', pagerId = '#cycle_stream_pager';
    var options = {
        config:{
            url:'jqgrid/jqgrid',
            data:{oper:'getGridOptions', table:table, db:db},
            getConfig:true
        },
        gridOptions:{
            url:'jqgrid/list', ///db/' + db + '/table/' + table,
            postData:{table:table, db:db},
            shrinkToFit:false,
            autowidth:true,
            editurl:'jqgrid/jqgrid/db/' + db + '/table/' + table
        },
    };
    grid_init(gridId, pagerId, options);
    
    table = 'trickmode';
    gridId = '#stream_trickmode_tabs #cycle_trickmode_list', pagerId = '#cycle_trickmode_pager';
    options = {
        config:{
            url:'jqgrid/jqgrid',
            data:{oper:'getGridOptions', table:table, db:db},
            getConfig:true
        },
        gridOptions:{
            url:'jqgrid/list', ///db/' + db + '/table/' + table,
            postData:{table:table, db:db},
            shrinkToFit:false,
            autowidth:true,

            editurl:'jqgrid/jqgrid/db/' + db + '/table/' + table
        },
    };
    grid_init(gridId, pagerId, options);
    

    $('#add_stream').click(function(){
        var newStreams = [];
        var selected = $('#stream_trickmode_tabs #cycle_stream_list').getGridParam('selarrrow');
//debug(selected);
        if (selected.length == 0){
            alert("Please select streams first");
        }        
        else{
            $.each(selected, function(i, n){
//debug([i, n]);            
                var row = $('#stream_trickmode_tabs #cycle_stream_list').getRowData(n);
                newStreams.push({id:n, label:row['codec0_0stream0_0name']});
            });
            addStreams('#table_stream_trickmode', newStreams, true);
        }
    });
    
    $('#add_trickmode').click(function(){
        var newTrickmodes = [];
        var selected = $('#stream_trickmode_tabs #cycle_trickmode_list').getGridParam('selarrrow');
//debug(selected);
        if (selected.length == 0){
            alert("Please select trickmodes first");
        }        
        else{
            $.each(selected, function(i, n){
                var row = $('#stream_trickmode_tabs #cycle_trickmode_list').getRowData(n);
                newTrickmodes.push({id:n, label:row['codec0_0trickmode0_0name']});
            });
            addTrickmodes('#table_stream_trickmode', newTrickmodes, true);
        }
    });
    
    $('#radio1').click(function(){ // add/remove radio
        $( "#stream_trickmode_tabs" ).tabs("option", "disabled", [] );
        $('#input_op').hide();
    });
    
    $('#radio2').click(function(){ // select/input radio
        $( "#stream_trickmode_tabs" ).tabs("option", "disabled", [1,2] );
        $('#input_op').show();
    });
    
    $('#select_cell').click(function(){
        var resulttype_id = $('#test_result_type option:selected').val();
        var resulttype_text = $('#test_result_type option:selected').text().toLowerCase();
        if (resulttype_id == '')
            return;
        switch(resulttype_id){
            case 'none':
                $('#table_stream_trickmode td').removeClass('selected_row');
                break;
            case 'all':
                $('#table_stream_trickmode td.added_cell').addClass('selected_row');
                break;
            case 'new':
                $('#table_stream_trickmode td.new_row').addClass('selected_row');
                break;
            case '0':
                $('#table_stream_trickmode td.test_result_not_tested').addClass('selected_row');
                break;
            default:
                $('#table_stream_trickmode td.test_result_' + resulttype_text).addClass('selected_row');
        }
    });
    
    $('#input_result').click(function(){
        var resulttype_id = $('#input_test_result_type option:selected').val();
        var resulttype_text = $('#input_test_result_type option:selected').text();
        var allTestResultClass = "test_result_not_tested test_result_pass test_result_fail";
        var className;
        if (resulttype_id == '')
            return;
        if (resulttype_id == '0')
            className = 'test_result_not_tested';
        else
            className = 'test_result_' + resulttype_text.toLowerCase();
debug(className);            
        $('#table_stream_trickmode td.selected_row').each(function(){
            $(this).removeClass(allTestResultClass);
            $(this).html(resulttype_text);
            $(this).addClass(className);
        });
        
    });

}
     
                   
function addStreams(table_id, streams, newRow, removedRow){
    var cols = $(table_id + " tr").eq(0).children('td');
//debug(cols);    
    $.each(streams, function(i, n){
//debug([i, n]);
        if($(table_id + " tr[id='" + n['id'] + "']").length > 0)
            return;
        addRow(table_id, n['id'], n['label'], cols, newRow, removedRow);
    });
}

function addTrickmodes(table_id, trickmodes, newRow, removedRow){
    $.each(trickmodes, function(i, trickmode){
        if ($(table_id + " tr").eq(0).children("td[id='" + trickmode['id'] + "']").length > 0)
            return;
//debug([i, trickmode]);    
        addCol(table_id, trickmode['id'], trickmode['label'], newRow, removedRow);    
    });
}

function addRow(table_id, id, label, cols, newRow, removedRow){
    var html = "<tr id='" + id + "'>";
    var cell = {};
    $.each(cols, function(i, n){
        if (n == '')
            return;
        if (i == 0){
            cell = {id:id, row:id, col:0, label:label, title:'Click to toggle between REMOVE and RESUME'};
            html = html + displayCell(cell, 'rowClick', newRow, removedRow);
        }
        else{
            cell = {id:id + ':' + $(n).attr('id'), row:id, col:$(n).attr('id'), label:'New Added', title:'Click to toggle between REMOVE and RESUME'};
            html = html + displayCell(cell, 'cellClick', newRow, removedRow);
        }
    });
    html = html + "</tr>";
    $(table_id).append(html);
}

function addCol(table_id, colId, label, newCol, removedCol){
    $.each($(table_id + ' tr'), function(i, n){
        var html;
        var col = $(n).children('td:first');
        var lastCol = $(n).children('td:last');
        if (i == 0){
            html = displayCell({id:colId, col:colId, row:0, label:label}, 'colClick', newCol, removedCol);    
        }
        else{
            html = displayCell({id:$(col).attr('id') + ':' + colId, col:colId, row:$(col).attr('id'), label:'New Added'}, 'cellClick', newCol, removedCol);    
        }
        lastCol.after(html);
    });
}

function displayCell(cell, click, newRow, removedRow){
    if (newRow == undefined)
        newRow = false;
    if (removedRow == undefined)
        removedRow = false;
    var classes = [];
    if (newRow)
        classes.push('new_row');
    if (removedRow)
        classes.push('removed_row');
    var html = "<td id='" + cell['id'] + "' row='" + cell['row'] + "' col='" + cell['col'] + "' onclick='javascript:" + click + "(this)'";
    if (cell['title'] != undefined)
        html += " title='" + cell['title'] + "'";
    if (classes.length > 0)
        html = html + " class='" + classes.join(' ') + "'";
    html = html + ">" + cell['label'] + "</td>";
    return html;
}

function rowClick(e){
    var mode =  $(":input[name='mode'][checked]").val();
    var className = 'removed_row';
    if (mode == "2") // select/input mode
        className = 'selected_row';
    var tag = $(e).hasClass(className);
    
    $(e).toggleClass(className);
    $.each($(e).siblings(), function(i, n){
        if (tag)
            $(n).removeClass(className);
        else if(mode == "1" || $(n).hasClass('added_cell'))
            $(n).addClass(className);
    });
}

function cellClick(e){
    var mode =  $(":input[name='mode'][checked]").val();
    var className = 'removed_row';
    if (mode == "2") // select/input mode
        className = 'selected_row';
    var tag = $(e).hasClass(className);

    if (mode == "2"){
        if ($(e).hasClass('added_cell')){
            $(e).toggleClass('selected_row');
        }
    }
    else{
        var removed_cell = $(e).hasClass('removed_row');
        var not_added = $(e).hasClass('not_added_cell');
        var not_supported = $(e).hasClass('not_supported_cell');
        
        if (removed_cell)
            $(e).removeClass('removed_row');
        else if (not_added){
            $(e).toggleClass('new_row');
            if ($(e).hasClass('new_row'))
                $(e).html('New Added');
            else
                $(e).html('X');
        }
        else if (!not_supported)
            $(e).toggleClass('removed_row');
    }
}

function colClick(e){
    var mode =  $(":input[name='mode'][checked]").val();
    var className = 'removed_row';
    if (mode == "2") // select/input mode
        className = 'selected_row';
    var tag = $(e).hasClass(className);
    
    var colId = $(e).attr('col');
    var table = $(e).parents('table');
    $.each($(table).find('td[col="' + colId + '"]'), function(i, n){
        if (tag)
            $(n).removeClass(className);
        else if (mode == '1' || $(n).hasClass('added_cell'))
            $(n).addClass(className);
    });
}

function updateStreamTrickmodes(url){
    var newRows = [];
    var removedRows = [];
    $.each($('#table_stream_trickmode tr td.new_row'), function(i, n){
        if ($(n).attr('col') != '0' && $(n).attr('row') != '0')
            newRows.push($(n).attr('id'));
    });
    $.each($('#table_stream_trickmode tr td.removed_row'), function(i, n){
        if ($(n).attr('col') != '0' && $(n).attr('row') != '0')
            removedRows.push($(n).attr('id'));
    });
    
debug(newRows);
debug(removedRows);
debug(url);
    
    $.post(url, {new_rows:newRows, removed_rows:removedRows}, function(data){
		$('#dialog_stream_trickmode').dialog( "close" );
debug(data);        
    });
        
}