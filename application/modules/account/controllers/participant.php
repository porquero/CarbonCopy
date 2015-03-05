<?php

if ( ! defined('BASEPATH'))
	exit('No direct script access allowed');

/**
 * Manage account participant.
 *
 * @package CarbonCopy
 * @subpackage account
 * @author porquero
 */
class participant extends MX_Controller {

	/**
	 * Get all participants from account
	 *
	 * @return array
	 */
	function list_for_resume()
	{
		$this->load->module('file/read');

		$context = '_accounts/' . Modules::run('file/misc/final_slash', $this->session->userdata('current_account')) . '_participants/';
		$participants = $this->read->files($context);
		$result = array();

		foreach ($participants as $participant) {
			$result[] = $this->read->json_content($participant);
		}

		return $result;
	}

	/**
	 * Get participants for context.
	 * 
	 * @param string $context
	 * @return array
	 */
	public function list_for_context($context, $kind = 'context')
	{
		if (empty($context)) {
			die('Empty context!');
		}

		$context_info = Modules::run('file/read/json_content', Modules::run('file/misc/final_slash', $context) . 'info_' . $kind . '.json');
		$result = array();

		// TODO: add type validation to avoid server lock!
		if ( ! is_array($context_info)) {
			Plogger::var_dump($context_info);
			die("Fix this!. [{$context}]" . __FILE__ . __LINE__);
		}

		if (preg_match('/\,/', $context_info['participants']) === 1) {
			$participants = explode(',', $context_info['participants']);

			foreach ($participants as $participant) {
				if (empty($participant)) {
					continue;
				}
				$result[]['info']['id'] = $participant;
			}
		}
		elseif ($context_info['participants'] === '') {
			$context = preg_replace('/\/_topics/', '', $context);
			$context = preg_replace('/\/[a-z0-9-]*$/', '', $context);

			if (preg_match('/\/contexts$/', $context) === 1) {
				return $this->list_for_resume();
			}

			return $this->list_for_context($context);
		}
		else {
			$result[]['info']['id'] = $context_info['participants'];
		}

		return $result;
	}

	/**
	 * Get participants for account
	 *
	 * @return type
	 */
	public function list_for_account()
	{
		$this->load->module('file/read');
		$participants = $this->read->files_basename(_INC_ROOT . '_accounts/' . $this->session->userdata('current_account') . '/_participants');

		return $participants;
	}

	/**
	 * Get account participants and generate select html form
	 * 
	 * @param string $id_name
	 * @return string html output
	 */
	public function html_select_for_account($id_name, $select = NULL)
	{
		$participants = $this->list_for_account();
		$output = '<select name="' . $id_name . '" id="' . $id_name . '">';
		$select = $select === NULL ? connected_user() : $select;

		foreach ($participants as $participant) {
			$participant = preg_replace('/\.json/', '', $participant);
			$selected = $participant == $select ? 'selected' : '';
			$output .= '<option value="' . $participant . '" ' . $selected . '>' . $participant . '</option>';
		}
		$output .= '</select>';

		return $output;
	}

	/**
	 * Get context participants and generate select html form
	 *
	 * @param string $id_name
	 *
	 * @return string html output
	 */
	public function html_select_for_context($context, $id_name, $select = NULL, $is_topic = FALSE)
	{
		if (empty($context)) {
			die('Contexto vacío!');
		}

		$this->load->module('file/misc');
		$select = $select === NULL ? connected_user() : $select;

		$context = "_accounts/{$this->session->userdata('current_account')}/contexts/{$this->misc->unslug($context)}";
		$participants = $this->list_for_context($is_topic ? topic_real_path($context) : $context, $is_topic ? 'topic' : 'context');
		$output = '<select name="' . $id_name . '" id="' . $id_name . '">';

		foreach ($participants as $participant) {
			$participant = trim($participant['info']['id']);
			$selected = $participant == $select ? 'selected' : '';
			$output .= '<option value="' . $participant . '" ' . $selected . '>' . $participant . '</option>';
		}
		$output .= '</select>';

		return $output;
	}

	/**
	 * Get account participants and generate checkboxs html form
	 *
	 * @param type $id_name
	 * @return type
	 */
	public function html_checkbox_for_account($id_name, $check = array())
	{
		$participants = $this->list_for_account();
		$checked_all = count($check) > 0 ? '' : 'checked="checked"';
		$inherit = lang('inherit');
		$output = <<<PQR
<div class="checkbox">
        <label><input type="checkbox" value="all" {$checked_all} name="{$id_name}" id="{$id_name}" /> <span>{$inherit}</span></label>
PQR;

		foreach ($participants as $participant) {
			$participant = preg_replace('/\.json/', '', $participant);

			if (connected_user() === $participant) {
				continue;
			}

			$checked = in_array($participant, $check) ? 'checked' : '';
			$output .= '<label><input type="checkbox" value="' . $participant . '" name="' . $id_name . '[]" '
				. $checked . '/> <span>' . $participant . '</span></label>';
		}
		$me = lang('me');
		$output .=<<<PQR
		<label><input type="checkbox" checked disabled /> <span>{$me}</span></label>
		<div class="clear"></div>
</div>
PQR;

		return $output;
	}

