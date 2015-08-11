var options = {
    dataType: 'json',
    type: 'post',
    success: process_validation,
    beforeSubmit: function () {
        $('#invsent').remove();
    }
};
$('#invite-form').ajaxForm(options);

function process_validation(data) {
    if (data.result === 'ok') {
        $('#invite-form').append('<div id="invsent">invitation sent!</div>');
    } else {
        alert(data.message);
    }
}

$('.as_administrator').on('click', function () {
    if ($(this).prop('checked')) {
        aggressive_message('Setting as administrator.');
        $.ajax({
            url: site_url + 'account/participant/as_administrator/' + $(this).val(),
            cache: false,
            success: function (result) {
                if (result !== "1") {
                    alert('Error trying to setting as administrator. Please try again later.');
                    window.location = document.URL;
                }
                hide_aggressive_message();
            }
        });
    } else {
        aggressive_message('Setting as participant.');
        $.ajax({
            url: site_url + 'account/participant/as_participant/' + $(this).val(),
            cache: false,
            success: function (result) {
                if (result !== "1") {
                    alert('Error trying to setting as participant. Please try again later.');
                    window.location = document.URL;
                }
                hide_aggressive_message();
            }
        });
    }
});

$('.activation').on('click', function () {
    aggressive_message('Changing user activation.');
    $.ajax({
        url: site_url + 'account/participant/activation/' + $(this).val(),
        cache: false,
        success: function (result) {
            if (result !== "1") {
                alert('Error changing user activation. Please try again later.');
                window.location = document.URL;
            }
            hide_aggressive_message();
        }
    });
});