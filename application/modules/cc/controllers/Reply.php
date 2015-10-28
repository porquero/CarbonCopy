<?php

if ( ! defined('BASEPATH'))
	exit('No direct script access allowed');

/**
 * Manage topic replies
 *
 * @package CarbonCopy
 * @subpackage cc
 * @author porquero
 */
class reply extends MX_Controller {

	/**
	 * Filename for topic replies.
	 *
	 * @var string
	 */
	private $replies_filename = 'replies.csv';

	/**
	 * Get list of replies for topic
	 *
	 * @param string $topic_context
	 * @return array
	 */
	public function get_for_topic($topic_context)
	{
		$this->load->module('file/read');
		$this->load->module('file/misc');

		$topic_context = $this->misc->final_slash($topic_context);

		return $this->read->json_content($topic_context . $this->replies_filename);
	}

}


