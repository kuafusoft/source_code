function initCandidate(gridId, jsonData){
    jsonData.gridOptions.subGrid = true;
    jsonData.gridOptions.subGridRowExpanded = function(subgrid_id, row_id) { 
        expandSubGridRow('candidate_id', subgrid_id, row_id, 'recruite', 'vw_interview', 'recruite', 'candidate');
    };
    jsonData.onContextMenuShow = function(el){
        ajaxuploadItem('li#attach', 'attach_resume', '/jqgrid/jqgrid/oper/attach/db/recruite/table/candidate/element/' + $(el).attr('id'));
    };
    jsonData.contextMenu = function(action, el){
        var handled = true;
        var rowId = $(el).attr('id');
        switch(action){
            case 'information':
                $('<div id="candidate_information" />').load('/jqgrid/jqgrid', {oper:'information', db:'recruite', table:'candidate', element:rowId}, function(data){
                    var dialog = $(this).html(data)
                		.dialog({
                		    width:800,
                		    height:500,
                			autoOpen: false,
                			title: 'Candidate Information',
                			modal: true,
                			buttons: {
                				Ok: function(){
                                    dialog.dialog("close");
                                }
                			},
                			open:function(event, ui){
                                $( "#candidate_information_tabs" ).tabs({ selected: 'tabs-base' });        
                            },
                            close:function(event, ui){
                                $('#candidate_information').remove();
                            }
                		});
                	dialog.dialog('open');
                });
                break;
            case 'interview':
                var dialog = $('div#candidate_interview');
                if (dialog.length == 0){
                    $('<div id="candidate_interview" />').load('/jqgrid/jqgrid/oper/interview/db/recruite/table/candidate/element/' + rowId, 
                        function(data){
//debug(data);                        
                            dialog = $(this).html(data)
                    		.dialog({
                    		    width:800,
                    			autoOpen: false,
                    			title: 'Input Interview Result',
                    			modal: true,
                    			buttons:{
                                    'OK':function(){
                                        var tags = [], grades = [];
                                        $('#candidate_interview [name="tag[]"]').each(function(i){tags[i] = $(this).val()});
                                        $('#candidate_interview [name="grade[]"]').each(function(i){grades[i] = $(this).val()});
                                        var comment = $('#candidate_interview #interview_comment').val();
debug([tags, grades, comment]);
                                        
                    				    var params = {
                                            oper:'interview', 
                                            db:'recruite', 
                                            table:'candidate', 
                                            element:rowId,
                                            tags:tags,
                                            grades:grades,
                                            comment:comment
                                        };
                    				    $.post('/jqgrid/jqgrid', params, function(data){
                        					dialog.dialog( "close" );
//        			                         $(gridId).trigger('reloadGrid');
                                        });
                                    },
                                    'Cancel':function(){
                    					dialog.dialog( "close" );
                                    }
                                },
                                close:function(event, ui){
//                                    dialog.dialog('destroy');
                                    $('#candidate_interview').remove();
                                },
                                open:function(event, ui){
                                    // bind event to category
                                    $('#candidate_interview [name="tag_category[]"]').bind("change", {}, bindTagWithCategory);
                                }
                    		});
                    		dialog.dialog("open");
                    	});
                    }
                    else{
                    	dialog.dialog({
                		    width:800,
                			autoOpen: false,
                			title: 'SRS Link Cases',
                			modal: true,
                			open:function(event, ui){}
                		});
                    	dialog.dialog('open');
                    }
                break;
                
            case 'arrange_interview':
                var dialog_param = {div_id:'candidate_arrange_interview', title:'Arrange Interview', 'ok':'OK', 'cancel':'Cancel', width:800};
                actionDialog(dialog_param, '/jqgrid/jqgrid/oper/arrange_interview/db/recruite/table/candidate/element/' + rowId);
                break;
                
            case 'linkproject':
                $('<div id="srs_linkproject" />').load('/jqgrid/jqgrid/oper/linkproject/db/sys_req/table/vw_prj_srs_node/element/' + rowId, function(data){
                    var dialog = $(this).html(data)
                		.dialog({
                		    width:800,
                		    height:500,
                			autoOpen: false,
                			title: 'Link Projects',
                			modal: true,
                			buttons: {
                				Ok: function(){
                				    var projects = [];
                				    var i = 0;
                				    $('[name="projects"]:checked:not(:disabled)').each(function(){
                                        projects[i ++] = $(this).val();
                                    });
//debug(projects);                                    
                				    $.post('/jqgrid/jqgrid', {oper:'linkproject', db:'sys_req', table:'vw_prj_srs_node', element:rowId, projects:projects}, function(data){
                    					dialog.dialog( "close" );
                                    });
                                }
                			},
                            close:function(event, ui){
                                $('#srs_linkproject').remove();
                            }
                		});
                	dialog.dialog('open');
                });
                break;

            default:
                handled = false;
                
        }
        return handled;
    };
    jsonData.gridComplete = function(gridId){
        ajaxuploadItem("div.ui-pg-div:contains('upload')", 'candidate_resume', '/jqgrid/jqgrid/oper/upload/db/recruite/table/candidate');
    };
//debug(jsonData);
    return jsonData;
}

function candidate_buttonActions(key, gridId, options){
debug(key);
debug(gridId);
debug(options);
    switch(key){
        case 'upload':
            break;        
    }
    alert("here");
}

function addInterviewResult(div, template){
    $("#" + div).append("<div>" + $("#" + template).html() + "</div>");
    $('#candidate_interview [name="tag_category[]"]').unbind("change", bindTagWithCategory).bind("change", {}, bindTagWithCategory);
}

function bindTagWithCategory(event){
    bindOptions({target:$(this).siblings('#candidate_interview [name="tag[]"]'), url:'/jqgrid/jqgrid', data:{db:'recruite', table:'candidate', oper:'getTags', category_id:$(this).val()}, blankItem:false});
}

