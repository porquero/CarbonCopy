<?php

function form_button($data = '', $content = '', $type = 'button', $extra = '')
{
	$defaults = array('name' => (( ! is_array($data)) ? $data : ''), 'type' => $type);

	if (is_array($data) AND isset($data['content'])) {
		$content = $data['content'];
		unset($data['content']); // content is not an attribute
	}

	return "<button " . _parse_form_attributes($data, $defaults) . $extra . ">" . $content . "</button>";
}
