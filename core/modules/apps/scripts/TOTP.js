ScriptLoader.load('//cdn.jsdelivr.net/jquery.slick/1.5.0/slick.min.js');
Asset.css('simple_tabs.css');

var TOTP = function (el) {
    var el = jQuery('#' + el);
    jQuery('ul.tabs li', el).click(function () {
        var tab_id = jQuery(this).attr('data-tab');

        jQuery('ul.tabs li', el).removeClass('current');
        jQuery('.tab-content', el).removeClass('current');

        jQuery(this).addClass('current');
        jQuery("#" + tab_id, el).addClass('current');
    });
    jQuery('.items', el).slick({
        dots: false,
        infinite: true
    });
}