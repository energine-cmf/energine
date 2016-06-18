var Select2_wrapper_jquery;
(function ($) {
    Select2_wrapper_jquery = function (el, url, dataFunction, responseFunction) {
        var select;

        (select = $('select', el)).select2({
            ajax: {
                "url": url,
                delay: 250,
                method: 'POST',
                processResults:responseFunction,
                data: dataFunction,
                dataType: 'json'
            },
            language: select.data('language'),
            maximumSelectionLength:1,
            cache: true
        });
    }
})(window.jQuery);
;