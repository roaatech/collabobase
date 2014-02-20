(function($) {
    $.showdal = function(options) {
        ops = $.extend({title: null, body: '', buttons: null, show: true, keyboard: true, copy: false}, options);

        total = $('<div class="modal fade" style="display: none;" aria-hidden="true"></div>');
        modal = $('<div class="modal-dialog"></div>').appendTo(total);
        content = $('<div class="modal-content"></div>');
        header = $('<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button><h4 class="modal-title">' + ops.title + '</h4></div>');
        footer = $('<div class="modal-footer"></div>');

        if (!ops.title) {

            header = null;

        }

        if (typeof (ops.body) !== 'object') {
            body = $('<div class="modal-body">' + ops.body + '</div>');
        } else {
            body = $('<div class="modal-body"></div>');
            if (ops.copy) {
                body.append($(ops.body).clone());
            } else {
                body.append(ops.body);
            }
        }

        if (!ops.buttons) {

            footer = null;

        } else if (typeof (ops.buttons) == 'string') {

            $(ops.buttons).appendTo(footer);

        } else {

            for (index in ops.buttons) {

                button = ops.buttons[index];

                buttonOptions = {};
                defaultButtonOptions = {
                    "action": null,
                    "title": index,
                    "class": "btn",
                    "attributes": {}
                };

                if (isFunction(button)) {

                    buttonOptions = $.extend({}, defaultButtonOptions);

                } else if (typeof (button) == 'string') {

                    buttonOptions = $.extend({}, defaultButtonOptions, {"onclick": button});

                } else {

                    buttonOptions = $.extend(defaultButtonOptions, button);

                }

                attributes = "";
                for (attribute in buttonOptions.attributes) {
                    attributes += ' ' + attribute + '="' + buttonOptions.attributes[attribute] + '"';
                }

                btn = '<button type="button" class="btn ' + buttonOptions.class + '" ' + attributes + '>' + buttonOptions.title + '</button>';
                $(btn).click(buttonOptions.action).appendTo(footer);

            }

        }

        content.append(header);
        content.append(body);
        content.append(footer);
        modal.append(content);

        $('body').append(total);

        $(total).modal(ops).on('hidden.bs.modal', function(e) {
            $(this).remove();
        });


        if (ops.hasOwnProperty('onShow')) {
            $(total).on('show.bs.modal', ops.onShow);
        }
        if (ops.hasOwnProperty('onShown')) {
            $(total).on('shown.bs.modal', ops.onShown);
        }

        return total;

    };

    $.fn.showdal = function(options) {
        if (typeof (options) === 'string') {
            switch (options) {
                case "close":
                case "hide":
                    return this.each(function() {
                        modal = $(this).filter(".modal");
                        if (modal.length == 0)
                            modal = $(this).closest(".modal");
                        modal.modal("hide");
                    });
                    break;
            }
        } else {
            return this.each(function() {
                ops = $.extend({title: $(this).attr('title')}, options, {"body": this});
                $.showdal(ops);
            });
        }
    };

})(jQuery);

function isFunction(functionToCheck) {
    var getType = {};
    return functionToCheck && getType.toString.call(functionToCheck) === '[object Function]';
}