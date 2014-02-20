(function($) {

    $.fn.showlert = function(message, options) {

        if (!options && message && typeof (message) == 'object') {
            options = message;
            message = null;
        } else if (!options && message) {
            options = {};
        }

        ops = $.extend({title: null, message: message, type: "info", dismissable: true, dismissAfter: false}, options);

        alert = $('<div class="alert alert-' + ops.type + ' ' + (ops.dismissable ? 'alert-dismissable' : '') + '"></div>');
        if (ops.dismissable)
            $('<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>').appendTo(alert);
        if (ops.title) {
            $('<strong>' + ops.title + '</strong>&nbsp;').appendTo(alert);
        }
        $('<span>' + ops.message + '</span>').appendTo(alert);

        return this.each(function() {
            a2 = alert.clone(true);
            $(this).prepend(a2);
            if (ops.dismissAfter && !isNaN(ops.dismissAfter)) {
                setTimeout(function() {
                    $(a2).alert("close");
                }, ops.dismissAfter)
            }
        });
    };

})(jQuery);