	/**
	 * Get context participants and generate checkboxs html form
	 *
	 * @param string $context
	 * @param string $id_name
	 *
	 * @return string
	 */
	public function html_checkbox_for_context($context, $id_name, $for_check = array(), $is_topic = FALSE)
	{
		if (empty($context)) {
			die(lang('empty_context'));
		}

		$this->load->module('file/misc');

		$context = "_accounts/{$this->session->userdata('current_account')}/contexts/{$this->misc->unslug($context)}";
		$participants = $this->list_for_context($is_topic ? topic_real_path($context) : $context, $is_topic ? 'topic' : 'context');
		$checked_all = count($for_check) > 0 ? '' : 'checked="checked"';
		$inherit = lang('inherit');
		$output = <<<PQR
<div class="checkbox">
        <label><input type="checkbox" id="pa" value="all" {$checked_all} name="{$id_name}" id="{$id_name}" /> <span>{$inherit}</span></label>
PQR;

		foreach ($participants as $participant) {
			$participant = trim($participant['info']['id']);

			if (connected_user() === $participant) {
				continue;
			}
			$checked = in_array($participant, $for_check) ? 'checked' : '';
			$output .= '<label><input type="checkbox" class="pp" value="' . $participant . '" name="' . $id_name . '[]" ' . $checked
				. ' /> <span>' . $participant . '</span></label>';
		}
		$me = lang('me');
		$output .=<<<PQR
		<label><input type="checkbox" checked disabled /> <span>{$me}</span></label>
		<div class="clear"></div>
</div>
PQR;

		return $output;
	}

	/**
	 * User profile
	 *
	 * @param string $username
	 */
	public function profile($username, $menu_act = 'all', $ts_date = NULL)
	{
		is_connected();

		$this->load->module('cc/timeline');

		$file = _INC_ROOT . '_accounts/' . $this->session->userdata('current_account') . '/_participants/' . $username . '.json';
		$info = $this->info($username);

		$this->timeline->menu_act[$menu_act] = 'act';
		$participant_id = $menu_act === 'to_me' ? connected_user() : NULL;

		if (is_file($file)) {
			$data = json_decode(file_get_contents($file));
			$lang_username = lang('username');
			$lang_email = lang('email');
			$detail = <<<PQR
<ul>
	<li><b>{$lang_username}</b> {$data->info->id}</li>
	<li><b>{$lang_email}</b> {$info->email}</li>
</ul>
PQR;
			$tl_date = is_null($ts_date) ? '' : ': ' . account_date_format($ts_date);

			$this->tpl->variables(
				array(
						'title' => $info->name,
						'tl_title' => lang('user_timeline') . $tl_date,
						'url_all' => '',
						'url_to_me' => '',
						'description' => $detail,
						'participants' => Modules::run('account/participant/list_for_resume'),
						'breadcrumb' => create_breadcrumb(''),
						'timeline' => $this->timeline->get_for_participant($username, TRUE, $participant_id, $ts_date),
						'menu_act' => $this->timeline->menu_act,
						'url_all' => site_url('/account/participant/profile/' . $username),
						'url_to_me' => site_url('/account/participant/profile/' . $username . '/to_me'),
						'url_ts' => site_url('/account/participant/profile/' . $username . '/' . $menu_act),
			));
			$this->tpl->section('_view', 'profile.phtml');
			$this->tpl->section('_sidebar', '_sidebar_profile.phtml');
			$this->tpl->load_view(_TEMPLATE);
		}
		else {
			echo lang('participant_not_found');
		}
	}

	/**
	 * LIst all people for account
	 */
	public function all_people()
	{
		is_connected();
		$this->load->helper('form');
		$this->load->model('cc/m_user');

		$participants = $this->list_for_account();
		$where_in = array();

		foreach ($participants as $participant) {
			$where_in[] = preg_replace('/\.json/', '', $participant);
		}

		$this->tpl->variables(
			array(
					'title' => lang('people'),
					'footer' => js_tag('pub/web_tpl/js/people.js'),
					'description' => lang('all_we_are'),
					'info_participants' => $this->m_user->where_in($where_in),
					'breadcrumb' => create_breadcrumb(''),
		));
		$this->tpl->section('_view', 'all_people.phtml');
		$this->tpl->section('_sidebar', '_sidebar_people.phtml');
		$this->tpl->load_view(_TEMPLATE);
	}

