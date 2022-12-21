$(document).ready(function(){
    hideIframeInput()

    $("#DISPLAY").on('change', function(e) {
        hideIframeInput()
    });

});


function hideIframeInput() {
    var opt = jQuery("#DISPLAY").val();
    var  group = $('input[name="IFRAMEWEIGHT"]').closest(".form-group");
    var  group1 = $('input[name="APPLEPAYSCRIPT_1"]').closest(".form-group");
    if ( opt == 1 ) {
        group.show()
        group1.show()
    } else {
        group.hide()
        group1.hide()
    }
}