var options = {
	dataType: 'json',
	type: 'post',
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

$('textarea#home_info').ckeditor();