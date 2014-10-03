var options = {
	dataType: 'json',
	type: 'post',
	success: process_validation
};
$('#config_form').ajaxForm(options);

function process_validation(data) {
	if (data.result === 'ok') {
		window.location = site_url;
	} else {
		alert(data.message);
	}
}

$('textarea#home_info').ckeditor();