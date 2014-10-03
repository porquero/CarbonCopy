<?php

if ( ! defined('BASEPATH'))
	exit('No direct script access allowed');

/**
 * Manage user files.
 *
 * @package CarbonCopy
 * @subpackage cc
 * @author porquero
 */
class file extends MX_Controller {

	/**
	 * Make downloable file from slug_context
	 *
	 * @param string $slug_context
	 */
	public function download($slug_context)
	{
		is_connected();

		$this->load->helper('file');

		$file = urldecode(file_real_path($slug_context));

		if ($file === FALSE) {
			die('Error: File not found.');
		}

		// Set headers.
		header('Cache-Control: public');
		header('Content-Description: File Transfer');
		header('Content-Disposition: attachment; filename=' . urldecode(get_name_from_slug($slug_context)));
		header('Content-Type: ' . get_mime_by_extension($file));
		header('Content-Transfer-Encoding: binary');

		// Read the file from disk
		readfile($file);
	}

	/**
	 * Upload file for especified path
	 * 
	 * @param string $path_upload
	 * @return array
	 */
	public function upload($path_upload)
	{
		is_connected();

		$config['upload_path'] = $path_upload;
		$config['allowed_types'] = '*';
		$config['max_size'] = 50000;
		$config['max_width'] = 4000;
		$config['max_height'] = 4000;
		$config['overwrite'] = FALSE;
		$config['remove_spaces'] = FALSE;
		// Clean filename. TODO Is it neccesary?
		$config['file_name'] = preg_replace('/[ -]{2,}/', '-', preg_replace('/[\_\(\)]/', '-', $_FILES['userfile']['name']));
		preg_match('/\.[a-z0-9]*$/', $config['file_name'], $ext);
		preg_match('/(.*)\.[a-z0-9]*$/', $config['file_name'], $name);
		if (isset($name[1]) AND isset($ext[0])) {
			$config['file_name'] = trim($name[1], " \t\n\r\0\x0B\-") . $ext[0];
		}
//        $config['max_filename'] = 64;

		$this->load->library('upload', $config);

		if ( ! $this->upload->do_upload()) {
			$error = array('error' => $this->upload->display_errors());

			return array(
					'result' => 'fail',
					'data' => $error
			);
		}
		else {
			$data = array('upload_data' => $this->upload->data());

			return array(
					'result' => 'ok',
					'data' => $data
			);
		}
	}

}
