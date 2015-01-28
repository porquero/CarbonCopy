<?php

if ( ! defined('BASEPATH'))
	exit('No direct script access allowed');

/**
 * Isolate tests
 *
 * @author Cristian Riffo <criffoh@gmail.com>
 */
class tests extends MX_Controller {

	public function all_topics($context)
	{
		$context = preg_replace('/\_/', '/', $context);
		Plogger::var_dump(glob_recursive(_INC_ROOT . "_accounts/{$this->session->userdata('current_account')}/contexts/{$context}/info_topic.json"));
	}

}


