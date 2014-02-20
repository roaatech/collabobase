(function($) {

    $.fn.showcroll = function(options) {

        ops = $.extend({}, options);

        return this.each(function() {
            pops = $.extend({}, ops);

//            console.log($(this)[0].scrollHeight);
//            console.log($(this).outerHeight());

            sh = $(this)[0].scrollHeight;
            oh = $(this).outerHeight();

            if (sh <= oh)
                return;

            if ($(this).css("position") == "static") {
                $(this).css("position", "relative");
            }

        });

    };

})(jQuery);