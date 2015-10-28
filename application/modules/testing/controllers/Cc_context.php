<?php

if ( ! defined('BASEPATH'))
	exit('No direct script access allowed');

/**
 * Tests for cc/context controller
 *
 * @author Cristian Riffo <criffoh@gmail.com>
 */
class Cc_context extends MX_Controller {

	public function labels($context)
	{
		$this->load->module('cc/context');

		Plogger::var_dump($this->context->labels($context));
	}
}
