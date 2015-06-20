<?php

if ( ! defined('BASEPATH'))
	exit('No direct script access allowed');

/*
  |--------------------------------------------------------------------------
  | File and Directory Modes
  |--------------------------------------------------------------------------
  |
  | These prefs are used when checking and setting modes when working
  | with the file system.  The defaults are fine on servers with proper
  | security, but you may wish (or even need) to change the values in
  | certain environments (Apache running a separate process for each
  | user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
  | always be used to set the mode correctly.
  |
 */
define('FILE_READ_MODE', 0644);
define('FILE_WRITE_MODE', 0666);
define('DIR_READ_MODE', 0755);
define('DIR_WRITE_MODE', 0777);

/*
  |--------------------------------------------------------------------------
  | File Stream Modes
  |--------------------------------------------------------------------------
  |
  | These modes are used when working with fopen()/popen()
  |
 */

define('FOPEN_READ', 'rb');
define('FOPEN_READ_WRITE', 'r+b');
define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 'wb'); // truncates existing file data, use with care
define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 'w+b'); // truncates existing file data, use with care
define('FOPEN_WRITE_CREATE', 'ab');
define('FOPEN_READ_WRITE_CREATE', 'a+b');
define('FOPEN_WRITE_CREATE_STRICT', 'xb');
define('FOPEN_READ_WRITE_CREATE_STRICT', 'x+b');

/* CC constants */
// Version.
define('_CC_VERSION', '1.1');

// Templates.
define('_TEMPLATE', 'web_tpl.phtml');
define('_TEMPLATE_BLANK_HTML', 'BLANK_html.phtml');
define('_TEMPLATE_BLANK_TXT', 'BLANK_txt.phtml');

// Include path.
define('_INC', dirname(__FILE__) . '/../');
define('_INC_ROOT', dirname(__FILE__) . '/../../');

// Message types
define('_MSG_OK', 'msg_ok');
define('_MSG_WARNING', 'msg_warning');
define('_MSG_ERROR', 'msg_error');

/* End of file constants.php */
/* Location: ./application/config/constants.php */