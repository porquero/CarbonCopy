<?php

/**
 * @package CarbonCopy
 * @subpackage helper
 */

/**
 * Show Breadcrumb for Path
 *
 * @param string $path
 * @param string $id_topic
 *
 * @return string Breadcrumb generated
 */
function create_breadcrumb($path, $id_topic = NULL)
{
	$contexts = explode('_', $path);
	$breadcrumb = '';
	$result = '';

	foreach ($contexts as $context) {
		if ($context == $id_topic) {
			continue;
		}

		$breadcrumb = trim($breadcrumb, '_') . '_' . $context;
		$context_href = site_url('/cc/context/resume/' . trim($breadcrumb, '_'));
		$result .= <<<PQR
<a href="{$context_href}">[{$context}]</a>
PQR;
	}

	return $result;
}

/**
 * Get context from full context (remove last id)
 *
 * @param string $full_context
 *
 * @return string
 */
function parent_context($full_context)
{
	$parent_context = preg_replace('/_[a-z0-9-]*$/', '', $full_context);

	if ($parent_context === $full_context) {
		return '';
	}

	return $parent_context;
}

/**
 * Get Name for actual context/topic from slug path.
 *
 * @param string $slug_path context/topic path slugged
 * @return string
 */
function get_name_from_slug($slug_path)
{
	$name = preg_match('/(\_[^\_]*)$/', $slug_path, $m);
	$name = preg_replace('/\_/', '', $m);

	if (count($name) == 0) {
		return $slug_path;
	}

	return $name[0];
}

/**
 * Return real path topic adding '_topic' directory
 *
 * @param string $topic_path
 * @return string
 */
function topic_real_path($topic_path)
{
	return preg_replace('/(\/[a-zA-Z0-9-]*)$/', '/_topics$1', $topic_path);
}

/**
 * Return real file path for topic
 *
 * @param string $file_path
 * @return string
 */
function file_real_path($file_path)
{
	$file_path = preg_replace('/_/', '/', $file_path);
	$file_path = preg_replace('/(\/[^\/]*)$/', '/_files$1', $file_path);
	$expl = explode('/_files', $file_path);

	$CI = &get_instance();
	$file_path = _INC_ROOT . '_accounts/' . $CI->session->userdata('current_account') . '/contexts/' . topic_real_path($expl[0]) . '/_files' . $expl[1];
	return $file_path;
}

/**
 * Return context full path according sent context
 *
 * @param string $context
 *
 * @return string
 */
function context_real_path($context)
{
	$CI = & get_instance();

	return "_accounts/{$CI->session->userdata('current_account')}/contexts/" . unslug_path($context);
}

/**
 * Return date in account date format.
 *
 * @param string $date Date to convert
 * @param boolean $time Indicates if return date with time also.
 *
 * @return string
 */
function account_date_format($date, $time = false)
{
	if (empty($date)) {
		return 'no date';
	}

	$CI = &get_instance();
	$info = Modules::run('file/read/json_content', "_accounts/{$CI->session->userdata('current_account')}/config.json");

	if ($time === TRUE) {
		return date($info['date_format_time'], strtotime($date));
	}

	return date($info['date_format'], strtotime($date));
}

/**
 * Return format to be used in Mysql queries.
 *
 * @return string
 */
function account_date_format_mysql()
{
	$CI = &get_instance();
	$info = Modules::run('file/read/json_content', "_accounts/{$CI->session->userdata('current_account')}/config.json");

	return $info['date_format_mysql'];
}

/**
 * Get basename for listed files.
 * @tutorial Use with array_walk. Ex: array_walk($file_list, 'file_basename');
 * 
 * @param string $list_files
 */
function file_basename(&$list_files, $key)
{
	$list_files = basename($list_files);
}

/**
 * Remove nultiline line break
 *
 * @param string $string
 *
 * @return string
 */
function nl2br_ml($string)
{
	return preg_replace('/\n/m', '', nl2br($string));
}

