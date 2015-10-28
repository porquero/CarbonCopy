<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Example using component model.
 *
 * @author Cristian
 */
class M_example extends m_component {

    public function timeline() {
        $q = <<<PQR
SELECT * 
FROM  `cc_timeline` 
limit 10
PQR;

        $r = $this->db->query($q);

        return $r->result();
    }

}
