// JavaScript Document
function prj_construct(gridId, options){
    options['editOptions']['beforeShowForm'] = function(formId){
        debug(formId);
        $('#srs20_0prj0_0platform_id').combobox();
        $('#srs20_0prj0_0os_id').combobox();
    }
    return options;
}