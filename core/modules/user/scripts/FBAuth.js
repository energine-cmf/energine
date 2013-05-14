ScriptLoader.load('Overlay');
window.FBL = {
    appID:null,
    get:function () {
        return FBL.appID;
    },
    set:function (appID) {
        FBL.appID = appID;
    }
}
window.addEvent('domready', function () {
    var fbAuth = $('fbAuth');
    if (fbAuth) {
        fbAuth.addEvent('click', function (e) {
            Energine.cancelEvent(e);
            var over = new Overlay(document.body);
            over.show();
            (function (d) {
                var js, id = 'facebook-jssdk';
                if (d.getElementById(id)) {
                    return;
                }
                js = d.createElement('script');
                js.id = id;
                js.async = true;
                js.src = "//connect.facebook.net/en_US/all.js";
                d.getElementsByTagName('head')[0].appendChild(js);

            }(document));

            window.fbAsyncInit = function () {
                FB.init({
                    appId:FBL.get(),
                    status:true,
                    cookie:true,
                    xfbml:true,
                    oauth:true
                });
                FB.login(function (response) {
                    console.log(response);
                    if (response.authResponse) {
                        document.location = Energine.static + 'auth.php?fbAuth';
                    }
                    else {
                        over.hide();
                    }
                }, {scope:'email,user_about_me'});

            };

        });
    }
});




