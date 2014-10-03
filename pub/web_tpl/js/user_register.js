var options = {
	dataType: 'json',
	type: 'post',
	success: process_validation
};
$('#register_form').ajaxForm(options);

function process_validation(data) {
	if (data.result === 'ok') {
		window.location = site_url + 'cc/user/register_ok/' + data.message;
	} else {
		alert(data.message);
	}
}
