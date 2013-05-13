ScriptLoader.load('Form', 'FBAuth', 'VKAuth');

var LoginForm = new Class({
    initialize:function(element) {

        window.addEvent('domready', function() {
            var vkAuth = $('vkAuth');
            vkAuth.addEvent('click', function() {
                VK.Auth.login(vkAuth);
            });
        });
    }
});
