<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Show contexts as sitemap format.
 * 
 */
class sitemap extends component {

    /**
     * Template variables.
     *
     * @var string
     */
    public
            $title = 'Sitemap', // Page title.
            $subtitle = '', // Page subtitle.
            $description = ''; // Page description.

    /**
     * Required: Default component action.
     */

    /**
     * Principal view.
     */
    public function index() {
        $this->top_contexts = Modules::run('cc/resume/contexts', $this->session->userdata('current_account'));
    }

    /**
     * Get contexts tree.
     * 
     * @param array $contexts
     * 
     * @return string
     */
    public function contexts_tree($contexts) {
        $return = '';

        if ($contexts) {
            $return .= '<ul>';
            foreach ($contexts as $context_slug => $context_info) {
                if (!belongs_to('context', $context_slug, connected_user())) {
                    continue;
                }


                $context_name = preg_match('/\_[a-zA-Z0-9-]*$/', $context_slug, $m);
                $context_name = preg_replace('/\_/', '', $m);
                $context_link = site_url('/cc/context/resume/' . $context_slug);
                $icon = context_icon($context_info);

                $return .= '<li><a href="' . $context_link . '" class="context">'
                        . $context_info['info']['title'] . ' <span class="'
                        . $icon . '"></span></a>';

                // Check if has contexts.
                $info = Modules::run('cc/context/info', $context_slug);

                $topics = Modules::run('cc/timeline/contexts_topics', $context_slug, TRUE, connected_user(), NULL);

                if (count($topics)) {
                    foreach ($topics as $topic) {
                        $link = site_url('/cc/topic/resume/' . $topic->context . $topic->id_topic);
                        $return .= <<<PQR
<li><a href="{$link}" class="topic"><b>{$topic->title} <span class="{$icon}"></span></b></a></li>
PQR;
                    }
                }

                $return .= $this->contexts_tree($info['contexts']) . '</li>';
            }

            $return .= '</ul>';
        }

        return $return;
    }

}
