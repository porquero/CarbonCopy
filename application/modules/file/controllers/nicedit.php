<?php

if ( ! defined('BASEPATH'))
	exit('No direct script access allowed');

/**
 *
 * @author Cristian Riffo <criffoh@gmail.com>
 */
class nicedit extends MX_Controller {

	private $nicupload_allowed_extensions = array('jpg', 'jpeg', 'png', 'gif', 'bmp');

	/**
	 * Hacked version for upload images using nicedit.
	 *
	 * @return boolean
	 */
	public function simple_upload()
	{
		$this->load->module('cc/file');

		$this->file_upload_result = $this->file->upload(_INC_ROOT . 'pub/images/');

		if ($this->file_upload_result['result'] !== 'ok') {
			echo json_encode(array('error' => strip_tags($this->file_upload_result['data']['error'])));

			return FALSE;
		}

		$file = $_FILES['userfile'];
		$image = $file['tmp_name'];
		@$size = getimagesize($image);

		$status = array();
		$status['done'] = 1;
		$status['width'] = $size[0];
		$status['noprogress'] = true;
		$status['upload']['links']['original'] = base_url() . '/pub/images/' . $this->file_upload_result['data']['upload_data']['file_name'];
		$status['upload']['links']['imgur_page'] = base_url() . '/pub/images/' . $this->file_upload_result['data']['upload_data']['file_name'];
		$status['upload']['links']['delete_page'] = base_url() . '/pub/images/' . $this->file_upload_result['data']['upload_data']['file_name'];
		$status['upload']['links']['small_square'] = base_url() . '/pub/images/' . $this->file_upload_result['data']['upload_data']['file_name'];
		$status['upload']['links']['large_thumbnail'] = base_url() . '/pub/images/' . $this->file_upload_result['data']['upload_data']['file_name'];

		echo json_encode($status);

		return TRUE;
	}

}
