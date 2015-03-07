<?php

/**
 * Manage system users.
 *
 * @package CarbonCopy
 * @subpackage cc
 * @author Cristian Riffo <criffoh@gmail.com>
 *
 */
class user extends MX_Controller {

    /**
     * Register form
     *
     * @param string $invitation_hash Used to add user to invited account
     * 
     * @return type
     */
    public function register_form($invitation_hash = '') {
        // Only for not connected.
        if (connected_user() !== FALSE) {
            redirect();
            return;
        }

        $this->load->helper('form');
        $this->tpl->variables(
                array(
                    'title' => lang('register_new_user'),
                    'description' => lang('complete_form'),
                    'footer' => js_tag('pub/js/jquery.form.js') . js_tag('pub/web_tpl/js/user_register.js'),
                    'invitation_hash' => $invitation_hash,
        ));

        // User register disabled means make compatible extends with it.
        if (strlen($invitation_hash) > 0) {
            $this->tpl->section('_view', 'register_form.phtml');
        } else {
            $this->tpl->section('_view', 'register_form_disabled.phtml');
        }
        $this->tpl->load_view(_TEMPLATE);
    }

    /**
     * Try to create a new user.
     *
     * @return string json
     */
    public function register() {
        $result = $this->validate_register_form();

        if ($result['result'] === 'ok') {
            $this->load->model('m_user');

            $data = array(
                'username' => $this->input->post('username'),
                'name' => $this->input->post('name'),
                'email' => $this->input->post('email'),
                'password' => md5($this->input->post('password')),
            );

            // Add invited account to new user.
            if (strlen($this->input->post('invitation_hash')) > 0) {
                list($account, $hash) = explode('_', $this->input->post('invitation_hash'));
                $hash_path = _INC_ROOT . '_accounts/' . $account . '/_invitations/' . $hash;

                if (is_file($hash_path)) {
                    $data['accounts'] = $account;
                    $data['principal_account'] = $account;

                    $this->add_account($this->input->post('username'), $account);

                    unlink($hash_path);
                }
            }

            if ($this->create($data) === TRUE) {
                $res = 'ok';
                $message = md5($this->input->post('email')) . md5($this->input->post('password'));
                $this->load->library('email');
                $recipient = $this->input->post('email');
                $subject = 'CarbonCopy - ' . lang('email_validation');
                $message_mail = nl2br(sprintf(lang('email_validation_message'), $this->input->post('name'), site_url_ws()
                                , md5($this->input->post('password')) . md5($this->input->post('email'))));
                $this->email->from('noreply@carboncpm.com', 'CarbonCopy');
                $this->email->to($recipient);

                $this->email->subject($subject);
                $this->email->message($message_mail);

                $this->email->set_mailtype('html');
                $this->email->send();
            } else {
                $res = 'fail';
                $message = 'An error as occurred. Please try again.';
            }

            $result = array(
                'result' => $res,
                'message' => $message,
            );
        } else {
            $result = array(
                'result' => 'fail',
                'message' => validation_errors(),
            );
        }

        if ($this->input->is_ajax_request()) {
            echo json_encode($result);
        } else {
            return $result;
        }
    }

    /**
     * Register ok view.
     */
    public function register_ok($token) {
        $this->tpl->variables(
                array(
                    'title' => lang('registration_successful'),
                    'description' => lang('check_email'),
        ));
        $this->tpl->load_view(_TEMPLATE);
    }

    /**
     * Create a new user
     *
     * @param array $data
     *
     * @return bool
     */
    public function create($data) {
        $this->load->model('m_user');

        return $this->m_user->create($data);
    }

    /**
     * Login form
     */
    public function login_form($redirect = '') {
        if (connected_user() !== FALSE) {
            redirect();
        }

        $this->load->helper('form');
        $this->tpl->variables(
                array(
                    'title' => lang('login_form'),
                    'description' => lang('connect_cc'),
                    'redirect' => $redirect,
                    'msg_type' => $this->session->flashdata('msg_type'),
                    'msg' => $this->session->flashdata('msg'),
        ));
        $this->tpl->section('_sidebar', '_sidebar.phtml');
        $this->tpl->section('_view', 'login_form.phtml');
        $this->tpl->load_view(_TEMPLATE);
    }

