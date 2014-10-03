var options = {
	dataType: 'json',
	type: 'post',
	success: process_validation,
	beforeSubmit: function() {
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
