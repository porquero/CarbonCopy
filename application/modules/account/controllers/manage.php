<?php

if ( ! defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Accounts manager
 *
 * @package CarbonCopy
 * @subpackage account
 * @author Cristian Riffo <criffoh@gmail.com>
 */
class Manage extends MX_Controller {

    /**
     * Create a new CC account
     *
     * @param string $username first account participant
     */
    public function create($username)
    {
        $this->load->module('file/write');
        $this->load->model('m_user');
        $this->load->model('cc/m_timeline');

        $account_id = uniqid();
        $account_path = _INC_ROOT . '_accounts/' . $account_id;

        // Create account dir.
        $zip = new ZipArchive();
        $zip->open(_INC_ROOT . '_accounts/_account_tpl.zip');
        $zip->extractTo($account_path);
        $zip->close();

        // Create first participant with user data.
        $username_data = json_encode(array(
            'info' => array(
                'id' => $username,
                'language' => 'english',
            ),
            'type' => 'administrator',
        ));
        $this->write->archive($account_path . '/_participants/' . $username . '.json', $username_data);

        // Save account info.
        $account_data = json_encode(array(
            'id' => $account_id,
            'name' => $username,
            'info' => 'Comunication manager'
        ));
        $this->write->archive($account_path . '/' . 'info_account.json', $account_data);

        // Create account timeline
        $this->m_timeline->create($account_id);

        // Add principal account to user.
        if ($this->m_user->has_not_default_account($username)) {
            $data = array(
                'principal_account' => $account_id,
                'accounts' => $account_id
            );
            $this->m_user->update($username, $data);
        }
        else {
            Modules::run('cc/user/add_account', $username, $account_id);
        }
    }

    /**
     * Change user account
     *
     * @param string $id_account
     */
    public function set_current($id_account)
    {
        $this->load->model('cc/m_user');
        if ($this->m_user->has_account(connected_user(), $id_account)) {
            $this->session->set_userdata('current_account', $id_account);
            $this->session->set_userdata('current_account_info', Modules::run('file/read/json_content', "_accounts/{$id_account}/info_account.json"));
        }

        redirect();
    }

    /**
     * Account config form
     */
    public function config_form()
    {
        is_connected('administrator');

        $this->load->helper('form');

        $account_config = Modules::run('account/configuration/load');
        $account_info = Modules::run('file/read/json_content', "_accounts/{$this->session->userdata('current_account')}/info_account.json");

        $this->tpl->variables(
          array(
              'title' => 'Account config',
              'description' => '',
              'account_date_format' => $account_config['date_format'],
              'account_language' => $account_config['language'],
              'context_label' => $account_config['context_label'],
              'topic_label' => $account_config['topic_label'],
              'account_name' => $account_info['name'],
              'account_info' => $account_info['info'],
              'footer' => js_tag('pub/js/jquery.form.js') . js_tag('pub/js/nicedit/nicEdit.js') . js_tag('pub/' . _TEMPLATE . '/js/account_config.js'),
              'date_format' => array(
                  'd-m-Y' => 'dd-mm-yyyy',
                  'Y-m-d' => 'yyyy-mm-dd'
              ),
              'language' => array(
                  'english' => 'english',
                  'spanish' => 'spanish',
              ),
              'msg_type' => isset($msg_type) ? $msg_type : '',
              'msg' => isset($msg) ? $msg : '',
              'notification' => (string) $account_config['notification'],
              'notification_day' => $account_config['notification_day'],
              'week_days' => array(
                  'monday' => lang('monday'),
                  'tuesday' => lang('tuesday'),
                  'wednesday' => lang('wednesday'),
                  'thursday' => lang('thursday'),
                  'friday' => lang('friday'),
                  'saturday' => lang('saturday'),
                  'sunday' => lang('sunday'),
              ),
              'future_tasks' => $account_config['future_tasks'],
        ));

        $this->tpl->section('_sidebar', '_sidebar.phtml');
        $this->tpl->section('_view', 'config_form.phtml');
        $this->tpl->load_view(_TEMPLATE);
    }

    /**
     * Save account config
     *
     * @return string
     */
    public function config()
    {
        is_connected('administrator');

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
            $this->load->module('file/write');

            // Save config data.
            $account_conifg = $this->_date_format($this->input->post('date_format')) + array(
                'language' => $this->input->post('language'),
                'context_label' => $this->input->post('context_label'),
                'topic_label' => $this->input->post('topic_label'),
                'notification' => $this->input->post('notification'),
                'notification_day' => $this->input->post('week_day'),
              ) + Modules::run('account/configuration/load');

            $config_saved = $this->write->archive("_accounts/{$this->session->userdata('current_account')}/config.json", json_encode($account_conifg));

            // Save info data.
            $account_info = Modules::run('file/read/json_content', "_accounts/{$this->session->userdata('current_account')}/info_account.json");
            $account_info = array(
                'id' => $account_info['id'],
                'name' => $this->input->post('account_name'),
                'info' => $this->input->post('home_info'),
            );
            $info_saved = $this->write->archive("_accounts/{$this->session->userdata('current_account')}/info_account.json", json_encode($account_info));

            $res = ($info_saved AND $config_saved) === TRUE ? 'ok' : 'fail';

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
     * Validate sent form to change config
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

        $field = 'account_name';
        $rules = 'trim|required|min_length[2]|max_length[75]';
        $this->form_validation->set_rules($field, 'lang:' . $field, $rules);

        $field = 'home_info';
        $rules = 'trim|required|min_length[2]';
        $this->form_validation->set_rules($field, 'lang:' . $field, $rules);

        $field = 'date_format';
        $rules = 'trim|required';
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
     * Return date format to use in account.
     *
     * @param string $type
     *
     * @return array
     */
    private function _date_format($type)
    {
        $types = array(
            'd-m-Y' => array(
                'date_format' => 'd-m-Y',
                'date_format_time' => 'd-m-Y H:i',
                'date_format_mysql' => '%d-%c-%y',
            ),
            'Y-m-d' => array(
                'date_format' => 'Y-m-d',
                'date_format_time' => 'Y-m-d H:i',
                'date_format_mysql' => '%y-%c-%d',
            ),
        );

        return $types[$type];
    }

    /**
     * Extends configuration
     */
    public function extends_form()
    {
        is_connected('administrator');
        $this->tpl->variables(
          array(
              'title' => lang('extensions_manager'),
              'description' => '',
              'footer' => '',
              'msg_type' => isset($msg_type) ? $msg_type : '',
              'msg' => isset($msg) ? $msg : '',
              'components' => Modules::run('extends/component/installed'),
              'sections' => Modules::run('extends/section/installed'),
              'footer' => js_tag('pub/' . _TEMPLATE . '/js/account_config.js'),
        ));

        $this->tpl->section('_sidebar', '_sidebar.phtml');
        $this->tpl->section('_view', 'extends.phtml');
        $this->tpl->load_view(_TEMPLATE);
    }

    /**
     * Account config principal view.
     */
    public function index()
    {

        $components = '';

        $this->tpl->variables(
          array(
              'title' => lang('account_configuration'),
              'description' => '',
              'footer' => '',
              'msg_type' => isset($msg_type) ? $msg_type : '',
              'msg' => isset($msg) ? $msg : '',
        ));

        $this->tpl->section('_view', 'index.phtml');
        $this->tpl->load_view(_TEMPLATE);
    }

}
