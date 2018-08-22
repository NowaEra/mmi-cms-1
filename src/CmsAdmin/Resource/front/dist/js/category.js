var CMS = CMS || {};
var openedWindow = {closed: true};
var followScroll = false;

var $elems = $("html, body");
var delta = 0;

$(document).on("mousemove", function (e) {
    if (followScroll) {
        var h = $(window).height();
        var y = e.clientY - h / 2;
        delta = y * 0.1;
    } else {
        delta = 0;
    }
});

(function f() {
    if (delta) {
        $elems.scrollTop(function (i, v) {
            return v + delta;
        });
    }
    requestAnimationFrame(f);
})();


CMS.category = function () {
    "use strict";
    var that = {},
            initSortableWidgets,
            initNewWindowButtons,
            initWidgetButtons,
            initCategoryChange,
            reloadWidgets;

    initSortableWidgets = function () {
        if ($('#widget-list').length > 0) {
            $('#widget-list').sortable({
                start: function () {
                    followScroll = true;
                },
                stop: function () {
                    followScroll = false;
                },
                handle: '.handle-widget',
                update: function (event, ui) {
                    $.post(request.baseUrl + "/?module=cmsAdmin&controller=categoryWidgetRelation&action=sort&categoryId=" + $(this).attr('data-category-id'), $(this).sortable('serialize'),
                            function (result) {
                                if (result) {
                                    alert(result);
                                }
                            });
                }
            });
        }
    };

    initNewWindowButtons = function () {
        $('#categoryContentContainer').on('click', 'a.new-window', function () {
            if (openedWindow.closed) {
                openedWindow = window.open($(this).attr('href'), '', "width=" + ($(window).width() - 200) + ",height=" + ($(window).height() - 200) + ",left=150,top=150,toolbar=no,scrollbars=yes,resizable=no");
                return false;
            }
            openedWindow.focus();
            return false;
        });
    };

    initWidgetButtons = function () {
        $('#widget-list').on('click', '.delete-widget', function () {
            if (!window.confirm($(this).attr('title') + '?')) {
                return false;
            }
            $.get($(this).attr('href'));
            $(this).parent('div').parent('li').remove();
            return false;
        });
        $('#widget-list').on('click', '.toggle-widget', function () {
            var state = parseInt($(this).data('state'));
            state++;

            if (state > 1) {
                state = 0;
            }

            $.get($(this).attr('href'), {'state': state});
            if (state === 1) {
                $(this).children('i').attr('class', 'fa fa-2 fa-eye pull-right');
                $(this).attr('title', 'aktywny');
            } else {
                $(this).children('i').attr('class', 'fa fa-2 fa-eye-slash pull-right');
                $(this).attr('title', 'ukryty');
            }
            $(this).data('state', state);
            return false;
        });
    };

    initCategoryChange = function () {
        $('form.cmsadmin-form-category').on('change', '#cmsadmin-form-category-cmsCategoryTypeId', function () {
            $('#cmsadmin-form-category-submit-top').val('type');
            $('#cmsadmin-form-category-submit-top').click();
            return false;
        });
    };

    reloadWidgets = function () {
        $.get(request.baseUrl + "/?module=cmsAdmin&controller=categoryWidgetRelation&action=preview&categoryId=" + $('#widget-list-container').attr('data-category-id'), function (data) {
            $('#widget-list-container').html(data);
            initSortableWidgets();
            initWidgetButtons();
            if (window.MathJax !== undefined) {
                window.MathJax.Hub.Queue(["Typeset", MathJax.Hub]);
            }
        });
    };

    that.reloadWidgets = reloadWidgets;

    var dataTabRestore = function () {
        var itemName = 'categoryActiveTab-' + $('ul.nav-tabs').attr('data-id');
        $('ul.nav-tabs > li').on('click', 'a', function (evt) {
            sessionStorage.setItem(itemName, $(this).attr("href"));
        });
        var currentTab = sessionStorage.getItem(itemName);
        if (currentTab) {
            $('ul.nav-tabs > li > a[href$="' + currentTab + '"]').click();
            return;
        }
        $('ul.nav-tabs > li > a[href$="#settings"]').click();
    };
    dataTabRestore();
    initSortableWidgets();
    initNewWindowButtons();
    initWidgetButtons();
    initCategoryChange();
    return that;
};

$(document).ready(function () {
    "use strict";
    CMS.category();
});