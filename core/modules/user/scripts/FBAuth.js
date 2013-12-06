/**
 * @file Contain the description of the next classes:
 * <ul>
 *     <li>[FBL]{@link FBL}</li>
 * </ul>
 *
 * @requires share/Energine
 * @requires share/Overlay
 *
 * @author Pavel Dubenko
 *
 * @version 1.0.0
 */

ScriptLoader.load('Overlay');

/**
 * Facebook login.
 * @namespace
 */
var FBL = /** @lends FBL */{
    /**
     * App ID.
     * @type {number|string}
     */
    appID:null,

    /**
     * Get the app ID.
     *
     * @function
     * @static
     * @returns {number|string}
     */
    get:function () {
        return FBL.appID;
    },

    /**
     * Set the app ID.
     *
     * @function
     * @static
     * @param {number|string} id ID.
     */
    set:function (id) {
        FBL.appID = id;
    }
};

window.addEvent('domready', function () {
    var fbAuth = $('fbAuth');
    if (!fbAuth) {
        return;
    }

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
                    document.location = Energine['static'] + 'auth.php?fbAuth';
                } else {
                    over.hide();
                }
            }, {scope:'email,user_about_me'});
        };
    });
});