/**
 * Make text url compatible
 *
 * @param String $str
 * @param mixed $replace
 * @param string $delimiter
 *
 * @return string
 *
 * @link http://cubiq.org/the-perfect-php-clean-url-generator
 * @link http://www.randomsequence.com/articles/removing-accented-utf-8-characters-with-php/
 */
function slug_text($str, $replace = array(), $delimiter = '-')
{
	$str = urldecode($str);
	$s_pattern = 'ç,æ,œ,á,é,í,ó,ú,à,è,ì,ò,ù,ä,ë,ï,ö,ü,ÿ,â,ê,î,ô,û,å,e,i,ø,u,ñ,Ç,Æ,Œ,Á,É,Í,Ó,Ú,À,È,Ì,Ò,Ù,Ä,Ë,Ï,Ö,Ü,Ÿ,Â,Ê,Î,Ô,Û,Å,E,I,Ø,U,Ñ';

	$r_pattern = 'c,ae,oe,a,e,i,o,u,a,e,i,o,u,a,e,i,o,u,y,a,e,i,o,u,a,e,i,o,u,n';
	$r_pattern .= strtoupper(',' . $r_pattern);

	$_search = explode(",", $s_pattern);
	$_replace = explode(",", $r_pattern);

	$str = str_replace($_search, $_replace, $str);

	if ( ! empty($replace)) {
		$str = str_replace((array) $replace, ' ', $str);
	}

	// Commented because some especial chars generate an error!
	//	$clean = iconv('UTF-8', 'ASCII//TRANSLIT', utf8_encode(trim($str)));
	$clean = preg_replace("/[^a-zA-Z0-9_|+ -\\/]/", '', trim($str));
	$clean = strtolower(trim($clean, '-'));
	$clean = preg_replace("/[_|+ -\.]+/", $delimiter, $clean);

	return $clean;
}

/**
 * Invert windows path separator.
 *
 * @param type $str
 * @return type
 */
function back2slash($str)
{
	return str_replace('\\', '/', $str);
}

/**
 * Return path with final slash, if hasn't
 *
 * @param string $context
 * @return string
 */
function final_slash($context)
{
	return preg_match("/\/$/", $context) ? $context : $context . '/';
}

/**
 * Slug path for use url string
 *
 * @param string $context
 * @return string
 */
function slug_path($context)
{
	return preg_replace('/\//', '_', $context);
}

/**
 * Unslug path converting in file system.
 *
 * @param type $context
 * @return type
 */
function unslug_path($context)
{
	return preg_replace('/\_/', '/', $context);
}

/**
 * Unslug path for use in timeline
 *
 * @param type $context
 * @return type
 */
function unslug_for_timeline($context)
{
	return '[' . preg_replace('/\_/', '][', $context) . ']';
}

/**
 * Get all context from account
 * 
 * @param atring $account_path
 *
 * @return array
 *
 * @see http://php.net/manual/en/function.glob.php#92565
 */
function account_contexts($account_path)
{
	$return = array();
	$ds = DIRECTORY_SEPARATOR;
	$path = _INC_ROOT . "_accounts{$ds}{$account_path}{$ds}contexts{$ds}";
	$filter = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? '[a-z0-9-]*' : '[^_]*';
	$dir = $path . $filter;
	$replace = array($path, $ds);
	$CI = & get_instance();
	$CI->load->module('cc/context');

	while ($dirs = glob($dir, GLOB_ONLYDIR)) {
		$dir .= $ds . $filter;
		$dirs_r = array();

		foreach ($dirs as $dirr) {
			$context = str_replace($replace, array('', '_'), $dirr);

			$info = $CI->context->info($context);
			$dirs_r[str_replace($path, '', $dirr)] = array('title' => $info['info']['title'], 'context' => $context);
		}

		$return = array_merge($return, $dirs_r);
	}

	return $return;
}

/**
 * Return connected user.
 * 
 * @return mixed string|false
 */
function connected_user()
{
	$CI = & get_instance();

	return $CI->session->userdata('connected_user');
}

/**
 * Helper Get current account info.
 * 
 * @return array
 */
