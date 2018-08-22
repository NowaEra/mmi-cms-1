/*jslint unparam: true */
/*global $, document, window, request */

jQuery.fn.putCursorAtEnd = function () {
    return this.each(function () {
        $(this).focus();
        if (this.setSelectionRange) {
            var len = $(this).val().length * 2;
            this.setSelectionRange(len, len);
        } else {
            $(this).val($(this).val());
        }
    });
};

var CMS = CMS ? CMS : {};

CMS.grid = function () {
    "use strict";
    var initGridFilter,
            initGridOrder,
            initGridOperation;

    initGridFilter = function () {

        var stoptyping;
        var doFilter = true;
        $('table.grid').on('keyup', "th > div.field > .field", function (event) {
            if (event.which === 27) {
                return;
            }
            var field = $(this);
            clearTimeout(stoptyping);
            stoptyping = setTimeout(function () {
                if (field.val().length === 0 && !doFilter) {
                    doFilter = true;
                    return;
                }
                doFilter = true;
                filter(field);
            }, 500);
        });

        $('table.grid').on('change', "th > div.range > input", function () {
            let from = $(this).parent('div.range').children('input.from').val();
            let to = $(this).parent('div').children('input.to').val();
            let targetField = $(this).parent('div').parent('th').children('div.hidden').children('input');
            targetField.val(from + ';' + to);
            filter(targetField);
        });

        $('table.grid').on('change', "th > div.field > select.field", function () {
            filter($(this));
        });

        $('table.grid').on('input', "th > div.field > input.field", function () {
            if ($(this).val().length === 0) {
                if (doFilter === true) {
                    filter($(this));
                    doFilter = false;
                }
            }
        });

        function filter(field) {
            var filter = field.attr('name'),
                    value = field.val(),
                    fieldName = field.attr('name'),
                    gridId = field.parent('div').parent('th').parent('tr').parent('tbody').parent('table').attr('id');
            $.ajax({
                url: window.location,
                type: 'POST',
                data: {filter: filter, value: value},
                beforeSend: function () {
                    field.addClass('grid-loader');
                },
                success: function (data) {
                    $('#' + gridId).html(data);
                    let input = $('input[name=\'' + fieldName + '\']');
                    if (input.hasClass('hidden')) {
                        return;
                    }
                    input.putCursorAtEnd();
                }
            });
        }

    };

    initGridOrder = function () {
        //sortowanie grida
        $('table.grid').on('click', 'th > a.order', function () {
            var field = $(this).attr('href'),
                    gridId = $(this).parent('th').parent('tr').parent('tbody').parent('table').attr('id'),
                    method = $(this).attr('data-method');
            $.ajax({
                url: window.location,
                type: 'POST',
                data: {order: field, method: method},
                success: function (data) {
                    $('#' + gridId).html(data);
                }
            });
            return false;
        });
    };

    initGridOperation = function () {
        //akcja na zmianie checkboxa
        $('table.grid').on('change', 'td > div.checkbox > input.checkbox', function () {
            var id = $(this).attr('id').split('-');
            $.ajax({
                url: window.location,
                type: 'POST',
                data: {id: id[1], name: id[0], value: $(this).val(), checked: $(this).is(':checked')}
            });
        });
    };

    initGridFilter();
    initGridOrder();
    initGridOperation();
};

$(document).ready(function () {
    "use strict";
    CMS.grid();
});
