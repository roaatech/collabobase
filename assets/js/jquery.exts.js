(function($) {

    $.fn.value = function(value) {
        if (value === undefined) {
            if ($(this).get(0).hasOwnProperty('value'))
                return $(this).val();
            else
                return $(this).html();
        } else {
            return this.each(function() {
                if ($(this).get(0).hasOwnProperty('value'))
                    $(this).val(value);
                else
                    $(this).html(value);
            });
        }
    }

})(jQuery);