<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Manage account resume
 *
 * @package CarbonCopy
 * @subpackage cc
 * @author porquero
 */
class Resume extends MX_Controller {

    function __construct()
    {
        parent::__construct();
    }
    
    /**
     *  Resume for all contexts
     *
     * @param string $menu_act Indicates wich timeline is active. To being used in css.
     */
    public function index($menu_act = 'all', $ts_date = NULL) {
        $this->output->set_header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
        $this->output->set_header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
        $this->output->set_header('Cache-Control: post-check=0, pre-check=0', FALSE);
        $this->output->set_header('Pragma: no-cache');

        if ($menu_act === 'to_me') {
            is_connected();
        }

        $msg_type = $this->session->flashdata('msg_type');
        $msg = $this->session->flashdata('msg');

        $this->load->module('cc/timeline');
        $this->load->model('m_user');
        $this->load->model('m_due');

        $contexts = $this->contexts($this->session->userdata('current_account'));
        $info = Modules::run('file/read/json_content', "_accounts/{$this->session->userdata('current_account')}/info_account.json");
        $account_config = Modules::run('file/read/json_content', "_accounts/{$this->session->userdata('current_account')}/config.json");

        $this->timeline->menu_act[$menu_act] = 'act';
        $participant_id = $menu_act === 'to_me' ? connected_user() : NULL;
        $tl_date = is_null($ts_date) ? '' : ': ' . account_date_format($ts_date);

        $this->tpl->variables(
                array(
                    'title' => $info['name'],
                    'head' => link_tag('pub/' . _TEMPLATE . '/css/resume.css'),
                    'tl_title' => lang('timeline') . $tl_date,
                    'description' => $info['info'],
                    'contexts' => $contexts,
                    'context_label' => $account_config['context_label'],
                    'participants' => Modules::run('account/participant/list_for_resume'),
                    'manage_participants_link' => '/cc/resume/manage_participants_form',
                    'timeline' => $this->timeline->get_for_resume(TRUE, $participant_id, $ts_date),
                    'url_all' => site_url(),
                    'url_to_me' => site_url('/cc/resume/index/to_me'),
                    'url_ts' => site_url('/cc/resume/index/' . $menu_act),
                    'menu_act' => $this->timeline->menu_act,
                    'accounts' => $this->m_user->accounts(connected_user()),
                    'msg_type' => isset($msg_type) ? $msg_type : '',
                    'msg' => isset($msg) ? $msg : '',
                    'due_date_topics' => $this->m_due->for_day($ts_date),
                    'due_future_topics' => $this->m_due->future($ts_date),
                    'only_opened' => TRUE,
                )
        );

        $this->tpl->section('_view', 'index.phtml');
        $this->tpl->section('_sidebar', '_sidebar.phtml');
        $this->tpl->section('_aside', '_timeline.phtml');
        $this->tpl->load_view(_TEMPLATE);
    }

    /**
     * Get fist contexts for account
     *
     * @param string $account
     * @return array
     */
    function contexts($account) {
        $this->load->module('cc/context');
        $result = array();
        $contexts = Modules::run('file/read/directories', "_accounts/{$account}/contexts/");

        foreach ($contexts as $context) {
            // TODO: Validate if context exists!
            if (preg_match('/^_(.*)/', basename($context)) === 1) {
                continue;
            }
            $result[] = $this->context->info(basename($context));
        }

        return $result;
    }

}
