// Manage participants.
(function () {
    var manage_participants = {
        props: {},
        init: function (props) {
            this.props = $.extend({}, this.props, props);
            return this;
        },
        load_form: function (url) {
            $this = this;
            var div = $('#manage_participants_div');
            div.load(url, function () {
                div.show();
                var options = {
                    dataType: 'json',
                    type: 'post',
                    success: $this.process_validation
                };
                $('#manage_participants_form').ajaxForm(options);
            });
        },
        process_validation: function (data) {
            switch (data.result) {
                case 'canceled':
                    break;
                case 'ok':
                    $('#participants ul').remove();
                    $('#participants').append(data.message);
                    break;
                default:
                    hide_aggressive_message();
                    alert(data.message);
                    break;
            }
            $('#manage_participants_div').hide();
        }
    };

    // Para instanciar y ejecutar constructor.
    new_manage_participants = function (props) {
        var REL_OBJ_NAME = Object.create(manage_participants);
        return REL_OBJ_NAME.init(props);
    };
})();

var manage_participants_i = new_manage_participants();
$('.man-lst').on('click', function (e) {
    manage_participants_i.load_form($(this).prop('href'));
    e.preventDefault();

    return false;
});
$('#delegate').on('click', '.pp', function () {
    $('#pa:checked').attr('checked', false);
});
$('#delegate').on('click', '#pa', function () {
    $('.pp:checked').attr('checked', false);
});

// Search
(function () {
    var search = {
        props: {},
        no_search: [9, 13, 16, 17, 18, 19, 20, 33, 34, 35, 36, 37, 38, 39, 45, 91, 92, 93, 112, 113, 114, 115, 116, 117, 118, 119, 120, 121, 122, 123, 144, 145],
        init: function (props) {
            this.props = $.extend({}, this.props, props);
            return this;
        },
        go: function (e, q) {
            if (e.keyCode === 40) {
                $('#search_results a:first').focus();

                return false;
            }

            if (e.keyCode === 27) {
                $('#search').val('');
                $('#search').blur();
                $('#search_results').hide();

                return false;
            }

            if (this.no_search.indexOf(e.keyCode) > -1) {
                return false;
            }

            if (q.length > 1) {
                $.ajax({
                    url: site_url + 'cc/search/go/',
                    data: {'q': q},
                    success: function (result) {
                        if (result.length > 0) {
                            $('#search_results').html(result);
                            $('#search_results').show();
                        } else {
                            $('#search_results').hide();
                        }
                    }
                });
            } else {
                $('#search_results').hide();
            }
        },
        nav_result: function (e) {
            if (e.keyCode === 38) {
                e.preventDefault();
                el = $('#search_results a:focus');

                if (el.is(':first-child')) {
                    $('#search').focus();
                }

                el.parent().prev().find('a:first').focus();
            }

            if (e.keyCode === 40) {
                e.preventDefault();
                el = $('#search_results a:focus');

                el.parent().next().find('a:first').focus();
            }

            return false;
        }
    };

    // Para instanciar y ejecutar constructor.
    new_search = function (props) {
        var REL_OBJ_NAME = Object.create(search);
        return REL_OBJ_NAME.init(props);
    };
})();

var search = new_search();
$('#search').on('keyup', function (e) {
    search.go(e, $(this).val());
});
$('#search_results').on('keydown', 'a', function (e) {
    search.nav_result(e);
});

$('#move_context').on('click', function (e) {
    $this = $(this);
    var div = $('#move_form');
    div.load($this.prop('href'), function () {
        div.show();

        $('body').on('click', function () {
            div.hide();
        });
    });

    e.preventDefault();

    return false;
});

$('#delete_context').on('click', function (e) {
    if (confirm("Are you sure to delete this context?") === true) {
        aggressive_message();
        return true;
    } else {
        e.preventDefault();
        return false;
    }
});

$('#delete_topic').on('click', function (e) {
    if (confirm("Are you sure to delete this topic?") === true) {
        aggressive_message();
        return true;
    } else {
        e.preventDefault();
        return false;
    }
});

$('.focus').focus();

$('#date_line li').hover(function () {
    $(this).find('ul').stop(true, true).slideDown();
}, function () {
    $(this).find('ul').stop(true, true).slideUp();
});

$('#b_task').on('click', function () {
    aggressive_message();

    var status = $(this).data('status');
    var context = $(this).data('context');

    $.ajax({
        url: site_url + 'cc/topic/open_close/',
        type: 'GET',
        data: {'status': status, 'context': context},
        success: function (result) {
            if (result !== 'ok') {
                alert(result);
            }
            else {
                window.location = document.URL;
            }
        }
    });

});

function aggressive_message(message) {
    if (message !== undefined && typeof message === 'string') {
        $('#aggressive_message div span').text(message);
    }
    $('#aggressive_message').css('display', 'table');
}

function hide_aggressive_message() {
    $('#aggressive_message').fadeOut();
}

$(function () {
    $(document).bind('keydown', 'shift+7', function () {
        $('#search').focus().select();
        return false;
    });
    $('div.nicEdit-main').bind('keydown', 'shift+7', function (e) {
        e.stopPropagation();
    });
    $(document).bind('keydown', 'alt+c', function () {
        document.location = site_url + 'cc/user/login_form';
        return false;
    });
    $(document).bind('keydown', 'alt+x', function () {
        document.location = site_url + 'cc/user/logout';
        return false;
    });
});
