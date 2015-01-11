<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Components manager
 *
 * @author Cristian
 */
class component extends MX_Controller {

    /**
     * Load component and its view.
     * 
     * @param string $component
     */
    public function run($component = NULL) {

        if (is_null($component)) {
            redirect();
        }

        list($component_name, $action) = explode('_', $component, 2);
        is_null($action) ? $action = 'index' : NULL;

        include_once _INC_ROOT . '/extends/components/' . "{$component_name}/controllers/{$component_name}.php";

        $component = new $component_name();
        $component->load->model('m_component');

        include_once _INC_ROOT . '/extends/components/' . "{$component_name}/models/m_{$component_name}.php";
        $m_component = "m_{$component_name}";
        $component->$m_component = new $m_component;
        
        $component->$action();

        $this->tpl->variables(
                array(
                    'title' => $component->title,
                    'head' => link_tag('extends/components/'. $component_name . '/assets/styles.css'),
                    'footer' => js_tag('extends/components/'. $component_name . '/assets/scripts.js'),
                    'subtitle' => $component->subtitle,
                    'description' => $component->description,
                    'msg_type' => isset($component->msg_type) ? $component->msg_type : '',
                    'msg' => isset($component->msg) ? $component->msg : '',
                    'component' => $component,
                    'action' => $action,
        ));

        $this->tpl->section('_sidebar', '_sidebar.phtml');
        $this->tpl->section('_view', 'index.phtml');
        $this->tpl->load_view(_TEMPLATE);
    }

}
