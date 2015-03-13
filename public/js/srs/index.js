
$(function(){
    $('div#tmpForm').append("<p>Just a Test</p>").css("color", "red");
//    srs_review();
});

function srs_review(){
    // display a dialog, showing: reviewers, notes, submit and cancel buttons
    // first get the reviewers
    
    // display the dialog
    $.ajax({
        url:"srs/getreviewdlg",
        type:'POST',
        success:function(html){
            alert("success");
            var prompt = $('<div id="div_prompt"></div>')
                		.html(html)
                		.dialog({
                			autoOpen: false,
                			title: 'Generating',
                			modal: true,
                			close:function(event, ui){
                			    $('#div_prompt').remove();
                            }
                		});
                
        	prompt.dialog('open');
            
//            $('div#tmpForm').append(html).dialog(;
        },
        error:function(request, text, e){
            alert(text + '.' + e);
            
        }
    });
};

function srs_linkCases(){

};
