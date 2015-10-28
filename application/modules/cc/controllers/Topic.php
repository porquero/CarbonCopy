<?php

if ( ! defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Manage context  topics
 *
 * @package CarbonCopy
 * @subpackage cc
 * @author porquero
 */
class Topic extends MX_Controller {

    /**
     * Filename for topic replies.
     *
     * @var string
     */
    private $replies_filename = 'replies.csv';

    /**
     * Resume for actual topic
     *
     * @param string $context context slugged
     */
    public function resume($context)
    {
        $this->load->module('file/misc');
        $this->load->module('file/read');

        $msg_type = $this->session->flashdata('msg_type');
        $msg = $this->session->flashdata('msg');

        $topic = "_accounts/{$this->session->userdata('current_account')}/contexts/{$this->misc->unslug($context)}";

        // Validate if topic exists.
        if ( ! is_dir(_INC_ROOT . topic_real_path($topic))) {
            header('HTTP/1.1 404 Not Found');

            $this->tpl->variables(array(
                'title' => 'Error',
                'breadcrumb' => create_breadcrumb($context, get_name_from_slug($context)),
                'description' => sprintf(lang('topic_doesnt_exists'), get_name_from_slug($context))
            ));

            $this->tpl->load_view(_TEMPLATE);
            return;
        }

        // If private send to login form.
        if (Modules::run('cc/context/is_private', $context)) {
            is_connected();
        }

        // Validate if user belongs to it.
        if ( ! belongs_to('topic', $context, connected_user())) {
            $this->tpl->variables(
              array(
                  'title' => 'Error',
                  'breadcrumb' => create_breadcrumb($context, get_name_from_slug($context)),
                  'description' => lang('not_belongs_to_topic'),
            ));
            $this->tpl->load_view(_TEMPLATE);
            return;
        }

        // Set user date participation.
        $this->_set_last_participation($context);

        $info = $this->info($context);
        $date = account_date_format($info['info']['created_date']);
        $url = site_url('/account/participant/profile/' . $info['info']['created_by']);
        $author = <<<PQR
<div class="tpcdtl no-border">
    <div id="usr-pub"><a href="{$url}" class="usr">{$info['info']['created_by']}</a>
    <span>{$date}</span></div>
</div>
PQR;

        $user_locking = $this->_user_locking($topic);
        if ($user_locking !== FALSE) {
            $msg = sprintf(lang('topic_being_manipulated'), $user_locking);
            $msg_type = 'msg_warning';
        }

        // Run enabled sections.
        foreach (Modules::run('extends/section/installed') as $section) {
            if ($section['enabled'] && $section['data']->trigger->topic === TRUE) {
                Modules::run('extends/section/run', $section['id'], $context, get_name_from_slug($context));
            }
        }

        $this->tpl->variables(
          array(
              'head' => link_tag('pub/' . _TEMPLATE . '/css/topic.css'),
              'title' => $info['info']['title'],
              'description' => $info['info']['description'] . $author,
              'participants' => Modules::run('account/participant/list_for_context', topic_real_path($topic), 'topic'),
              'manage_participants_link' => '/cc/topic/manage_participants_form/' . $context,
              'breadcrumb' => create_breadcrumb($context, get_name_from_slug($context)),
              'context' => $context,
              'full_timeline' => Modules::run('cc/timeline/get_for_topic', $context, TRUE),
              'replies_path' => $info['replies'],
              'info' => $info['info'],
              'files' => $this->read->listing(topic_real_path($topic) . '/_files', 'any'),
//					'editable' => (boolean) connected_user() === TRUE ? 'true' : 'false',
              'user_locking' => $user_locking,
              'msg_type' => isset($msg_type) ? $msg_type : '',
              'msg' => isset($msg) ? $msg : '',
              'statuses' => array('opened' => 'close', 'closed' => 'open'),
              'participation' => $this->_get_participations($topic),
        ));

        $this->tpl->section('_sidebar', '_sidebar.phtml');
        $this->tpl->section('_aside', '_files.phtml');
        $this->tpl->section('_view', 'resume.phtml');
        $this->tpl->load_view(_TEMPLATE);
    }

    /**
     * Return contexts info
     *
     * @param string $context_topic
     * @return array
     */
    public function info($context_topic)
    {
        $this->load->module('file/read');
        $this->load->module('file/misc');

        $topic = "_accounts/{$this->session->userdata('current_account')}/contexts/{$this->misc->unslug($context_topic)}";
        $context_topic = $this->misc->final_slash(topic_real_path($topic));
        $info = $this->read->json_content($context_topic . 'info_topic.json');

        $result = array(
            'info' => $info,
            'replies' => $context_topic . $this->replies_filename,
        );

        return $result;
    }

    /**
     * Form to add topic
     * 
     * @param type $context
     */
    public function add_form($context)
    {
        is_connected();

        $this->load->helper(array('form', 'inflector'));
        $labels = Modules::run('cc/context/labels', $context);

        $this->tpl->variables(
          array(
              'title' => lang('add') . ' ' . singular($labels['topic_label']),
              'footer' => js_tag('pub/js/jquery.form.js') . js_tag('pub/js/nicedit/nicEdit.js') . js_tag('pub/' . _TEMPLATE . '/js/edit_topic.js'),
              'description' => '',
              'breadcrumb' => create_breadcrumb($context),
              'context' => $context,
              'id_topic' => uniqid(),
              'mode_edition' => 'create',
              'submit_txt' => lang('create'),
              'participants' => array(),
              'responsible' => NULL,
              'topic_description' => '',
              'topic_title' => '',
              'due' => '',
              'mode_edition' => 'add',
        ));
        $this->tpl->section('_view', 'add_form.phtml');
        $this->tpl->load_view(_TEMPLATE);
    }

    /**
     * Validate sent form and add topic for context.
     *
     * @return string
     */
    public function add()
    {
        is_connected();

        $result = $this->validate_form();

        if ($result['result'] === 'ok') {
            $this->load->helper(array('form'));

            $this->load->module('file/write');
            $this->load->module('cc/file');

            $dir_path = _INC_ROOT . '_accounts/' . $this->session->userdata('current_account') . '/contexts/'
              . unslug_path($this->input->post('context')) . '/_topics/' . $this->input->post('id');

            if (is_dir($dir_path) === TRUE) {
                $result = array(
                    'result' => 'fail',
                    'message' => 'Topic already exists in this context.'
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

            $dir_created = $this->write->dir($dir_path);
            $archive_created = FALSE;
            $participants = $this->input->post('participants');

            if ($dir_created === TRUE) {
                $this->write->dir($dir_path . '/_files');
                $this->write->dir($dir_path . '/_participation');
                $topic_info = array(
                    'id' => $this->input->post('id'),
                    'title' => trim($this->input->post('topic_title')),
                    'description' => trim($this->input->post('topic_description')),
                    'responsible' => $this->input->post('responsible'),
                    'due' => $this->input->post('due'),
                    'status' => 'opened',
                    'participants' => $participants == 'all' ? '' : implode(',', (array) $participants)
                      . ',' . connected_user(),
                    'created_by' => connected_user(),
                    'created_date' => date('Y-m-d'),
                );
                $content = json_encode($topic_info);
                $archive_created = $this->write->archive($dir_path . '/info_topic.json', $content);
                $this->write->archive($dir_path . '/' . $this->replies_filename, '');

                // Save file attached.
                $this->file_name = '';
                $message = '';
                $attach_process = TRUE;

                // Only upload when user add file.
                if (is_bool($this->input->post('userfile')) === TRUE) {
                    $this->file_upload_result = $this->file->upload($dir_path . '/_files');

                    if ($this->file_upload_result['result'] === 'ok') {
                        $this->file_name = $this->file_upload_result['data']['upload_data']['file_name'];
                    }
                    else {
                        $attach_process = FALSE;
                        $message = strip_tags($this->file_upload_result['data']['error']);
                    }
                }

                $res = $dir_created == TRUE && $archive_created == TRUE && $attach_process == TRUE ? 'ok' : 'fail';

                if ($res === 'ok') {
                    $this->load->model('m_timeline');

                    // Timeline info.
                    $data = array(
                        'title' => $this->input->post('topic_title'),
                        'from_participant' => connected_user(),
                        'to_participant' => $this->input->post('responsible'),
                        'context' => $this->input->post('context'),
                        'action_id' => 1,
                        'id_topic' => '_' . $this->input->post('id'),
                    );
                    $this->m_timeline->save_action($data);

                    // Due info.
                    if ($this->input->post('due') !== '') {
                        $this->load->model('m_due');
                        $this->m_due->add($this->input->post('context') . '_' . $this->input->post('id')
                          , $this->input->post('due'));
                    }
                }

                if ($res == 'fail' and strlen($message) == 0) {
                    //@todo Error number is manual now. Create secuencial system error codes next.
                    $message = 'Sytem error number #32444. Please contact us and send the error number.';
                }

                $result = array(
                    'result' => $res,
                    'message' => $message,
                );
            }
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
     * Validate sent form to create topic
     *
     * @return array for use in ajax request
     */
    public function validate_form()
    {

        $this->load->library('form_validation');
        $this->load->helper(array('form'));
        $this->load->language('cc_form_validation');
        $this->form_validation->set_error_delimiters('', '');
        $result['result'] = 'ok';

        $field = 'id';
        $rules = 'trim|required|min_length[2]|max_length[30]';
        $this->form_validation->set_rules($field, 'lang:' . $field, $rules);

        $field = 'topic_title';
        $rules = 'trim|required|min_length[2]|max_length[125]';
        $this->form_validation->set_rules($field, 'lang:' . $field, $rules);

        $field = 'due';
        $rules = 'valid_date_YYYYMMDD';
        $this->form_validation->set_rules($field, 'lang:' . $field, $rules);

//		$field = 'topic_description';
//		$rules = 'required|min_length[10]';
//		$this->form_validation->set_rules($field, 'lang:' . $field, $rules);

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
     * Save reply for topic in context
     *
     * @return mixed
     */
    public function reply()
    {
        is_connected();

        $this->load->module('file/misc');

        $topic = "_accounts/{$this->session->userdata('current_account')}/contexts/{$this->misc->unslug($this->input->post('context'))}";
        if ($this->input->post('submit') === 'cancel') {
            $this->_unlock($topic);
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

        $result = $this->validate_reply_form();

        if ($result['result'] === 'ok') {
            $this->load->helper(array('form', 'cc'));
            $this->load->module('file/write');
            $this->load->module('cc/file');

            $path = _INC_ROOT . '_accounts/' . $this->session->userdata('current_account') . '/contexts/' . topic_real_path(unslug_path($this->input->post('context')));

            $this->file_name = '';
            $message = '';
            $attach_process = TRUE;

            // Only upload when user add file.
            if (is_bool($this->input->post('userfile')) === TRUE) {
                $this->file_upload_result = $this->file->upload($path . '/_files');

                if ($this->file_upload_result['result'] === 'ok') {
                    $this->file_name = $this->file_upload_result['data']['upload_data']['file_name'];
                }
                else {
                    $attach_process = FALSE;
                    $message = strip_tags($this->file_upload_result['data']['error']);
                    $reply_saved = FALSE;
                }
            }

            if ($attach_process === TRUE) {
                $fields = array(
                    date('Y/m/d H:i'),
                    connected_user(),
                    trim($this->input->post('message')),
                    $this->file_name,
                );

                $reply_saved = $this->write->csv_archive_line($path . '/' . $this->replies_filename, $fields);
            }

            $res = $reply_saved == TRUE && $attach_process == TRUE ? 'ok' : 'fail';

            if ($res === 'ok') {
                $this->_unlock($topic);
                $this->load->model('m_timeline');

                $data = array(
                    'title' => $this->input->post('title'),
                    'from_participant' => connected_user(),
                    'context' => preg_replace('/_' . $this->input->post('id_topic') . '$/', '', $this->input->post('context')),
                    'action_id' => 2,
                    'id_topic' => '_' . $this->input->post('id_topic'),
                );
                $this->m_timeline->save_action($data);
            }

            if ($res == 'fail' && strlen($message) == 0) {
                //@todo Error number is manual now. Create secuencial system error codes next.
                $message = 'Sytem error number #32445. Please contact us and send the error number.';
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
     * Reply form
     * 
     * @param string $context
     */
    public function reply_form($context)
    {
        is_connected();

        $this->load->helper(array('cc', 'form'));

        $this->load->module('file/misc');
        $topic = "_accounts/{$this->session->userdata('current_account')}/contexts/{$this->misc->unslug($context)}";

        // Try to lock edition for other users.
        if ($this->_try_lock($context, $topic) !== TRUE) {
            return;
        }

        $info = $this->info($context);

        $this->tpl->variables(
          array(
              'title' => $info['info']['title'],
              'footer' => js_tag('pub/js/jquery.form.js') . js_tag('pub/js/nicedit/nicEdit.js') . js_tag('pub/' . _TEMPLATE . '/js/topic_reply.js'),
              'description' => lang('topic_reply'),
              'breadcrumb' => create_breadcrumb($context, get_name_from_slug($context)),
              'context' => $context,
              'id_topic' => get_name_from_slug($context),
        ));
        $this->tpl->section('_view', 'reply_form.phtml');
        $this->tpl->load_view(_TEMPLATE);
    }

    /**
     * Validate reply sent form
     * 
     * @return array
     */
    public function validate_reply_form()
    {

        $this->load->library('form_validation');
        $this->load->helper(array('form'));
        $this->load->language('cc_form_validation');
        $this->form_validation->set_error_delimiters('', '');
        $result['result'] = 'ok';

        $field = 'message';
        $rules = 'required';
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
     * Manage published topic participants
     *
     * @param string $context
     */
    public function manage_participants_form($context)
    {
        is_connected();

        $this->load->helper(array('form'));

        $this->load->module('file/misc');
        $topic = "_accounts/{$this->session->userdata('current_account')}/contexts/{$this->misc->unslug($context)}";

        // Try to lock edition for other users.
        if ($this->_try_lock($context, $topic) !== TRUE) {
            return;
        }

        $info = $this->info($context);

        $this->tpl->variables(
          array(
              'title' => lang('manage_participants'),
              'footer' => js_tag('pub/js/jquery.form.js'), // . js_tag('pub/' . _TEMPLATE . '/js/topic_manage_participants.js'),
              'context' => $context,
              'id_topic' => get_name_from_slug($context),
              'participants' => empty($info['info']['participants']) ? array() : explode(',', $info['info']['participants']),
        ));
        $this->tpl->section('_view', 'manage_participants_form.phtml');
        $this->tpl->load_view(_TEMPLATE_BLANK_TXT);
    }

    /**
     * Manage participants for topic
     *
     * @param string $context
     *
     * @return string html participants list
     */
    public function manage_participants($context)
    {
        $this->load->module('file/misc');
        $this->load->module('file/write');

        $topic = "_accounts/{$this->session->userdata('current_account')}/contexts/{$this->misc->unslug($context)}";
        if ($this->input->post('submit') === 'cancel') {
            $this->_unlock($topic);
            $result = array(
                'result' => 'canceled',
                'message' => ''
            );
            if ($this->input->is_ajax_request()) {
                echo json_encode($result);
                return;
            }
            else {
                return $result;
            }
        }

        $this->_unlock($topic);
        $info = $this->info($context);
        $topic_info = array(
            'id' => $info['info']['id'],
            'title' => $info['info']['title'],
            'description' => $info['info']['description'],
            'responsible' => $info['info']['responsible'],
            'due' => $info['info']['due'],
            'status' => $info['info']['status'],
            'participants' => trim($this->input->post('participants') == 'all' ? '' : implode(',', (array) $this->input->post('participants'))
                . ',' . connected_user(), ','),
            'created_by' => $info['info']['created_by'],
            'created_date' => $info['info']['created_date'],
        );
        $content = json_encode($topic_info);
        $dir_path = _INC_ROOT . '_accounts/' . $this->session->userdata('current_account') . '/contexts/'
          . topic_real_path(unslug_path($context));
        $archive_created = $this->write->archive($dir_path . '/info_topic.json', $content);

        $participants = '';
        foreach (Modules::run('account/participant/list_for_context', topic_real_path($topic), 'topic') as $participant) {
            $url = site_url('/account/participant/profile/' . trim($participant->info['id']));
            $participants .= <<<PQR
<li><a href="{$url}" class="usr">{$participant->info['id']}</a></li>
PQR;
        }
        if ($archive_created) {
            // Save in timeline.
            $this->load->model('m_timeline');
            $data = array(
                'title' => $info['info']['title'],
                'from_participant' => connected_user(),
                'context' => parent_context($context),
                'action_id' => 5,
                'id_topic' => '_' . $info['info']['id'],
            );
            $this->m_timeline->save_action($data);

            // Send ok message.
            $result = array(
                'result' => 'ok',
                'message' => '<ul>' . $participants . '</ul>'
            );
        }
        else {
            $result = array(
                'result' => 'fail',
                'message' => 'System Error #49874684'
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
     * Topic edit form
     *
     * @param type $context
     */
    public function modify_form($context)
    {
        if ( ! (is_connected() && belongs_to('topic', $context, connected_user()))) {
            die(lang('not_belongs_to_topic'));
        }

        $this->load->helper(array('form'));
        $this->load->module('file/misc');

        $topic = "_accounts/{$this->session->userdata('current_account')}/contexts/{$this->misc->unslug($context)}";

        // Try to lock edition for other users.
        if ($this->_try_lock($context, $topic) !== TRUE) {
            return;
        }

        $info = $this->info($context);

        $this->tpl->variables(
          array(
              'title' => lang('modify_topic'),
              'footer' => js_tag('pub/js/jquery.form.js') . js_tag('pub/js/nicedit/nicEdit.js') . js_tag('pub/' . _TEMPLATE . '/js/edit_topic.js'),
              'description' => '',
              'breadcrumb' => create_breadcrumb($context),
              'context' => $context,
              'mode_edition' => 'modify',
              'participants' => empty($info['info']['participants']) ? array() : explode(',', $info['info']['participants']),
              'responsible' => $info['info']['responsible'],
              'topic_title' => $info['info']['title'],
              'topic_description' => $info['info']['description'],
              'info' => $info,
              'id_topic' => $info['info']['id'],
              'participants' => empty($info['info']['participants']) ? array() : explode(',', $info['info']['participants']),
              'responsible' => $info['info']['responsible'],
              'due' => $info['info']['due'],
        ));
        $this->tpl->section('_view', 'modify_form.phtml');
        $this->tpl->load_view(_TEMPLATE);
    }

    /**
     * Save new data into topic
     *
     * @return mixed json(ajax) array(call)
     */
    public function modify()
    {
        is_connected();

        $this->load->module('file/misc');

        $context = $this->input->post('context');
        $topic = "_accounts/{$this->session->userdata('current_account')}/contexts/{$this->misc->unslug($context
            . '_' . $this->input->post('id'))}";
        if ($this->input->post('submit') === 'cancel') {
            $this->_unlock($topic);
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
            $this->load->model('m_timeline');

            $topic_name_slug = slug_path(strtolower($this->input->post('id')));
            $dir_path = _INC_ROOT . topic_real_path('_accounts/' . $this->session->userdata('current_account') . '/contexts/'
                . unslug_path($context) . '/' . $topic_name_slug);
            $this->_unlock($topic);
            $info = $this->info($context . '_' . $this->input->post('id'));

            if (is_dir($dir_path) === TRUE) {
                $topic_info = array(
                    'id' => $info['info']['id'],
                    'title' => trim($this->input->post('topic_title')),
                    'description' => trim($this->input->post('topic_description')),
                    'responsible' => $this->input->post('responsible'),
                    'due' => $this->input->post('due'),
                    'status' => $info['info']['status'],
                    'participants' => $info['info']['participants'],
                    'created_by' => $info['info']['created_by'],
                    'created_date' => $info['info']['created_date'],
                );

                $this->load->model('m_timeline');
                $content = json_encode($topic_info);
                $this->m_timeline->modify_topic($info['info']['id'], trim($this->input->post('topic_title')));
                $archive_created = $this->write->archive($dir_path . '/info_topic.json', $content);

                $res = $archive_created == TRUE ? 'ok' : 'fail';

                $message = '';
                if ($res == 'fail') {
                    //@todo Error number is manual now. Create secuencial system error codes next.
                    $message = 'Sytem error number #32143. Please contact us and send the error number.';
                }
                else {
                    // Timeline info.
                    $data = array(
                        'title' => $this->input->post('topic_title'),
                        'from_participant' => connected_user(),
                        'to_participant' => $this->input->post('responsible'),
                        'context' => $context,
                        'action_id' => 5,
                        'id_topic' => '_' . $topic_name_slug,
                    );
                    $this->m_timeline->save_action($data);

                    // Due info.
                    $this->load->model('m_due');
                    if ($this->input->post('due') == '') {
                        $this->m_due->delete_topic($this->input->post('context') . '_' . $this->input->post('id'));
                    }
                    else {
                        $this->m_due->change($this->input->post('context') . '_' . $this->input->post('id')
                          , $this->input->post('due'));
                    }
                }

                $result = array(
                    'result' => $res,
                    'message' => $message,
                );
            }
            else {
                $result = array(
                    'result' => 'fail',
                    'message' => 'Sytem error number #32143. Please contact us and send the error number.'
                );
            }
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
     * Try to lock topic manipulation.
     *
     * @param type $topic
     */
    private function _try_lock($context, $topic)
    {
        $this->load->module('file/write');
        $this->load->module('file/misc');

        $user_locking = $this->_user_locking($topic);
        if ($user_locking !== FALSE) {
            $this->tpl->variables(array(
                'title' => lang('atention'),
                'breadcrumb' => create_breadcrumb($context, get_name_from_slug($context)),
                'description' => sprintf(lang('topic_being_manipulated'), $user_locking)
            ));

            $this->tpl->load_view(_TEMPLATE);
        }
        else {
            $topic_lock = $this->misc->final_slash(topic_real_path($topic)) . 'LOCK';
            $this->write->archive($topic_lock, connected_user(), 'w');

            return TRUE;
        }
    }

    /**
     * Check if topic is locked by manipulation and return username if true.
     *
     * @param string $topic
     *
     * @return mixed false or username locking
     */
    private function _user_locking($topic)
    {
        $this->load->module('file/read');
        $this->load->module('file/misc');

        $topic_lock = $this->misc->final_slash(topic_real_path($topic)) . 'LOCK';

        if (is_file($topic_lock)) {
            $user_locking = $this->read->content($topic_lock);
            if (connected_user() !== $user_locking) {
                return $user_locking;
            }
        }

        return FALSE;
    }

    /**
     * Unlock topic manipulation.
     *
     * @param type $topic
     */
    private function _unlock($topic)
    {
        $this->load->module('file/misc');

        $lock_file = $this->misc->final_slash(topic_real_path($topic)) . 'LOCK';

        if (is_file($lock_file)) {
            return unlink($lock_file);
        }

        return FALSE;
    }

    /**
     * Send to trash topic
     *
     * @param string $data id_topic|context
     */
    public function delete($data)
    {
        $this->load->model('m_timeline');

        list($id_topic, $context) = explode('|', urldecode($data));

        $context_path = _INC_ROOT . preg_replace('/\/{2,}/', '/', "_accounts/{$this->session->userdata('current_account')}/contexts/" . unslug_path($context));
        $topic_path = $context_path . '/_topics/' . $id_topic;
        $trash_path = $context_path . '/_trash/_topics';
        $deleted_path = $trash_path . '/' . $id_topic;

        // Validate if _trash exists.
        if ( ! is_dir($trash_path)) {
            mkdir($trash_path, 0777, TRUE);
        }

        // Remove old same topic id if exists.
        if (is_dir($deleted_path)) {
            rmdir($deleted_path);
        }

        // Delete topictimeline.
        $rm = $this->m_timeline->delete_topic($id_topic, $context);

        // Send to trash.
        $rc = rename($topic_path, $deleted_path);

        if ($rm && $rc) {
            $this->load->model('m_due');

            $this->session->set_flashdata('msg_type', 'msg_ok');
            $this->session->set_flashdata('msg', lang('topic_deleted'));
            $this->m_due->delete_topic($context . '_' . $id_topic);

            redirect('/cc/context/resume/' . $context);
        }
        else {
            $this->tpl->variables(array(
                'msg_type' => 'msg_error',
                'msg' => lang('error_delete_topic'),
            ));

            $this->tpl->load_view(_TEMPLATE);
        }
    }

    /**
     * Topic move form
     *
     * @param type $data
     */
    public function move_form($data)
    {
        list($id_topic, $from_context) = explode('|', urldecode($data));
        $all_contexts = account_contexts($this->session->userdata('current_account'));
        $contexts = array();

        // Only show context that user belongs to.
        foreach ($all_contexts as $k => $context) {
            $this_context = trim($from_context, '_');
            $belongs = empty($context['context']) ? belongs_to('account', connected_user()) : belongs_to('context', $context['context'], connected_user());

            if ( ! $belongs || $from_context === $context['context'] || $context['context'] === $this_context) {
                continue;
            }
            $contexts[$k] = $context;
        }

        $variables = array(
            'contexts' => $contexts,
            'id_topic' => $id_topic,
            'from_context' => $from_context,
        );

        $this->tpl->variables($variables);

        if ($this->input->is_ajax_request()) {
            $this->tpl->section('_view', 'move_form.phtml');
            $this->tpl->load_view(_TEMPLATE_BLANK_TXT);
        }
        else {
            return $variables;
        }
    }

    /**
     * Move topic to another context
     *
     * @param string $data id_topic|from_context|to_context
     */
    public function move($data)
    {
        $message = lang('error_move_topic');
        list($id_topic, $from_context, $to_context) = explode('|', urldecode($data));

        $move_from = _INC_ROOT . preg_replace('/\/{2,}/', '/', "_accounts/{$this->session->userdata('current_account')}/contexts/" . unslug_path($from_context) . '/_topics/' . $id_topic);
        $move_to = preg_replace('/\/{2,}/', '/', "_accounts/{$this->session->userdata('current_account')}/contexts/" . unslug_path($to_context) . '/_topics/' . $id_topic);
        $topic = "_accounts/{$this->session->userdata('current_account')}/contexts/" . $from_context . '/' . $id_topic;

        $this->_try_lock($from_context, $topic);

        if (is_dir($move_to)) {
            $message = lang('same_context');
        }
        elseif (rename($move_from, _INC_ROOT . $move_to)) {
            $info = $this->info($to_context . '_' . $id_topic);
            $this->load->model('m_timeline');
            $this->m_timeline->move_topic($id_topic, $from_context, $to_context);

            // Change topic_context for due date.
            if ($info['info']['due'] !== '') {
                $this->load->model('m_due');
                $this->m_due->move_topic($from_context . '_' . $id_topic, $to_context . '_' . $id_topic);
            }

            $this->session->set_flashdata('msg', lang('topic_moved'));
            $this->session->set_flashdata('msg_type', 'msg_ok');

            redirect('/cc/topic/resume/' . trim($to_context . '_' . $id_topic, '_'));
        }

        $topic = "_accounts/{$this->session->userdata('current_account')}/contexts/" . $to_context . '/' . $id_topic;
        $this->_unlock($move_to);

        $this->tpl->variables(array(
            'msg_type' => 'msg_warning',
            'msg' => $message,
        ));

        $this->tpl->load_view(_TEMPLATE);
    }

    /**
     * Change topic status from/to open/close
     */
    public function open_close()
    {
        is_connected();

        if ($this->input->is_ajax_request() && $this->input->get('status') !== NULL && $this->input->get('context') !== NULL) {
            $this->load->module('file/misc');
            $this->load->model('m_timeline');
            $this->load->model('m_due');

            $topic = "_accounts/{$this->session->userdata('current_account')}/contexts/{$this->misc->unslug($this->input->get('context'))}";

            $this->_try_lock($this->input->get('context'), $topic);

            $info = $this->info($this->input->get('context'));
            $statuses = array('opened' => 'closed', 'closed' => 'opened');
            $due_statuses = array('opened' => 0, 'closed' => 1);
            $topic_info = array(
                'id' => $info['info']['id'],
                'title' => $info['info']['title'],
                'description' => $info['info']['description'],
                'responsible' => $info['info']['responsible'],
                'due' => $info['info']['due'],
                'status' => $statuses[$this->input->get('status')],
                'participants' => $info['info']['participants'],
                'created_by' => $info['info']['created_by'],
                'created_date' => $info['info']['created_date'],
            );
            $content = json_encode($topic_info);
            $dir_path = _INC_ROOT . '_accounts/' . $this->session->userdata('current_account') . '/contexts/'
              . topic_real_path(unslug_path($this->input->get('context')));

            $this->write->archive($dir_path . '/info_topic.json', $content);

            $this->_unlock($topic);

            // Due status.
            $this->m_due->status($this->input->get('context'), $due_statuses[$this->input->get('status')]);

            // Timeline info.
            $tm_status = array(
                'closed' => 3,
                'opened' => 4,
            );
            $data = array(
                'title' => $info['info']['title'],
                'from_participant' => connected_user(),
                'context' => parent_context($this->input->get('context')),
                'action_id' => $tm_status[$statuses[$this->input->get('status')]],
                'id_topic' => '_' . $info['info']['id'],
            );
            $this->m_timeline->save_action($data);

            echo 'ok';
        }
        else {
            echo 'Not allowed';
        }
    }

    /**
     * Set last time date that user see the topic.
     * 
     * @param string $topic
     * 
     * @return bool
     */
    private function _set_last_participation($topic)
    {
        $this->load->module('file/write');

        // Validate if participation dir exits.
        $participation_dir = _INC_ROOT . '_accounts/' . $this->session->userdata('current_account')
          . '/contexts/' . $this->misc->final_slash(topic_real_path(unslug_path($topic))) . '_participation/';

        if ( ! is_dir($participation_dir)) {
            $this->write->dir($participation_dir);
        }

        $participation_file = $participation_dir . connected_user();

        return $this->write->archive($participation_file, account_date_format(date('Y-m-d H:i:s'), TRUE), 'w');
    }

    /**
     * Get users participation for the topic.
     * 
     * @param mixed $topic
     */
    private function _get_participations($topic)
    {
        return $this->read->listing(topic_real_path($topic) . '/_participation', 'any');
    }

}
