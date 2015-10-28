<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Isolate tests
 *
 * @author Cristian Riffo <criffoh@gmail.com>
 */
class Tests extends MX_Controller {

    public function all_topics($context) {
        $context = preg_replace('/\_/', '/', $context);
        Plogger::var_dump(glob_recursive(_INC_ROOT . "_accounts/{$this->session->userdata('current_account')}/contexts/{$context}/info_topic.json"));
    }

    public function glob_r() {
        Plogger::var_dump(glob(_INC_ROOT . '/extends/components/*'));
    }

    public function emails()
    {
        $this->load->model('cc/m_user');
        
        Plogger::var_dump($this->m_user->emails());
    }
}
