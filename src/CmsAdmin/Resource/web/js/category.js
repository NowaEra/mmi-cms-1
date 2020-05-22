var CMS = CMS || {};
var openedWindow = { closed: true };
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

CMS.category = function () {
    "use strict";
    var that = {},
        initSectionFolding,
        initSortableWidgets,
        initWidgetButtons,
        initCategoryChange;

    initSectionFolding = function () {
        $('a.fold').click(function () {
            $(this).parents('div.boxSection').addClass('folded');
        });
        $('a.unfold').click(function () {
            $(this).parents('div.boxSection').removeClass('folded');
        });
    };

    initSortableWidgets = function () {
        if ($('.widget-list').length > 0) {
            $('.widget-list').sortable({
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

    initWidgetButtons = function () {
        $('.widget-list').on('click', '.toggle-widget', function () {
            var state = parseInt($(this).data('state'));
            state++;
            if (state > 1) {
                state = 0;
            }
            $.get($(this).attr('href'), { 'state': state });
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
        $('form.cmsadmin-form-categoryform').on('change', '#cmsadmin-form-categoryform-template', function () {
            $('#cmsadmin-form-categoryform-submit-top').val('type');
            $('#cmsadmin-form-categoryform-submit-top').click();
            return false;
        });
    };

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
        $('ul.nav-tabs > li > a[href$="#basic"]').click();
    };
    dataTabRestore();
    initSortableWidgets();
    initWidgetButtons();
    initCategoryChange();
    initSectionFolding();
    return that;
};

$(document).ready(function () {
    "use strict";
    CMS.category();
});