function current_account_info()
{
	$CI = & get_instance();

	return $CI->session->userdata('current_account_info');
}

/**
 * Helper Get user info.
 *
 * @return array
 */
function user_info()
{
	$CI = & get_instance();

	return $CI->session->userdata('user_info');
}

/**
 * Helper to validate if user belongs to context.
 *
 * @param string $context_type account|context|topic
 * @param string $context_or_user Can be user (for account) or context (for other)
 * @param string $user User for context
 * @param boolean $strict TRUE: For users belongs to topic only. FALSE: For conected users only.
 *
 * @return boolean
 */
function belongs_to($context_type, $context_or_user, $user = NULL, $strict = FALSE)
{
	if ($context_type === 'account') {
		return Modules::run('cc/participant/belongs_to_account', $context_or_user);
	}

	return Modules::run('cc/user/belongs_to_' . $context_type, $context_or_user, $user, $strict);
}

/**
 * Helper to get if user is connected.
 * 
 * @return boolean
 */
function is_connected()
{
	$CI = & get_instance();

	$CI->load->helper('url');

	$CI->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0");
	$CI->output->set_header("Pragma: no-cache");

	if (connected_user() === FALSE) {
		redirect('cc/user/login_form/' . base64_encode(current_url()));
		exit;
	}

	return TRUE;
}

/**
 * Validate if user is logged and show sent logged data if yes.
 *
 * @param type $participants
 * @param string $data_logged
 * @param string $data_not_logged
 * @param type $user_locking Used for not show data if any user locking context.
 *
 * @return string
 */
function logged_data($participants, $data_logged, $data_not_logged = '', $user_locking = FALSE)
{
	if ($user_locking !== FALSE) {
		return '';
	}

	$plist = '';
	foreach ($participants as $participant) {
		$plist .= $participant['info']['id'] . '|';
	}

	if (FALSE !== connected_user() && strstr($plist, connected_user()) !== FALSE) {
		return $data_logged;
	}

	return $data_not_logged;
}

/**
 * Return class according to visibility and participants of context
 *
 * @param type $info_context
 * 
 * @return string Css class icon
 */
function context_icon($info_context)
{
	if (isset($info_context['info']['visibility'])) {
		if ($info_context['info']['visibility'] === 'public') {
			return 'public';
		}
		elseif ($info_context['info']['visibility'] === 'private' AND ! empty($info_context['info']['participants'])) {
			return 'private-lock';
		}
	}
	else {
		if ( ! empty($info_context['info']['participants'])) {
			return 'private-lock';
		}
	}

	return '';
}

/**
 * Return class according to visibility and participants of topic
 *
 * @param type $info_topic
 * 
 * @return string Css class icon
 */
function topic_icon($info_topic)
{
	if ( ! empty($info_topic['info']['participants'])) {
		return 'private-lock';
	}

	return '';
}

/**
 * Return site_url without slash
 *
 * @return string
 */
function site_url_ws()
{
	return preg_replace('/\/$/', '', site_url());
}

/**
 * Delete not empty directory
 *
 * @param string $dir
 *
 * @see http://php.net/manual/en/function.rmdir.php#98622
 */
function rrmdir($dir)
{
	if (is_dir($dir)) {
		$objects = scandir($dir);
		foreach ($objects as $object) {
			if ($object != "." && $object != "..") {
				if (filetype($dir . "/" . $object) === "dir") {
					rrmdir($dir . "/" . $object);
				}
				else {
					unlink($dir . "/" . $object);
				}
			}
		}
		reset($objects);
		rmdir($dir);
	}
}

function clean_uploaded_file_name($file_name)
{
	$file_name = preg_replace('/[ -]{2,}/', '-', preg_replace('/[\_\(\)]/', '-', $file_name));
	preg_match('/\.[a-z0-9]*$/', $file_name, $ext);
	preg_match('/(.*)\.[a-z0-9]*$/', $file_name, $name);
	if (isset($name[1]) AND isset($ext[0])) {
		$file_name = trim($name[1], " \t\n\r\0\x0B\-") . $ext[0];
	}

	return $file_name;
}
