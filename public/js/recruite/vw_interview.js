function initVW_Interview(gridId, jsonData){
    jsonData.gridComplete = function(){
        // bind change event with tag_category
        $('#gs_recruite0_0vw_interview0_0interview_tag_category_id').unbind("change", bindSearchTagWithCategory).bind("change", bindSearchTagWithCategory);
    };
//debug(jsonData);
    return jsonData;
}

function bindSearchTagWithCategory(event){
    bindOptions({target:$('#gs_recruite0_0vw_interview0_0interview_tag_id'), url:'/jqgrid/jqgrid', data:{db:'recruite', table:'candidate', oper:'getTags', category_id:$(this).val()}, blankItem:true});
}