	/**
	 * Try to Invite user by email
	 */
	public function invite()
	{
		$this->load->library('form_validation');
		$this->load->helper(array('form', 'language'));
		$this->load->language('cc_form_validation');
		$this->form_validation->set_error_delimiters('', '');

		$field = 'email';
		$rules = 'trim|required|min_length[5]|max_length[254]|valid_email';
		$this->form_validation->set_rules($field, 'lang:' . $field, $rules);

		if ($this->form_validation->run() === FALSE) {
			echo json_encode(array(
					'result' => 'fail',
					'message' => validation_errors()
			));

			return;
		}

		// Send invitation.
		$this->load->model('cc/m_user');
		$this->load->library('email');
		$this->load->module('file/write');

		// Check if user belongs to account.
		if ($this->m_user->email_exists($this->input->post('email'))
			AND $this->m_user->has_account($this->m_user->username($this->input->post('email')), $this->session->userdata('current_account'))) {
			echo json_encode(array(
					'result' => 'fail',
					'message' => lang('already_belongs')
			));

			return;
		}

		$user_data = $this->m_user->data(connected_user());
		$name = explode(' ', $user_data->name);

		// Avoid add unwanted user.
		$hash = uniqid();
		$this->write->archive(_INC_ROOT . '_accounts/' . $this->session->userdata('current_account') . '/_invitations/' . $hash, $this->input->post('email'));

		$mail_body = nl2br(sprintf(lang('invitation_mail'), $name[0], site_url(), $this->session->userdata('current_account'), $hash, site_url_ws()));

		// TODO Load email from configuration
		$this->email->from('noreply@carboncpm.com', 'CarbonCopy');
		$this->email->to($this->input->post('email'));

		$this->email->subject(sprintf(lang('invitation_subject'), $name[0]));
		$this->email->message($mail_body);
		$this->email->set_mailtype('html');

		$this->email->send();

		echo json_encode(array(
				'result' => 'ok',
				'message' => ''
		));
	}

	/**
	 * Return user info
	 *
	 * @param string $username
	 *
	 * @return object
	 */
	public function info($username)
	{
		$this->load->model('cc/m_user');

		return $this->m_user->data($username);
	}

	/**
	 * Validate if participant belongs to account
	 *
	 * @param string $context
	 * @param string $user
	 *
	 * @return type
	 */
	public function belongs_to_account($user)
	{
		$participants = Modules::run('account/participant/list_for_resume');

		$plist = '';
		foreach ($participants as $participant) {
			$plist .= $participant['info']['id'] . '|';
		}

		return strstr($plist, $user) !== FALSE;
	}

	/**
	 * Participant config for current account.
	 */
	public function config_form()
	{
		is_connected();

		$this->load->helper('form');
		$this->load->model('cc/m_user');

		$user_config = Modules::run('file/read/json_content', "_accounts/{$this->session->userdata('current_account')}/_participants/" . connected_user() . ".json");
		$user_data = $this->m_user->data(connected_user());

		$this->tpl->variables(
			array(
					'title' => 'Account config',
					'description' => '',
					'user_language' => $user_config['info']['language'],
					'footer' => js_tag('pub/js/jquery.form.js') . js_tag('pub/web_tpl/js/participant_config.js'),
					'language' => array(
							'english' => 'english',
							'spanish' => 'spanish',
					),
					'user_data' => $user_data,
					'msg_type' => isset($msg_type) ? $msg_type : '',
					'msg' => isset($msg) ? $msg : '',
		));

		$this->tpl->section('_view', 'config_form.phtml');
		$this->tpl->load_view(_TEMPLATE);
	}

