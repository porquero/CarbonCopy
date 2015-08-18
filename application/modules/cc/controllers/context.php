<?php

if ( ! defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Manage account contexts 
 *
 * @package CarbonCopy
 * @subpackage cc
 * @author porquero
 */
class context extends MX_Controller {

    /**
     * Used to cache method results
     *
     * @var array
     */
    protected $_cache = array();

    /**
     * Resume for actual context
     *
     * @param string $context context slugged
     * @param string $menu_act Indicates wich timeline is active. To being used in css.
     */
    public function resume($context = null, $menu_act = 'all', $ts_date = NULL)
    {
        if (is_null($context)) {
            $this->session->keep_flashdata('msg_type');
            $this->session->keep_flashdata('msg');

            redirect();
        }

        $this->load->module('cc/timeline');

        $msg_type = $this->session->flashdata('msg_type');
        $msg = $this->session->flashdata('msg');

        $context_path = context_real_path($context);

        // Validate if context exists.
        if ( ! is_dir(_INC_ROOT . $context_path)) {
            header('HTTP/1.1 404 Not Found');

            $this->tpl->variables(array(
                'title' => lang('error'),
                'breadcrumb' => create_breadcrumb($context),
                'description' => sprintf(lang('context_doesnt_exists'), get_name_from_slug($context))
            ));

            $this->tpl->load_view(_TEMPLATE);
            return;
        }
        $info = $this->info($context);

        // If private send to login form.
        if ($this->is_private($context)) {
            is_connected();
        }

        // Validate if participant belongs to context.
        if ( ! belongs_to('context', $context, connected_user())) {
            $this->tpl->variables(
              array(
                  'title' => lang('error'),
                  'breadcrumb' => create_breadcrumb($context),
                  'description' => lang('not_belongs_to_context'),
            ));
            $this->tpl->load_view(_TEMPLATE);
            return;
        }

        $this->timeline->menu_act[$menu_act] = 'act';
        $participant_id = $menu_act === 'to_me' ? connected_user() : NULL;

        $user_locking = $this->_user_locking($context_path);
        if ($user_locking !== FALSE) {
            $msg = sprintf(lang('context_being_manipulated'), $user_locking);
            $msg_type = 'msg_warning';
        }
        $tl_date = is_null($ts_date) ? '' : ': ' . account_date_format($ts_date);
        $labels = $this->labels($context);

        // Run enabled sections.
        foreach (Modules::run('extends/section/installed') as $section) {
            if ($section['enabled'] && $section['data']->trigger->context === TRUE) {
                Modules::run('extends/section/run', $section['id'], $context);
            }
        }

        $this->tpl->variables(
          array(
              'title' => $info['info']['title'],
              'tl_title' => lang('timeline') . $tl_date,
              'info' => $info,
              'description' => $info['info']['description'],
              'participants' => Modules::run('account/participant/list_for_context', $context_path),
              'manage_participants_link' => '/cc/context/manage_participants_form/' . $context,
              'full_timeline' => $this->timeline->contexts_topics($context, TRUE, $participant_id, $ts_date),
              'topics' => $this->timeline->get_for_context($context, TRUE, $participant_id, $ts_date),
              'breadcrumb' => create_breadcrumb($context),
              'id_context' => $info['info']['id'],
              'context_label' => $labels['context_label'],
              'topic_label' => $labels['topic_label'],
              'context' => $context,
              'url_all' => site_url('/cc/context/resume/' . $context),
              'url_to_me' => site_url('/cc/context/resume/' . $context . '/to_me'),
              'url_ts' => site_url('/cc/context/resume/' . $context . '/' . $menu_act),
              'menu_act' => $this->timeline->menu_act,
              '_this' => $this,
              'user_locking' => $user_locking,
              'msg_type' => isset($msg_type) ? $msg_type : '',
              'msg' => isset($msg) ? $msg : '',
        ));
        $this->tpl->section('_sidebar', '_sidebar.phtml');
        $this->tpl->section('_aside', '_timeline.phtml');
        $this->tpl->section('_view', 'resume.phtml');
        $this->tpl->load_view(_TEMPLATE);
    }

    /**
     * Return contexts info
     *
     * @param string $context
     * @counter integer Used to stop recursivity
     *
     * @return array
     */
    function info($context, &$counter = 0, $depth = 1)
    {
        if (isset($this->_cache[$context])) {
            return $this->_cache[$context];
        }

        $counter ++;

        $this->load->module('file/read');
        $this->load->module('file/misc');

        $context = context_real_path($context);
        $context = $this->misc->final_slash($context);
        $env_sl = preg_replace('/\//', '\\/', $context);

        $dirs = $this->read->directories($context);
        $info = $this->read->json_content($context . 'info_context.json');
        $directories = $contexts = array();

        // Avoid n recursivity.
        if ($counter > $depth) {
            return array(
                'info' => $info,
                'contexts' => $contexts,
                'dirs' => $directories,
            );
        }

        foreach ($dirs as $dir) {
            $dir_preg = slug_path(preg_replace('/_accounts\/[a-z0-9]*\/contexts\//', '', $dir));
            if (preg_match("/^{$env_sl}\_/", $dir) === 1) {
                $directories[] = $dir_preg;
            }
            else {
                $contexts[$dir_preg] = $this->info($dir_preg, $counter);
            }
        }

        $this->_cache[$context] = array(
            'info' => $info,
            'contexts' => $contexts,
            'dirs' => $directories,
        );

        return $this->_cache[$context];
    }

    /**
     * Create context
     *
     */
    public function create()
    {
        is_connected();

        $result = $this->validate_form();

        if ($result['result'] === 'ok') {
            $this->load->helper(array('form'));

            $this->load->module('file/write');
            $this->load->model('m_timeline');

            $context_title_slug = trim(slug_path(strtolower(slug_text(preg_replace('/\\//', '', $this->input->post('id'))))), '-');
            $context_title_slug = empty($context_title_slug) ? uniqid() : $context_title_slug;
            $dir_path = _INC_ROOT . context_real_path($this->input->post('context') . '/' . $context_title_slug);

            if (is_dir($dir_path) === TRUE) {
                $result = array(
                    'result' => 'fail',
                    'message' => lang('context_already_exists')
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
                $this->write->dir($dir_path . '/_topics');
                $context_info = array(
                    'id' => $context_title_slug,
                    'title' => $this->input->post('id'),
                    'description' => trim($this->input->post('context_description')),
                    'responsible' => $this->input->post('responsible'),
                    'label_inherit' => (int) $this->input->post('label_inherit'),
                    'context_label' => $this->input->post('context_label'),
                    'topic_label' => $this->input->post('topic_label'),
                    'participants' => $participants == 'all' ? '' : implode(',', (array) $participants)
                      . ',' . connected_user(),
                    'created_by' => connected_user(),
                    'created_date' => date('Y-m-d'),
                );

                if ($this->input->post('visibility') !== FALSE) {
                    $context_info['visibility'] = $this->input->post('visibility');
                }

                $archive_created = $this->write->archive($dir_path . '/info_context.json', json_encode($context_info));

                $res = $dir_created && $archive_created == TRUE ? 'ok' : 'fail';

                $message = $context_title_slug;
                if ($res == 'fail') {
                    //@todo Error number is manual now. Create secuencial system error codes next.
                    $message = 'Sytem error number #32143. Please contact us and send the error number.';
                }
                else {
                    $data = array(
                        'title' => $this->input->post('id'),
                        'from_participant' => connected_user(),
                        'context' => $this->input->post('context'),
                        'action_id' => 1,
                        'id_context' => $context_title_slug,
                    );
                    $this->m_timeline->save_action($data);
                }

                $result = array(
                    'result' => $res,
                    'message' => $message,
                );
            }
            else {
                $result = array(
                    'result' => 'fail',
                    'message' => lang('error_context_write')
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
     * Create context form view
     *
     * @param type $context
     */
    public function create_form($context = '')
    {
        is_connected();

        $this->load->helper(array('form'));

        $this->tpl->variables(
          array(
              'title' => lang('add_context'),
              'footer' => js_tag('pub/js/jquery.form.js') . js_tag('pub/js/nicedit/nicEdit.js') . js_tag('pub/web_tpl/js/edit_context.js'),
              'description' => '',
              'breadcrumb' => create_breadcrumb($context),
              'context' => $context,
              'mode_edition' => 'create',
              'context_participants' => array(),
              'responsible' => NULL,
              'context_description' => '',
              'id' => '',
              '_this' => $this,
              'info' => array(
                  'info' => array(
                      'label_inherit' => 1,
                      'context_label' => '',
                      'topic_label' => '',
                  )
              ),
        ));
        $this->tpl->section('_view', 'create_form.phtml');
        $this->tpl->load_view(_TEMPLATE);
    }

    /**
     * Validate sent form to create context
     *
     * @return string
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

//		$field = 'context_description';
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
     * Check if children contexts belongs to private top context.
     *
     * @param string $context
     *
     * @return bool
     */
    public function is_private($context)
    {
        preg_match('/[a-z0-9-]*/', $context, $m);
        $top_context = isset($m[0]) ? $m[0] : $context;

        $info = $this->info($top_context);

        return $info['info']['visibility'] == 'private';
    }

    /**
     * Edit form
     *
     * @param type $context
     */
    public function modify_form($context)
    {
        if ( ! (is_connected() && belongs_to('context', $context, connected_user()))) {
            die(lang('not_belongs'));
        }

        // Try to lock edition for other users.
        if ($this->_try_lock($context) !== TRUE) {
            return;
        }

        $this->load->helper(array('form'));

        $info = $this->info($context);

        $this->tpl->variables(
          array(
              'title' => lang('modify_context'),
              'footer' => js_tag('pub/js/jquery.form.js') . js_tag('pub/js/nicedit/nicEdit.js') . js_tag('pub/web_tpl/js/edit_context.js'),
              'description' => '',
              'breadcrumb' => create_breadcrumb($context),
              'context' => $context,
              'mode_edition' => 'modify',
              'context_participants' => empty($info['info']['participants']) ? array() : explode(',', $info['info']['participants']),
              'responsible' => $info['info']['responsible'],
              'context_description' => $info['info']['description'],
              'info' => $info,
              'id' => $info['info']['id']
        ));
        $this->tpl->section('_view', 'modify_form.phtml');
        $this->tpl->load_view(_TEMPLATE);
    }

    /**
     * Save new data for context
     *
     * @return mixed json(ajax) array(call)
     */
    public function modify()
    {
        is_connected();

        $context = preg_replace('/\_*+' . $this->input->post('id') . '$/', '', $this->input->post('context'));
        $id_context = context_real_path($context . '_' . $this->input->post('id'));

        if ($this->input->post('submit') === 'cancel') {
            $this->_unlock($id_context);
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

            $context_name_slug = slug_path(strtolower($this->input->post('id')));
            $dir_path = _INC_ROOT . '_accounts/' . $this->session->userdata('current_account') . '/contexts/'
              . unslug_path($context) . '/' . $context_name_slug;
            $this->_unlock($id_context);
            $info = $this->info($context . '_' . $this->input->post('id'));

            if (is_dir($dir_path) === TRUE) {
                $context_info = array(
                    'id' => $info['info']['id'],
                    'title' => $this->input->post('title'),
                    'description' => trim($this->input->post('context_description')),
                    'responsible' => $this->input->post('responsible'),
                    'participants' => $info['info']['participants'],
                    'created_by' => $info['info']['created_by'],
                    'created_date' => $info['info']['created_date'],
                    'label_inherit' => (int) $this->input->post('label_inherit'),
                    'context_label' => trim($this->input->post('context_label')),
                    'topic_label' => trim($this->input->post('topic_label')),
                );

                if ($this->input->post('visibility') !== FALSE) {
                    $context_info['visibility'] = $this->input->post('visibility');
                }

                $content = json_encode($context_info);
                $archive_created = $this->write->archive($dir_path . '/info_context.json', $content);

                $res = $archive_created == TRUE ? 'ok' : 'fail';

                $message = '';
                if ($res == 'fail') {
                    //@todo Error number is manual now. Create secuencial system error codes next.
                    $message = 'Sytem error number #32143. Please contact us and send the error number.';
                }
                else {
                    $data = array(
                        'title' => $this->input->post('title'),
                        'from_participant' => connected_user(),
                        'to_participant' => $this->input->post('responsible'),
                        'context' => $context,
                        'action_id' => 5,
                        'id_context' => $this->input->post('id'),
                    );
                    $this->m_timeline->save_action($data);
                }

                $result = array(
                    'result' => $res,
                    'message' => $message,
                );
            }
            else {
                $result = array(
                    'result' => 'fail',
                    //@todo Error number is manual now. Create secuencial system error codes next.
                    'message' => 'Sytem error number #32193. Please contact us and send the error number.'
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
     * Manage published context participants
     *
     * @param string $context
     */
    public function manage_participants_form($context)
    {
        is_connected();

        $this->load->helper(array('form'));


        // Try to lock edition for other users.
        if ($this->_try_lock($context) !== TRUE) {
            return;
        }

        $info = $this->info($context);

        $this->tpl->variables(
          array(
              'title' => lang('manage_participants'),
              'footer' => js_tag('pub/js/jquery.form.js'),
              'context' => $context,
              'id_topic' => get_name_from_slug($context),
              'participants' => empty($info['info']['participants']) ? array() : explode(',', $info['info']['participants']),
        ));
        $this->tpl->section('_view', 'manage_participants_form.phtml');
        $this->tpl->load_view(_TEMPLATE_BLANK_TXT);
    }

    /**
     * Manage participants for contet
     *
     * @param string $context
     *
     * @return string html participants list
     */
    public function manage_participants($context)
    {
        $this->load->module('file/misc');
        $this->load->module('file/write');

        $context_path = "_accounts/{$this->session->userdata('current_account')}/contexts/{$this->misc->unslug($context)}";
        if ($this->input->post('submit') === 'cancel') {
            $this->_unlock($context_path);
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

        $this->_unlock($context_path);
        $info = $this->info($context);
        // TODO: Use method for modify context info content!
        $context_info = array(
            'id' => $info['info']['id'],
            'title' => $info['info']['title'],
            'description' => $info['info']['description'],
            'responsible' => $info['info']['responsible'],
            'visibility' => $info['info']['visibility'],
            'participants' => trim($this->input->post('participants') == 'all' ? '' : implode(',', (array) $this->input->post('participants'))
                . ',' . connected_user(), ','),
            'created_by' => $info['info']['created_by'],
            'created_date' => $info['info']['created_date'],
        );
        $content = json_encode($context_info);
        $dir_path = _INC_ROOT . '_accounts/' . $this->session->userdata('current_account') . '/contexts/'
          . unslug_path($context);
        $archive_created = $this->write->archive($dir_path . '/info_context.json', $content);

        $participants = '';
        foreach (Modules::run('account/participant/list_for_context', $context_path) as $participant) {
            $url = site_url('/account/participant/profile/' . trim($participant->info['id']));
            $participants .= <<<PQR
<li><a href="{$url}" class="usr">{$participant->info['id']}</a></li>
PQR;
        }

        if ($archive_created) {
            // Save in timeline.
            $this->load->model('m_timeline');
            $data = array(
                'title' => $info['info']['id'],
                'from_participant' => connected_user(),
                'context' => preg_replace('/\_*+' . $info['info']['id'] . '$/', '', $context),
                'action_id' => 5,
                'id_context' => $info['info']['id'],
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
     * Try to lock context manipulation.
     *
     * @param type $contexti
     */
    private function _try_lock($contexti)
    {
        $this->load->module('file/write');
        $this->load->module('file/misc');

        $context_path = "_accounts/{$this->session->userdata('current_account')}/contexts/{$this->misc->unslug($contexti)}";
        $user_locking = $this->_user_locking($context_path);

        if ($user_locking !== FALSE) {
            $this->tpl->variables(array(
                'title' => lang('atention'),
                'breadcrumb' => create_breadcrumb($context_path),
                'description' => sprintf(lang('description_lock'), $user_locking)
            ));

            $this->tpl->load_view(_TEMPLATE);
        }
        else {
            $context_lock = $this->misc->final_slash($context_path) . 'LOCK';
            $this->write->archive($context_lock, connected_user());

            return TRUE;
        }
    }

    /**
     * Check if context is locked by manipulation and return username if true.
     *
     * @param string $context
     *
     * @return mixed false or username locking
     */
    private function _user_locking($context)
    {
        $this->load->module('file/read');
        $this->load->module('file/misc');

        $context_lock = $this->misc->final_slash($context) . 'LOCK';

        if (is_file($context_lock)) {
            $user_locking = $this->read->content($context_lock);
            if (connected_user() !== $user_locking) {
                return $user_locking;
            }
        }

        return FALSE;
    }

    /**
     * Unlock context manipulation.
     *
     * @param type $context
     */
    private function _unlock($context)
    {
        $this->load->module('file/misc');

        $lock_file = $this->misc->final_slash($context) . 'LOCK';

        if (is_file($lock_file)) {
            return unlink($lock_file);
        }

        return FALSE;
    }

    /**
     * context move form
     *
     * @param type $data
     */
    public function move_form($data)
    {
        list($id_context, $from_context) = explode('|', urldecode($data));
        $contexts = array();

        // Only show context that user belongs to.
        foreach (account_contexts($this->session->userdata('current_account')) as $k => $context) {
            $this_context = trim($from_context . '_' . $id_context, '_');
            $belongs = empty($context['context']) ? belongs_to('account', connected_user()) : belongs_to('context', $context['context'], connected_user());

            if ( ! $belongs || $from_context === $context['context'] || $context['context'] === $this_context || strstr($context['context'], $this_context . '_')) {
                continue;
            }
            $contexts[$k] = $context;
        }

        $variables = array(
            'contexts' => $from_context === '' ? $contexts : array('/' => array('title' => '[/]', 'context' => '')) + $contexts,
            'id_context' => $id_context,
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
     * Move context to another context
     *
     * @param string $data id_context|from_context|to_context
     */
    public function move($data)
    {
        $message = lang('error_context_moved');
        list($id_context, $from_context, $to_context) = explode('|', urldecode($data));

        $move_from = _INC_ROOT . preg_replace('/\/{2,}/', '/', "_accounts/{$this->session->userdata('current_account')}/contexts/" . unslug_path($from_context) . '/' . $id_context);
        $move_to = preg_replace('/\/{2,}/', '/', "_accounts/{$this->session->userdata('current_account')}/contexts/" . unslug_path($to_context) . '/' . $id_context);

        $this->_try_lock($from_context . '_' . $id_context);

        if (is_dir($move_to)) {
            $message = lang('same_context');
        }
        elseif (rename($move_from, _INC_ROOT . $move_to)) {
            $info = $this->info($to_context . '_' . $id_context);

            $context_info = array(
                'id' => $info['info']['id'],
                'title' => $info['info']['title'],
                'description' => $info['info']['description'],
                'responsible' => $info['info']['responsible'],
                'participants' => $info['info']['participants'],
                'created_by' => $info['info']['created_by'],
                'created_date' => $info['info']['created_date'],
            );

            if (empty($to_context)) {
                $context_info['visibility'] = 'private';
            }

            $this->write->archive(_INC_ROOT . $move_to . '/info_context.json', json_encode($context_info));

            $this->load->model('m_timeline');
            $this->load->model('m_due');

            $this->m_timeline->move_context($id_context, $from_context, $to_context);
            $this->m_due->move_context(trim($from_context . '_' . $id_context, '_'), trim($to_context . '_' . $id_context, '_'));

            $this->session->set_flashdata('msg', lang('context_moved'));
            $this->session->set_flashdata('msg_type', 'msg_ok');

            redirect('/cc/context/resume/' . trim($to_context . '_' . $id_context, '_'));
        }

        $this->_unlock($move_to);

        $this->tpl->variables(array(
            'msg_type' => 'msg_warning',
            'msg' => $message,
        ));

        $this->tpl->load_view(_TEMPLATE);
    }

    /**
     * Send to trash sent context
     *
     * @param string $data id_context|context
     */
    public function delete($data)
    {
        $this->load->model('m_timeline');
        $this->load->model('m_due');

        list($id_context, $context) = explode('|', urldecode($data));

        $context_path = _INC_ROOT . preg_replace('/\/{2,}/', '/', "_accounts/{$this->session->userdata('current_account')}/contexts/" . unslug_path($context));
        $id_context_path = $context_path . '/' . $id_context;
        $trash_path = $context_path . '/_trash';
        $deleted_path = $trash_path . '/' . $id_context;

        // Validate if _trash exists.
        if ( ! is_dir($trash_path)) {
            mkdir($trash_path);
        }

        // Remove old same context id if exists.
        if (is_dir($deleted_path)) {
            rrmdir($deleted_path);
        }

        // Delete context timeline.
        $rm = $this->m_timeline->delete_context($id_context, $context);

        // Delete topics due date.
        $this->m_due->delete_context("{$context}_{$id_context}");

        // Send to trash.
        $rc = rename($id_context_path, $deleted_path);

        if ($rm && $rc) {
            $this->session->set_flashdata('msg_type', 'msg_ok');
            $this->session->set_flashdata('msg', lang('context_deleted'));

            redirect('/cc/context/resume/' . $context);
        }
        else {
            $this->tpl->variables(array(
                'msg_type' => 'msg_error',
                'msg' => lang('error_delete_context'),
            ));

            $this->tpl->load_view(_TEMPLATE);
        }
    }

    /**
     * Get context labels (context and topic)
     *
     * @param string $context
     *
     * @return array
     */
    public function labels($context)
    {
        if (empty($context)) {
            $account_config = Modules::run('file/read/json_content', "_accounts/{$this->session->userdata('current_account')}/config.json");

            return array(
                'context_label' => $account_config['context_label'],
                'topic_label' => $account_config['topic_label'],
            );
        }

        $counter = 1;
        $context_info = $this->info($context, $counter);

        // TODO: add type validation to avoid server lock!
        if ( ! is_array($context_info)) {
            Plogger::var_dump($context_info);
            die("Fix this!. [{$context}]" . __FILE__ . __LINE__);
        }

        if (isset($context_info['info']['label_inherit']) && $context_info['info']['label_inherit'] === 0) {
            return array(
                'context_label' => $context_info['info']['context_label'],
                'topic_label' => $context_info['info']['topic_label'],
            );
        }
        else {
            $context = preg_replace('/\_?[a-z0-9-]*$/', '', $context);

            return $this->labels($context);
        }
    }

}
