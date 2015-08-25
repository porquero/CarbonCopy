var options = {
	dataType: 'json',
	type: 'post',
	beforeSubmit: aggressive_message,
	success: process_validation
};
$('#reset_password_form').ajaxForm(options);

function process_validation(data) {
	if (data.result === 'ok') {
		window.location = site_url;
	} else {
		alert(data.message);
	}
}
