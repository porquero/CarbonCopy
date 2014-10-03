var options = {
	dataType: 'json',
	type: 'post',
	success: process_validation
};
$('#reply_topic').ajaxForm(options);

/**
 * 
 * @param json data response
 */
function process_validation(data) {
	if (data.result === 'ok') {
		window.location = site_url + 'cc/topic/resume/' + $('#context').val();
	} else {
		alert(data.message);
	}
}
$('textarea#message').ckeditor();