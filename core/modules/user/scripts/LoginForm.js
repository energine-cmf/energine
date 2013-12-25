/**
 * @file Contain the description of the next classes:
 * <ul>
 *     <li>[LoginForm]{@link LoginForm}</li>
 * </ul>
 *
 * @requires share/Energine
 * @requires share/Form
 * @requires FBAuth
 * @requires VKAuth
 *
 * @author Pavel Dubenko
 *
 * @version 1.0.0
 */

// todo: Need we Form?
ScriptLoader.load('Form', 'FBAuth', 'VKAuth');

/**
 * Login form.
 *
 * @constructor
 * @param {Element} element Login form element.
 */
var LoginForm = new Class({
    // constructor
    initialize:function(element) {
        window.addEvent('domready', function() {
            var vkAuth = $('vkAuth');
            if(vkAuth) {
                vkAuth.addEvent('click', function() {
                    VK.Auth.login(vkAuth);
                });
            }
        });
    }
});
