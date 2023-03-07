var browsers = ["Firefox", "Chrome", "Safari", "Opera", "MSIE", "Trident", "Edge"];
var userbrowser, useragent = navigator.userAgent;
for (var i = 0; i < browsers.length; i++) {
    if( useragent.indexOf(browsers[i]) > -1 ) {
        userbrowser = browsers[i];
        break;
    }
};
 
switch(userbrowser) {
    case 'MSIE':
        userbrowser = 'Internet Explorer';
        break;
 
    case 'Trident':
        userbrowser = 'Internet Explorer';
        break;
 
    case 'Edge':
        userbrowser = 'Edge';
        break;

     case 'Chrome':
        userbrowser = 'Chrome';
        break;

    case 'Firefox':
        userbrowser = 'Firefox';
        break;

    case 'Safari':
        userbrowser = 'Safari';
        break;

    case 'Opera':
        userbrowser = 'Opera';
        break;
}

console.log('Browser:'+userbrowser);

if ( userbrowser === 'Internet Explorer') {
    console.log(userbrowser+' detected');

        var accessdenied = '<div class="fade show" id="unsupported-browser" style="background:rgba(0,0,0,0.6);"><div class="modal-dialog" style="display: block;position:fixed;height: 100%;margin: 0;padding: 0;"><div class="modal-content" style="text-align: center;position: fixed !important;height: auto;min-height: 100%;border-radius: 0;"><div class="modal-header"><h4 class="modal-title">Unsupported Browser - '+userbrowser+'</h4> </div><div class="modal-body" style="margin-top: 10%;font-size: xx-large;"><p>Oops... it looks like your using an incompatible browser.</p><br><p>Users of <strong>Internet Explorer</strong> are prohibited from continuing to this site.<br/><br/>We recommend upgrading to and using the latest <a href="https://www.microsoft.com/en-us/edge" target="_blank">Microsoft Edge</a>, <a href="https://www.google.com/chrome/" target="_blank">Google Chrome</a>, or <a href="https://www.mozilla.org/en-GB/firefox/new/" target="_blank">Firefox</a>.</p><p class="browser_buttons" style="margin-top:50px;"><a href="https://www.google.com/chrome/" style="background:#4C8BF5;" target="_blank" class="btn"><i class="fa fa-chrome"></i> Download Chrome</a><a href="https://www.microsoft.com/en-us/edge" style="background:#3277BC;" target="_blank" class="btn"><i class="fa fa-edge"></i> Download Edge</a><a href="https://www.mozilla.org/en-GB/firefox/new/" style="background:#D6530F;" target="_blank" class="btn"><i class="fa fa-firefox"></i> Download Firefox</a></div></div></div></div>';
        document.getElementByTag('body').innerHTML = accessdenied;

}
