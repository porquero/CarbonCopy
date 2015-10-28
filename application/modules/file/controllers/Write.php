<?php

if ( ! defined('BASEPATH'))
	exit('No direct script access allowed');

/**
 * File write methods
 *
 * @package CarbonCopy
 * @subpackage file
 * @author porquero
 */
class Write extends MX_Controller {

	/**
	 * Create archive and add content using mode received
	 * 
	 * @param string $archive_path
	 * @param binary $content
	 * @param string $mode
	 * 
	 * @return bool Returns true if archive is created
	 */
	public function archive($archive_path, $content, $mode = 'w+')
	{
		$fp = fopen($archive_path, $mode);
		if (is_bool($fp)) {
			return $fp;
		}

		fwrite($fp, $content);
		fclose($fp);

		return file_exists($archive_path);
	}

	/**
	 * Write 1 line in csv file
	 *
	 * @param string $archive_path
	 * @param array $fields
	 * @param string $mode
	 * @return boolean
	 */
	public function csv_archive_line($archive_path, $fields, $mode = 'a+')
	{
		$fp = fopen($archive_path, $mode);
		fputcsv($fp, $fields);
		fclose($fp);

		return file_exists($archive_path);
	}

	/**
	 * Create directory for path name
	 * 
	 * @param string $directory_path_name
	 * @return bool
	 */
	public function dir($directory_path_name)
	{
		if (is_dir($directory_path_name) === TRUE) {
			return FALSE;
		}

		return @mkdir($directory_path_name);
	}

}


