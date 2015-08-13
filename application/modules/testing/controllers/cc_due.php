<?php

/**
 * Tests for m_due model
 *
 * @author Cristian Riffo <criffoh at gmail.com>
 */
class cc_due extends MX_Controller {

	public function range()
	{
		$this->load->model('cc/m_due');

		Plogger::var_dump($this->m_due->range('2014-11-05', 7));
	}

	public function range_future()
	{
		$this->load->model('cc/m_due');

		Plogger::var_dump($this->m_due->range_future('2014-11-05'));
	}

	public function change()
	{
		$this->load->model('cc/m_due');
		Plogger::var_dump($this->m_due->change('proyecto-1_545a93df7c915', '2014-11-05'));
	}

	public function move()
	{
		$this->load->model('cc/m_due');
		Plogger::var_dump($this->m_due->move_topic('proyecto-1_545b8a09da3ea', 'proyecto-1_subproyecto_545b8a09da3ea'));
	}

	public function date_range()
	{
		$this->load->module('cc/due');

		Plogger::var_dump($this->due->date_range('2014-11-05'));
	}

	public function line()
	{
		$this->load->module('cc/due');

		Plogger::var_dump($this->due->line_range());
	}

	public function date_line()
	{
		$this->load->module('cc/due');

		echo '<link href="http://localhost/cc/pub/web_tpl/css/main.css?1415117830" rel="stylesheet" type="text/css" />';
		$this->due->date_line();
	}

}
