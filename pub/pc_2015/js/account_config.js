var options = {
    dataType: 'json',
    type: 'post',
    beforeSerialize: function () {
        nicEditors.findEditor('home_info').saveContent();
    },
    beforeSubmit: aggressive_message,
    success: process_validation
};
$('#config_form').ajaxForm(options);

function process_validation(data) {
    if (data.result === 'ok') {
        window.location = site_url;
    } else {
        hide_aggressive_message();
        alert(data.message);
    }
}

if ($('#home_info').length) {
    new nicEditor({fullPanel: true}).panelInstance('home_info', {hasPanel: true});
}

$('.component_activation').on('click', function () {
    if ($(this).prop('checked')) {
        aggressive_message('Activating.');
        $.ajax({
            url: site_url + 'extends/component/activate/' + $(this).val(),
            success: function (result) {
                if (result !== "1") {
                    alert('Error trying to activate component. Please try again later.');
                    window.location = document.URL;
                }
                hide_aggressive_message();
            }
        });
    } else {
        aggressive_message('Desactivating.');
        $.ajax({
            url: site_url + 'extends/component/desactivate/' + $(this).val(),
            success: function (result) {
                if (result !== "1") {
                    alert('Error trying to desactivate component. Please try again later.');
                    window.location = document.URL;
                }
                hide_aggressive_message();
            }
        });
    }
});

$('.section_activation').on('click', function () {
    if ($(this).prop('checked')) {
        aggressive_message('Activating.');
        $.ajax({
            url: site_url + 'extends/section/activate/' + $(this).val(),
            success: function (result) {
                if (result !== "1") {
                    alert('Error trying to activate section. Please try again later.');
                    window.location = document.URL;
                }
                hide_aggressive_message();
            }
        });
    } else {
        aggressive_message('Desactivating.');
        $.ajax({
            url: site_url + 'extends/section/desactivate/' + $(this).val(),
            success: function (result) {
                if (result !== "1") {
                    alert('Error trying to desactivate section. Please try again later.');
                    window.location = document.URL;
                }
                hide_aggressive_message();
            }
        });
    }
});