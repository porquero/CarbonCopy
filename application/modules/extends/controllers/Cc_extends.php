<?php

/**
 * Global extends actions.
 *
 * @author Cristian
 */
class cc_extends extends MX_Controller {

    /**
     * Return installed extends indicating if it is enable (true) or not (false)
     * in the account extends manager.
     * 
     * @return array Extends installed.
     */
    public function installed($type = 'components') {
        $components_path = glob(_INC_ROOT . '/extends/' . $type . '/*');
        $extends = array();
        $account_config = Modules::run('account/configuration/load');

        foreach ($components_path as $path) {
            $basename = basename($path);
            $extends[$basename]['id'] = $basename;
            $extends[$basename]['data'] = json_decode(file_get_contents($path . '/' . $basename . '.json'));
            $extends[$basename]['enabled'] = array_key_exists($basename, $account_config['extends'][$type]) && $account_config['extends'][$type][$basename] === TRUE;
        }

        return $extends;
    }

    /**
     * Activate/Desactivate extend.
     * 
     * @param string $extend
     */
    protected function _activation($extend, $type = 'components', $status = FALSE) {
        is_connected('administrator');

        $this->load->module('file/write');

        $account_config = Modules::run('account/configuration/load');
        $account_config['extends'][$type][$extend] = $status;
        $config_saved = $this->write->archive("_accounts/{$this->session->userdata('current_account')}/config.json", json_encode($account_config));

        if ($this->input->is_ajax_request()) {
            echo (int) $config_saved;
        } else {
            return $config_saved;
        }
    }

}
