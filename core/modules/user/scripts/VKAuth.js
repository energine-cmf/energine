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
    if(VKI.get())
        VK.init({apiId:VKI.get()});
});
function vkAuth(response) {
    if (response.session) {
        /* Пользователь успешно авторизовался */
        if (response.status == "connected") {
            window.location.href = Energine.base + 'auth.php?vkAuth';
        }
    } else {
        /* Пользователь нажал кнопку Отмена в окне авторизации, пока ничего с этим не делаем. */
    }
}