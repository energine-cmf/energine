ScriptLoader.load('http://cdn.jsdelivr.net/jquery.slick/1.5.0/slick.min.js');

var ProductCarousel = function (el) {
    jQuery('.multiple-items', jQuery('#'+el)).slick({
        dots:false,
        infinite: true,
        slidesToShow: 3,
        slidesToScroll: 3
    });
}

