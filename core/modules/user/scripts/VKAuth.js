window.VKI = {
    appID:null,
    get:function () {
        return VKI.appID;
    },
    set:function (appID) {
        VKI.appID = appID;
    }
}
window.addEvent('domready', function () {
    VK.init({apiId:VKI.get()});
    VK.UI.button('vk_login_button');
});
function vkAuth(response) {
    if (response.session) {
        /* Пользователь успешно авторизовался */
        if (response.status == "connected") {
            window.location.href = Energine.base + 'auth.php?vkAuth&' + Object.toQueryString(response.session.user);
        }
    } else {
        /* Пользователь нажал кнопку Отмена в окне авторизации, пока ничего с этим не делаем. */
    }
}