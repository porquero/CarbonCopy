<?php

/**
 *
 * @author Cristian
 */
class m_component extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->db = $this->load->database('default', TRUE);
    }

}
