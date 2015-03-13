// JavaScript Document
function interview_tag_construct(gridId, jsonData){
/*
    jsonData.gridOptions.subGrid = true;
    jsonData.gridOptions.subGridRowExpanded = function(subgrid_id, row_id) { 
        expandSubGridRow('tag_id', subgrid_id, row_id, 'recruite', 'vw_interview', 'recruite', 'interview_tag');
    };
*/  
    var getCategory = function(){
        var editUrl = jsonData.gridOptions.editurl;
//debug(editUrl);        
        var pat = /parentid\/(.+?)\/parentdb\/(.+?)\/parenttable\/(.+)/;
        var res = editUrl.match(pat);
        if (res)
            return res[1];
        return 0; 
/*
            var parentId = res[1];
            var parentDb = res[2];
            var parentTable = res[3];
            if (parentTable == 'interview_tag_category'){
                $category.val(parentId);
                $category.attr('disabled', true);
            }
        }
*/        
    };
    jsonData.addOptions.clearAfterAdd = false;
    jsonData.addOptions.beforeShowForm = function(formId){
        var $category = formId.find("#recruite0_0interview_tag0_0interview_tag_category_id");
        $category.val(getCategory());
        $category.attr('disabled', true);
    };
    jsonData.addOptions.afterSubmit = function(response, postdata){
        $("#recruite0_0interview_tag0_0name").val('');
        return [true, ''];
    };

    return jsonData;
}

