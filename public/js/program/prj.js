function program_prj_construct(gridId, options){
    var db = "program", table = "prj";
    var prefix = db + '0_0' + table + '0_0';
    var pid = options.gridOptions.postData.pid || 0;
    
    options.addOptions.beforeShowForm = function(formid){
        var start_time = $('#' + db + '0_0' + table + '0_0start_time');
        //如果有父节点，应该将开始时间设置成父节点的开始时间，如果没有父节点，则设置成当前时间
        if (pid == 0){
            var today = new Date();   
            var day = today.getDate();   
            var month = today.getMonth() + 1;   
            var year = today.getFullYear();   
            var date = year + "-" + month + "-" + day;   
            start_time.val(date);
        }
        else{
        
        }
    };
    
    options.addOptions.beforeSubmit = function(postdata, formid){
        debug(postdata);
        debug(options);
        postdata.pid = pid;
debug(postdata);
        return [true, ''];
    };
    
    return options;
};

