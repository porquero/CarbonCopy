<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Manage user searches
 *
 * @package CarbonCopy
 * @subpackage cc
 * @author Cristian Riffo <criffoh@gmail.com>
 */
class Search extends MX_Controller {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Search text in account and return result
     *
     * @return mixed array/json
     */
    public function go() {
        $q = $this->input->get('q');

        if (empty($q)) {
            return FALSE;
        }

        $result['contexts'] = $this->_in_context($q);
        $result['topics'] = $this->_in_topic($q);
        $result['replies'] = $this->_in_topic($q, 'replies.csv');

        if ($this->input->is_ajax_request()) {
            $send = '';

            foreach ((array) $result['contexts'] as $link) {
                $l = site_url('cc/context/resume/' . $link['context']);
                $bc = create_breadcrumb(parent_context($link['context']));
                $send.= <<<PQR
<li><a href="{$l}" tabindex="1" class="context"><span>{$link['title']}</span></a> {$bc}</li>
PQR;
            }

            unset($result['contexts']);
            $unique_link = array();

            foreach ((array) $result as $k => $links) {
                foreach ($links as $link) {
                    if (in_array($link, $unique_link)) {
                        continue;
                    }

                    $unique_link[] = $link;

                    $l = site_url('cc/topic/resume/' . $link['context']);
                    $bc = $bc = create_breadcrumb(parent_context($link['context']));
                    $send.= <<<PQR
<li><a href="{$l}" tabindex="1"><span>{$link['title']}</span></a> {$bc}</li>
PQR;
                }
            }

            if (!empty($send)) {
                echo '<ul>' . $send . '</ul>';
            }
        } else {
            return $result;
        }
    }

    /**
     * Search text in contexts info
     *
     * @param string $q
     *
     * @return array
     */
    private function _in_context($q) {
        $this->load->module('cc/context');
        $output = $this->_match($q, 'info_context.json');
        $result = array();
        $i = 0;

        foreach ($output as $context) {
            $result[$i]['context'] = preg_replace('/contexts\_/', '', slug_path(strtolower(slug_text(dirname($context)))));

            // Validate if user belongs to context.
            if (!belongs_to('context', $result[$i]['context'], connected_user())) {
                unset($result[$i]);
                continue;
            }

            $context_info = $this->context->info($result[$i]['context']);
            $result[$i]['title'] = $context_info['info']['title'];
            $i ++;
        }

        return $result;
    }

    /**
     * Search in topic info
     *
     * @param string $q
     *
     * @return array
     */
    private function _in_topic($q, $include = 'info_topic.json') {
        $this->load->module('cc/topic');
        $output = $this->_match($q, $include);
        $result = array();
        $account = $this->session->userdata('current_account');
        $i = 0;

        foreach ($output as $topic) {
            $context = preg_replace('/\/\_topics\//', '/', dirname($topic));
            $id_context = explode('/', $context);
            array_shift($id_context);
            array_pop($id_context);

            // Validate if user belongs to context topic.
            if (!belongs_to('context', implode('_', $id_context), connected_user())) {
                unset($result[$i]);
                continue;
            }

            $topic_info = $this->topic->info(preg_replace('/contexts/', '', $context, 1));
            $result[$i]['title'] = $topic_info['info']['title'];
            $result[$i]['context'] = preg_replace('/contexts_/', '', slug_path(strtolower(slug_text($context))));
            $i ++;
        }

        return $result;
    }

    /**
     * Return match files
     *
     * @param type $q
     * @param type $file
     */
    private function _match($q, $file) {
        $account = $this->session->userdata('current_account');
        $cur_dir = getcwd();
        chdir(_INC_ROOT . '_accounts/' . $account);

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $exec = 'Findstr /Smi ' . json_encode(urldecode($q)) . ' ' . $file;
            exec($exec, $output);
            $output = array_map('back2slash', $output);
        } else {
            $exec = 'grep -ilrF ' . json_encode(urldecode($q)) . ' --include="' . $file . '" * | head';
            exec($exec, $output);
        }

        chdir($cur_dir);

        $cleaned = array();

        foreach ($output as $dir) {
            if (strstr($dir, '_trash')) {
                continue;
            }
            $cleaned[] = $dir;
        }

        $output = array_slice($cleaned, 0, 10);

        return $output;
    }

}
