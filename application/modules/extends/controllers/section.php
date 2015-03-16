<?php

if ( ! defined('BASEPATH'))
	exit('No direct script access allowed');

include_once dirname(__FILE__) . '/cc_extends.php';

/**
 * Sections manager
 *
 * @author Cristian
 */
class section extends cc_extends {

	/**
	 * Load section and run.
	 *
	 * @param string $section
	 */
	public function run($section = NULL, $context = NULL, $id_topic = NULL)
	{
		is_connected();
		// Avoid run out of controller. Disabled to make power sections!
//        if (!strstr(serialize($this->load), 'application/modules/cc/')) {
//            exit('Not allowed.');
//        }

		if (strlen((string) $section) === 0 || strlen((string) $context) === 0) {
			echo 'Too few params.';
			return FALSE;
		}

		list($section_name, $action) = explode('_', $section, 2);
		is_null($action) ? $action = 'index' : NULL;

		// Validate if enabled.
		$account_config = Modules::run('account/configuration/load');
		if ( ! (array_key_exists($section_name, $account_config['extends']['sections']) && $account_config['extends']['sections'][$section_name] === TRUE)) {
			echo $section_name . ': not enabled. ';
			return FALSE;
		}

		include_once _INC_ROOT . '/extends/sections/' . "{$section_name}/controllers/{$section_name}.php";
		$params = json_decode(file_get_contents(_INC_ROOT . '/extends/sections/' . "{$section_name}/{$section_name}.json"));

		$section = new $section_name();
		$section->load->model('m_component');

		// Load section model if exists.
		$section_model = _INC_ROOT . '/extends/sections/' . "{$section_name}/models/m_{$section_name}.php";
		if (is_file($section_model)) {
			include_once $section_model;
			$m_section = "m_{$section_name}";
			$section->$m_section = new $m_section;
		}

		$section->$action($context, $id_topic);

		$this->tpl->variables(
			array(
					'head' => link_tag('extends/sections/' . $section_name . '/assets/styles.css'),
					'footer' => js_tag('extends/sections/' . $section_name . '/assets/scripts.js'),
					'section' => $section,
					'action' => $action,
		));

		$this->tpl->section('_section_' . $params->position, 'extends/section/index.phtml');
	}

	/**
	 * Return installed sections indicating if it is enable (true) or not (false)
	 * in the account extends manager.
	 *
	 * @return array Components installed.
	 */
	public function installed()
	{
		return parent::installed('sections');
	}

	/**
	 * Activate component.
	 *
	 * @param string $component
	 */
	public function activate($component)
	{
		return parent::_activation($component, 'sections', TRUE);
	}

	/**
	 * Desactivate component.
	 *
	 * @param string $component
	 */
	public function desactivate($component)
	{
		return parent::_activation($component, 'sections', FALSE);
	}

}
