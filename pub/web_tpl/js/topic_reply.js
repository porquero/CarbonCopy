var options = {
	dataType: 'json',
	type: 'post',
	beforeSerialize: function () {
		nicEditors.findEditor('message').saveContent();
	},
	beforeSubmit: aggressive_message,
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
		hide_aggressive_message();
		alert(data.message);
	}
}
new nicEditor({fullPanel: true}).panelInstance('message', {hasPanel: true});