	/**
	 * Save user config data.
	 * 
	 * @return string
	 */
	public function config()
	{
		is_connected();

		$this->load->module('file/misc');

		if ($this->input->post('submit') === 'cancel') {
			$result = array(
					'result' => 'ok',
					'message' => lang('canceled')
			);
			if ($this->input->is_ajax_request()) {
				echo json_encode($result);
				return;
			}
			else {
				return $result;
			}
		}

		$result = $this->validate_form();

		if ($result['result'] === 'ok') {

			// Save user data.
			$this->load->model('cc/m_user');
			$user_data = $this->m_user->data(connected_user());
			$update_data['name'] = $this->input->post('name');

			// Validating if email already exists in database.
			if ($this->input->post('email') !== $user_data->email) {
				if ($this->m_user->email_exists($this->input->post('email'))) {
					$result = array(
							'result' => 'fail',
							'message' => lang('email_exists')
					);

					if ($this->input->is_ajax_request()) {
						echo json_encode($result);
					}
					else {
						return $result;
					}

					// Only for break!
					return;
				}
				else {
					$update_data['email'] = $this->input->post('email');
				}
			}

			// Save password if not empty.
			if ($this->input->post('password') !== '') {
				$update_data['password'] = md5($this->input->post('password'));
			}

			$this->m_user->update(connected_user(), $update_data);

			// Save participant data.
			$this->load->module('file/write');

			// Save info data.
			$participant_config_path = "_accounts/{$this->session->userdata('current_account')}/_participants/" . connected_user() . ".json";
			$participant_info = Modules::run('file/read/json_content', $participant_config_path);
			$participant_info = array(
					'info' => array(
							'id' => $participant_info['info']['id'],
							'language' => $this->input->post('language'),
							'type' => $participant_info['info']['type'],
					)
			);

			$res = $this->write->archive($participant_config_path, json_encode($participant_info)) === TRUE ? 'ok' : 'fail';
			$this->session->set_userdata('user_info', Modules::run('file/read/json_content', $participant_config_path));

			$message = '';
			if ($res == 'fail') {
				//@todo Error number is manual now. Create secuencial system error codes next.
				$message = 'Sytem error number #7732143. Please contact us and send the error number.';
			}
			else {
				$this->session->set_flashdata('msg', lang('config_saved'));
				$this->session->set_flashdata('msg_type', 'msg_ok');
			}

			$result = array(
					'result' => $res,
					'message' => $message,
			);
		}
		else {
			$message = validation_errors();
			$result = array(
					'result' => 'fail',
					'message' => $message
			);
		}

		if ($this->input->is_ajax_request()) {
			echo json_encode($result);
		}
		else {
			return $result;
		}
	}

	/**
	 * Validate config form.
	 * 
	 * @return array
	 */
	public function validate_form()
	{
		$this->load->library('form_validation');
		$this->load->helper(array('form'));
		$this->load->language('cc_form_validation');
		$this->form_validation->set_error_delimiters('', '');
		$result['result'] = 'ok';

		$field = 'name';
		$rules = 'trim|required|min_length[7]|max_length[75]';
		$this->form_validation->set_rules($field, 'lang:' . $field, $rules);

		$field = 'email';
		$rules = 'trim|required|min_length[5]|max_length[254]|valid_email';
		$this->form_validation->set_rules($field, 'lang:' . $field, $rules);

		$field = 'language';
		$rules = 'trim|required';
		$this->form_validation->set_rules($field, 'lang:' . $field, $rules);

		if ($this->form_validation->run() === FALSE) {
			$message = validation_errors();
			$result = array(
					'result' => 'fail',
					'message' => $message
			);
		}

		return $result;
	}

	/**
	 * Set participant as administrator.
	 *
	 * @param string $user
	 */
	public function as_administrator($user)
	{
		is_connected('administrator');

		$this->load->module('file/write');

		// Save info data.
		$participant_config_path = "_accounts/{$this->session->userdata('current_account')}/_participants/" . connected_user() . ".json";
		$participant_info = Modules::run('file/read/json_content', $participant_config_path);
		$participant_info = array(
				'info' => array(
						'id' => $participant_info['info']['id'],
						'language' => $participant_info['info']['language'],
						'type' => 'administrator',
				)
		);

		$res = $this->write->archive($participant_config_path, json_encode($participant_info)) === TRUE ? '1' : 'fail';
		$this->session->set_userdata('user_info', Modules::run('file/read/json_content', $participant_config_path));

		if ($this->input->is_ajax_request()) {
			echo $res;
		}
		else {
			return $res;
		}
	}

	/**
	 * Set participant as participant.
	 *
	 * @param string $user
	 */
	public function as_participant($user)
	{
		is_connected('administrator');

		$this->load->module('file/write');

		// Save info data.
		$participant_config_path = "_accounts/{$this->session->userdata('current_account')}/_participants/" . connected_user() . ".json";
		$participant_info = Modules::run('file/read/json_content', $participant_config_path);
		$participant_info = array(
				'info' => array(
						'id' => $participant_info['info']['id'],
						'language' => $participant_info['info']['language'],
						'type' => 'participant',
				)
		);

		$res = $this->write->archive($participant_config_path, json_encode($participant_info)) === TRUE ? '1' : 'fail';
		$this->session->set_userdata('user_info', Modules::run('file/read/json_content', $participant_config_path));

		if ($this->input->is_ajax_request()) {
			echo $res;
		}
		else {
			return $res;
		}
	}
}
