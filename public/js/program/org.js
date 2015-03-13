function program_org_construct(gridId, options){
    var db = "program", table = "org";
    var prefix = db + '0_0' + table + '0_0';

    options.addOptions.beforeSubmit = function(postdata, formid){
        debug(postdata);
        debug(options);
        postdata.pid = options.gridOptions.postData.pid || 0;
debug(postdata);
        return [true, ''];
    };
    
    return options;
};

