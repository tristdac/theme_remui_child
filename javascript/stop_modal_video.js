// Stop YouTube and Vimeo iframe embedded videos when in a Bootstrap modal that has been closed
var waitForLoad = function () {
    if (typeof jQuery != "undefined") {
        $( document ).ready(function() {
                $('.modal').on('hidden.bs.modal', function () {
                        $("iframe").each(function() {
                                $(this).attr('src', $(this).attr('src')); 
                      });
                });
        });      
    } else {
        window.setTimeout(waitForLoad, 500);
    }
 };
 window.setTimeout(waitForLoad, 500);   
