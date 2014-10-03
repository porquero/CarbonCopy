<?php

/**
 * Log Class
 * Whit this class you can log text and expressions
 *
 * @package Playa
 * @subpackage Tools
 * @author porquero
 * @link http://www.playa-soluciones.cl/blog/7-investigacion-y-desarrollo/44-plogger
 * @version 1.2
 */
class Plogger {

	/**
	 * Logs Path
	 * @var string
	 */
	private static $path = '';

	/**
	 * Log filename
	 * @var string
	 */
	private static $fileName;

	/**
	 * Log filename ext
	 * @var string
	 */
	private static $fileExt;

	/**
	 * Flag to indicate if decode the content from utf8
	 * @var boolean
	 */
	private static $utf8decode;

	/**
	 * Plogger Init
	 */
	public static function init($utf8decode = true)
	{

		// @todo Esto no es así!
		self::$path = dirname(__FILE__) . '/../logs/';

		self::$fileName = date('d_m_Y');
		self::$fileExt = '.log';
		self::$utf8decode = $utf8decode || FALSE;
	}

	/**
	 * Set Path for logging
	 *
	 * @param string $path
	 */
	public static function setPath($path)
	{
		self::isInit();
		preg_match('/\/$/', $path) ? null : $path .= '/';
		self::$path = $path;
	}

	/**
	 * Log string
	 *
	 * @param string $text
	 * @param boolean $saverHour
	 */
	public static function log($text, $saverHour = true)
	{
		self::isInit();
		self::$utf8decode ? $text = utf8_decode($text) : null;
		$ddf = fopen(self::$path . self::$fileName . self::$fileExt, 'a');
		if ($saverHour) {
			fwrite($ddf, "[" . date("H:i:s") . "]_______________________________________________________________\r\n$text\r\n");
		}
		else {
			fwrite($ddf, "$text\r\n");
		}
		fclose($ddf);
	}

	/**
	 * Log Expression
	 * @param mixed $expression
	 */
	public static function logVar($expression, $debug = true)
	{
		self::log((string) $expression);
		self::log('URI: ' . $_SERVER['REQUEST_URI'], false);
		self::log('Expression:', false);
		self::log(urlencode(serialize($expression)), false);
		if ($debug) {
			self::log('Debug:', false);
			self::log(urlencode(serialize(debug_backtrace())), false);
			self::log('SERVER: ', false);
			self::log(urlencode(serialize($_SERVER)), false);
		}
	}

	/**
	 * Autoset Vars
	 */
	private static function isInit()
	{
		self::$path === '' ? self::init() : null;
	}

	/**
	 * Decode serialized data and show result
	 * @tutorial You can find serialized data in log file
	 */
	public static function decodeLog()
	{
		$html = <<<PQR
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<form action="" method="post">
    <textarea name="log"></textarea>
    <input type="submit">
</form>
PQR;
		echo $html;
		if ( ! empty($_POST['log'])) {
			$result = unserialize(urldecode($_POST['log']));
			self::var_dump($result);
		}
	}

	/**
	 * Set flag to decide if decode or not from utf8
	 * @param boolean $decode
	 * @tutorial Util for international chars
	 */
	public static function utf8decode($decode)
	{
		self::$utf8decode = $decode || FALSE;
	}

	/**
	 * Better var_dump
	 * @param mixed $expresion
	 * @param boolean $debug
	 */
	static function var_dump($expresion, $debug = false)
	{
		echo '<pre style="background:#CCC;border:solid 1px #EEE;border-top:solid 2px #666;color:#000;padding:7px;cursor:default">';
		var_dump($expresion);
		if ($debug) {
			echo '<strong>Debug:</strong><br />';
			var_export(debug_backtrace());
		}
		echo '</pre>';
	}

}
