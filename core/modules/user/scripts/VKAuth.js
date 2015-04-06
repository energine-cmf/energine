/**
 * @file Contain the description of the next classes:
 * <ul>
 *     <li>[VKI]{@link VKI}</li>
 * </ul>
 *
 * @requires share/Energine
 *
 * @author Pavel Dubenko
 *
 * @version 1.0.0
 */


/**
 * VKontakte login.
 *
 * @namespace
 */
var VKI = {
    /**
     * App ID.
     * @type {number|string}
     */
    appID:null,

    /**
     * Get the app id.
     *
     * @function
     * @static
     * @returns {number|string}
     */
    get:function () {
        return VKI.appID;
    },

    /**
     * Set the app ID.
     *
     * @function
     * @static
     * @param {number|string} id ID.
     */
    set:function (id) {
        VKI.appID = id;
    }
};

/**
 * Vkontakte authorisation.
 *
 * @param {Object} response Server response.
 */
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

window.addEvent('domready', function () {
    if(VKI.get())
        VK.init({apiId:VKI.get()});
});