function interview_tag_category_construct(gridId, options){
    options.gridOptions.subGrid = true;
    options.gridOptions.subGridRowExpanded = function(subgrid_id, row_id) { 
        expandSubGridRow('interview_tag_category_id', subgrid_id, row_id, 'recruite', 'interview_tag', 'recruite', 'interview_tag_category');
    };
    return options;
};