    /**
     * Login action
     */
    public function login($redirect = '') {
        $this->load->model('m_user');
        $user_data = $this->m_user->login($this->input->post('email'), $this->input->post('password'));

        if ($user_data !== FALSE) {
            // Validate if user has validate email.
            if ($this->m_user->validated($this->input->post('email')) === FALSE) {
                $this->session->set_flashdata('msg', lang('not_validated'));
                $this->session->set_flashdata('msg_type', 'msg_warning');
                redirect('/cc/user/login_form', 'refresh');
            }

            $this->session->set_userdata('connected_user', $user_data->username);
            $this->session->set_userdata('current_account', $user_data->principal_account);
            $this->session->set_userdata('current_account_info', Modules::run('file/read/json_content', "_accounts/{$user_data->principal_account}/info_account.json"));
            $this->session->set_userdata('user_info', Modules::run('file/read/json_content', "_accounts/{$user_data->principal_account}/_participants/{$user_data->username}.json"));
            sleep(1);
            redirect(base64_decode($redirect), 'refresh');
        } else {
            $this->session->set_flashdata('msg', lang('invalid_login'));
            $this->session->set_flashdata('msg_type', 'msg_error');
            redirect('/cc/user/login_form', 'refresh');
        }
    }

    /**
     * Destroy session and send to Home.
     *
     */
    public function logout() {
        $this->session->sess_destroy();
        redirect('', 'refresh');
    }

    /**
     * Return verificaction if user exists
     *
     * @param string $username
     *
     * @return bool
     */
    public function user_exists($username) {
        $this->load->model('m_user');

        $result = $this->m_user->user_exists($username);

        if ($this->input->is_ajax_request()) {
            echo (int) $result;
        } else {
            return $result;
        }
    }

    /**
     * Return verificaction if email exists
     *
     * @param string $email
     *
     * @return bool
     */
    public function email_exists($email) {
        $this->load->model('m_user');

        $result = $this->m_user->email_exists($email);

        if ($this->input->is_ajax_request()) {
            echo (int) $result;
        } else {
            return $result;
        }
    }

