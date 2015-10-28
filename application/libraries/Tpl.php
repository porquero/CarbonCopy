<?php

/**
 * Template system with buffer for merge data in same variable or section
 *
 * @package Playa
 * @subpackage Tools
 * @author porquero
 */
class Tpl {

    private
      $_variables = array(),
      $_section = array();

    /**
     * Set variable to be used in template view
     *
     * @param string $name
     * @param mixed $value
     * @param boolean $concat Concatenate values if true.
     * @param boolean $before Decides if concat at begining or not.
     *
     * @return mixed
     */
    public function variables($name, $value = NULL, $concat = FALSE, $before = FALSE)
    {
        if (is_array($name) == true) {
            foreach ($name as $n => $v) {
                $this->_set_var($n, $v, $concat, $before);
            }
        }
        else {
            $this->_set_var($name, $value, $concat, $before);
        }
    }

    /**
     * Set value to variable name
     *
     * @param string $name
     * @param mixed $value
     * @param boolean $concat If value is a string, concatenate values if true.
     * @param boolean $before Decides if concat at begining or not.
     *
     * @return void
     */
    private function _set_var($name, $value, $concat = FALSE, $before = FALSE)
    {
        if (array_key_exists($name, $this->_variables) === false) {
            $this->_variables[$name] = '';
        }

        if (is_string($value) === TRUE && $concat === TRUE) {
            if ($before === TRUE) {
                $this->_variables[$name] = $value . $this->_variables[$name];
            }
            else {
                $this->_variables[$name] .= $value;
            }
        }
        else {
            $this->_variables[$name] = $value;
        }
    }

    /**
     * Set section to be used in template view
     *
     * @param string $name
     * @param string $view
     * @param boolean $replace_all
     * @return mixed
     *
     * @tutorial Support views with full path and current controller
     */
    public function section($name, $view = null, $replace_all = false)
    {
        $CI = & get_instance();
        if ($view === null) {
            if (isset($this->_section[$name]) == false) {
                return null;
            }
            return $this->_section[$name];
        }
        else {
            if (array_key_exists($name, $this->_section) === false || $replace_all === true) {
                $this->_section[$name] = '';
            }
            $this->_section[$name][] = strstr($view, '/') ? $view : $CI->router->fetch_class() . '/' . $view;
        }
    }

    /**
     * Load view with section and variables seted
     *
     * @param string $view
     * @param array $vars
     * @param boolean $return
     *
     * @return void
     *
     * @todo Agregar generador de identificador único para nombre de sección ya que si se usa más de una vez
     * este método no se puede usar la misma variable _vire para las vistas!
     */
    public function load_view($view, $vars = array(), $return = false)
    {
        $CI = & get_instance();
        $vars = $vars === array() ? $this->_variables : $vars;

        // Load sections
        $sections = array();
        foreach ($this->_section as $section => $views) {
            if (array_key_exists($section, $sections) === false) {
                $sections[$section] = '';
            }
            foreach ($views as $v) {
                $sections[$section] .= $CI->load->view($v, $this->_variables, true);
            }
        }

        log_message('debug', 'TPL: Loadding ' . $view);
        
        $result = $CI->load->view($view, array_merge($sections, $vars), TRUE);

        if ($return === TRUE) {
            return $result;
        }

        echo $result;
    }

}
