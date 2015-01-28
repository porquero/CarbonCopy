<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Example Class for create Components.
 * 
 * @tutorial
 * - 'echo' doesn't work in method, you should use return to get data or set
 * class variables.
 * - For extend CC you can use CI capacity ($this->_ci variable) to run controllers,
 * models, helpers, etc.
 */
class example extends component{

    /**
     * Template variables.
     *
     * @var string
     */
    public
            $title = '', // Page title.
            $subtitle = '', // Page subtitle.
            $description = ''; // Page description.

    /**
     * Model component
     *
     * @var class
     */
    public $m_example;

    /**
     * Required: Default component action.
     * 
     */
    public function index() {
        $this->title = 'Example 1: CC Module';
        $this->var1 = 'Hello World!';
    }

    /**
     * Only used in this example.
     */
    public function other_view() {
        $this->title = 'Example 2: Other view CC Module';
        $this->var1 = 'Hello my Friend';
    }

}