    /**
     * Validate input data for register form
     *
     * @return array
     */
    public function validate_register_form() {
        $this->load->database();
        $this->load->library('form_validation');
        $this->load->helper(array('form'));
        $this->load->language('cc_form_validation');
        $this->form_validation->set_error_delimiters('', '');
        $result['result'] = 'ok';

        $field = 'name';
        $rules = 'trim|required|min_length[7]|max_length[75]';
        $this->form_validation->set_rules($field, 'lang:' . $field, $rules);

        $field = 'username';
        $rules = 'trim|required|min_length[4]|max_length[50]|alpha_numeric|is_unique[user.username]';
        $this->form_validation->set_rules($field, 'lang:' . $field, $rules);

        $field = 'email';
        $rules = 'trim|required|min_length[5]|max_length[254]|valid_email|is_unique[user.email]';
        $this->form_validation->set_rules($field, 'lang:' . $field, $rules);

        $field = 'password';
        $rules = 'trim|required|min_length[6]|max_length[255]';
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
     * Validate user
     *
     * @param string $token
     */
    public function validate($token = NULL) {
        if (is_null($token)) {
            redirect();
        }

        $this->load->model('m_user');

        if ($this->m_user->can_validate($token) === FALSE) {
            $this->tpl->variables(
                    array(
                        'title' => lang('invalid_data'),
                        'description' => '',
            ));
            $this->tpl->load_view(_TEMPLATE);
        } else {
            $username = $this->m_user->validate($token);

            // Create account.
//            $this->load->module('account/manage');
//            $this->manage->create($username);

            $this->tpl->variables(
                    array(
                        'title' => lang('registration_successful'),
                        'description' => sprintf(lang('can_login'), site_url_ws()),
            ));
            $this->tpl->load_view(_TEMPLATE);
        }
    }

    /**
     * Validate if participant belongs to context
     *
     * @param string $context
     * @param string $user
     * @param bool $strict TRUE: For users belongs to topic only. FALSE: For conected users only.
     *
     * @return type
     */
    public function belongs_to_context($context, $user, $strict = FALSE) {
        $this->load->module('file/misc');
        $path_context = "_accounts/{$this->session->userdata('current_account')}/contexts/{$this->misc->unslug($context)}";
        $participants = Modules::run('account/participant/list_for_context', $path_context);

        $plist = '';
        foreach ($participants as $participant) {
            $plist .= $participant['info']['id'] . '|';
        }

        return (Modules::run('cc/context/is_private', $context) === FALSE && $strict === FALSE) || strstr($plist, $user) !== FALSE;
    }

    /**
     * Validate if participant belongs to topic
     *
     * @param string $context
     * @param string $user
     * @param bool $strict TRUE: For users belongs to topic only. FALSE: For conected users only.
     *
     * @return type
     */
    public function belongs_to_topic($context, $user, $strict = FALSE) {
        $this->load->module('file/misc');
        $topic = "_accounts/{$this->session->userdata('current_account')}/contexts/{$this->misc->unslug($context)}";
        $participants = Modules::run('account/participant/list_for_context', topic_real_path($topic), 'topic');

        $plist = '';
        foreach ($participants as $participant) {
            $plist .= $participant['info']['id'] . '|';
        }

        return (Modules::run('cc/context/is_private', $context) === FALSE && $strict === FALSE) || strstr($plist, $user) !== FALSE;
    }

    /**
     * Finish the user invitation validating if exists.
     *
     * @param type $hash
     */
    public function invited($hash = NULL) {
        if ($hash === NULL) {
            redirect();
        }

        $this->load->module('file/read');

        $hash_data = explode('_', $hash);
        $hash_path = '';

        if (count($hash_data) === 2) {
            $hash_path = _INC_ROOT . '_accounts/' . $hash_data[0] . '/_invitations/' . $hash_data[1];
        }

        if (is_file($hash_path)) {
            $this->load->model('cc/m_user');
            $email = $this->read->content($hash_path);

            // Registered users.
            if ($this->m_user->email_exists($email)) {
                $this->add_account($this->m_user->username($email), $hash_data[0]);

                $this->tpl->variables(
                        array(
                            'title' => lang('invitation_successful'),
                            'description' => lang('access_invited'),
                ));
                $this->tpl->load_view(_TEMPLATE);

                unlink($hash_path);
                return;
            }
            // New users.
            else {
                redirect('cc/user/register_form/' . $hash);
            }
        }

        // Only on fail!
        $this->tpl->variables(
                array(
                    'title' => lang('error'),
                    'description' => '',
                    'msg_type' => 'msg_error',
                    'msg' => lang('error_validating_invitation'),
        ));
        $this->tpl->load_view(_TEMPLATE);
    }

    /**
     * Add account to user
     *
     * @param type $username
     * @param type $account
     *
     * @return boolean
     */
    public function add_account($username, $account) {
        $account_config = Modules::run('account/configuration/load');

        $username_data = json_encode(array(
            'info' => array(
                'id' => $username,
                'language' => $account_config['language'],
            ),
            'type' => 'participant',
        ));
        $user_file = _INC_ROOT . '_accounts/' . $account . '/_participants/' . $username . '.json';

        if (is_file($user_file) === FALSE) {
            $this->load->module('file/write');
            $this->load->model('cc/m_user');

            $this->write->archive($user_file, $username_data, 'x');
        }

        return $this->m_user->add_account($username, $account);
    }

    /**
     * Generate password form.
     */
    public function reset_password_form() {
        // Only for not connected.
        if (connected_user() !== FALSE) {
            redirect();
            return;
        }

        $this->load->helper('form');

        $this->tpl->variables(
                array(
                    'title' => lang('reset_form'),
                    'description' => lang('reset_description'),
                    'footer' => js_tag('pub/js/jquery.form.js') . js_tag('pub/web_tpl/js/user_reset_password.js'),
        ));

        $this->tpl->section('_view', 'reset_password.phtml');
        $this->tpl->load_view(_TEMPLATE);
    }

    /**
     * Generate hash to reset password by email link.
     */
    public function reset_password() {
        $this->load->module('file/write');
        $this->load->library('email');

        $hash = uniqid();
        // TODO Add user data!
        $this->write->archive(_INC_ROOT . 'tmp/' . $hash, md5($this->input->post('password')));

        $mail_body = nl2br(sprintf(lang('reset_mail'), site_url() . "/cc/user/validate_reset_password/{$hash}", site_url_ws()));

        // TODO Load email from configuration
        $this->email->from('noreply@carboncpm.com', 'CarbonCopy');
        $this->email->to($this->input->post('email'));

        $this->email->subject(lang('reset_subject'));
        $this->email->message($mail_body);
        $this->email->set_mailtype('html');

        $this->email->send();

        $this->session->set_flashdata('msg', lang('reset_password_sent'));
        $this->session->set_flashdata('msg_type', _MSG_OK);

        echo json_encode(array(
            'result' => 'ok',
            'message' => ''
        ));
    }

    /**
     * Validate reset password and change it if data is corect.
     *
     * @param string $hash
     */
    public function validate_reset_password($hash = NULL) {
        if ($hash === NULL) {
            redirect();
        }

        $this->load->module('file/read');

        $hash_path = '';

        if (strlen($hash) > 0) {
            $hash_path = _INC_ROOT . 'tmp/' . $hash;
        }

        if (is_file($hash_path)) {
            $this->load->model('cc/m_user');
            $password = $this->read->content($hash_path);

            // Registered users.
            if ($this->m_user->email_exists($password)) {
                $this->add_account($this->m_user->username($password), $hash[0]);

                $this->tpl->variables(
                        array(
                            'title' => lang('invitation_successful'),
                            'description' => lang('access_invited'),
                ));
                $this->tpl->load_view(_TEMPLATE);

                unlink($hash_path);
                return;
            }
            // New users.
            else {
                redirect('cc/user/register_form/' . $hash);
            }
        }

        // Only on fail!
        $this->tpl->variables(
                array(
                    'title' => lang('error'),
                    'description' => '',
                    'msg_type' => 'msg_error',
                    'msg' => lang('error_validating_invitation'),
        ));
        $this->tpl->load_view(_TEMPLATE);
    }

}
