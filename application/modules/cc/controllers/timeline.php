<?php

if ( ! defined('BASEPATH'))
	exit('No direct script access allowed');

/**
 * Manage account timeline.
 *
 * @package CarbonCopy
 * @subpackage cc
 * @author porquero
 */
class timeline extends MX_Controller {

	public $menu_act = array(
			'all' => '',
			'to_me' => '',
			'for_me' => '',
	);

	/**
	 * Get global timeline
	 *
	 * @param boolean $return Decides if return or not result
	 * @return string
	 */
	public function get_for_resume($return = FALSE, $participant_id = NULL, $ts_date = NULL)
	{
		$this->load->model('m_timeline');

		$timeline = $this->m_timeline->get_for_resume($participant_id, $ts_date);

		$this->tpl->variables(array(
				'timeline' => $timeline,
		));

		if ($return === TRUE) {
			return $timeline;
		}

		$this->tpl->section('_view', 'get_timeline.phtml');
		$this->tpl->load_view(_TEMPLATE_BLANK_TXT);
	}

	/**
	 * Get timeline for actual context
	 * 
	 * @param string $context context
	 * @param boolean $return Decides if return or not result
	 * @return string
	 */
	public function get_for_context($context, $return = FALSE, $participant_id = NULL, $ts_date = NULL)
	{
		$this->load->model('m_timeline');

		$timeline = $this->m_timeline->get_for_context($context, $participant_id, $ts_date);

		$this->tpl->variables(array(
				'context' => $context,
				'timeline' => $timeline,
		));

		if ($return === TRUE) {
			return $timeline;
		}

		$this->tpl->section('_view', 'get_timeline.phtml');
		$this->tpl->load_view(_TEMPLATE_BLANK_TXT);
	}

	/**
	 * Get timeline for actual context
	 *
	 * @param string $context context
	 * @param boolean $return Decides if return or not result
	 * @return string
	 */
	public function contexts_topics($context, $return = FALSE, $participant_id = NULL, $ts_date = NULL)
	{
		$this->load->model('m_timeline');

		$timeline = $this->m_timeline->contexts_topics($context, $participant_id, $ts_date);

		$this->tpl->variables(array(
				'context' => $context,
				'timeline' => $timeline,
		));

		if ($return === TRUE) {
			return $timeline;
		}

		$this->tpl->section('_view', 'get_timeline.phtml');
		$this->tpl->load_view(_TEMPLATE_BLANK_TXT);
	}

	/**
	 * Get timeline for actual topic
	 * 
	 * @param string $context context
	 * @param boolean $return Decides if return or not result
	 * @return string
	 */
	public function get_for_topic($context, $return = FALSE)
	{
		$this->load->model('m_timeline');

		$timeline = $this->m_timeline->get_for_topic($context);

		$this->tpl->variables(array(
				'timeline' => $timeline,
		));

		if ($return === TRUE) {
			return $timeline;
		}

		$this->tpl->section('_view', 'get_timeline_simplified.phtml');
		$this->tpl->load_view(_TEMPLATE_BLANK_TXT);
	}

	/**
	 * Get timeline for participant
	 *
	 * @param type $username
	 *
	 * @return string
	 */
	public function get_for_participant($username, $return = FALSE, $participant_id = NULL, $ts_date = NULL)
	{
		$this->load->model('m_timeline');

		$timeline = $this->m_timeline->get_for_user($username, $participant_id, $ts_date);

		$this->tpl->variables(array(
				'timeline' => $timeline,
		));

		if ($return === TRUE) {
			return $timeline;
		}

		$this->tpl->section('_view', 'get_timeline.phtml');
		$this->tpl->load_view(_TEMPLATE_BLANK_TXT);
	}

}
