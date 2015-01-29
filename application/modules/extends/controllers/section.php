<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Sections manager
 *
 * @author Cristian
 */
class section extends MX_Controller {

    /**
     * Load section and run.
     * 
     * @param string $section
     */
    public function run($section = NULL) {
        // Avoid run out of controller.
        if (!strstr(serialize($this->load), 'application/modules/cc/')) {
            exit('Not allowed.');
        }

        if (strlen((string) $section) === 0) {
            echo 'Invalid section name.';
        }


        list($section_name, $action) = explode('_', $section, 2);
        is_null($action) ? $action = 'index' : NULL;

        include_once _INC_ROOT . '/extends/sections/' . "{$section_name}/controllers/{$section_name}.php";

        $section = new $section_name();
        $section->load->model('m_component');

        // Load section model if exists.
        $section_model = _INC_ROOT . '/extends/sections/' . "{$section_name}/models/m_{$section_name}.php";
        if (is_file($section_model)) {
            include_once $section_model;
            $m_section = "m_{$section_name}";
            $section->$m_section = new $m_section;
        }

        $section->$action();

        $this->tpl->variables(
                array(
                    'head' => link_tag('extends/sections/' . $section_name . '/assets/styles.css'),
                    'footer' => js_tag('extends/sections/' . $section_name . '/assets/scripts.js'),
                    'section' => $section,
                    'action' => $action,
        ));

        $this->tpl->section('_section', 'extends/section/index.phtml');
    }

}
