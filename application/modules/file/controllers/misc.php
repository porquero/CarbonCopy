<?php

if ( ! defined('BASEPATH'))
	exit('No direct script access allowed');

/**
 * Miscelaneous file functions.
 *
 * @package CarbonCopy
 * @subpackage file
 * @author porquero
 *
 * @deprecated All these function are helper now! Please see cc_helper.php
 */
class Misc extends MX_Controller {

	/**
	 * Return path with final slash, if hasn't
	 *
	 * @param string $context
	 * @return string
	 */
	public function final_slash($context)
	{
		return preg_match("/\/$/", $context) ? $context : $context . '/';
	}

	/**
	 * Slug path for use url string
	 *
	 * @param string $context
	 * @return string
	 *
	 * @deprecated Replaced with helper slug_path!
	 */
	public function slug_path($context)
	{
		return preg_replace('/\//', '_', $context);
	}

	/**
	 * Unslug path converting in file system.
	 * 
	 * @param type $context
	 * @return type
	 */
	public function unslug($context)
	{
		return preg_replace('/\_/', '/', $context);
	}

	/**
	 * Unslug path for use in timeline
	 * 
	 * @param type $context
	 * @return type
	 */
	public function unslug_for_timeline($context)
	{
		return '[' . preg_replace('/\_/', '][', $context) . ']';
	}

}


