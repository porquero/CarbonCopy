<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Manage account configuration
 *
 * @package CarbonCopy
 * @subpackage account
 * @author porquero
 */
class configuration extends MX_Controller {

    /**
     * Get item value from account config file.
     * If $item == NULL, return all config data.
     *
     * @param string $item item name
     * @return mixed array|string
     */
    public function load($item = NULL) {
        $this->load->module('file/read');

        $config = $this->read->json_content("_accounts/{$this->session->userdata('current_account')}/config.json");

        if ($item == NULL) {
            return $config;
        }

        return $config[$item];
    }

}
