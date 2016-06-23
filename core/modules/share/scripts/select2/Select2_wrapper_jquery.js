var Select2_wrapper_jquery;
(function ($) {
    Select2_wrapper_jquery = function ($select, url, dataFunction, responseFunction, resultFunction, selectionFunction) {
        $select = $($select);
        if(!$('#select2-i18n').length){
            $.getScript('scripts/select2/i18n/' + $select.data('lang')+'.js');
        }

        return $select .select2({
            theme: "classic",
            ajax: {
                "url": url,
                delay: Lookup.TIMEOUT_PERIOD,
                method: 'POST',
                processResults: responseFunction,
                data: dataFunction,
                dataType: 'json',
                cache: true
            },
            templateResult: resultFunction,
            templateSelection: selectionFunction,
            language: $select.data('lang'),
            allowClear: true,
            escapeMarkup: function (markup) { return markup; }

            //,minimumInputLength: Lookup.START_CHAR_COUNT
        });
    }
})(window.jQuery);
;