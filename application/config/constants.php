<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');


/**
 * Upload Path
 */
define('STR_UPLOAD_PATH',		'/api_MunchScoutApp/upload/');
define('STR_PHOTO_PREFIX',		'http://hoppers.com.mx/api_MunchScoutApp/upload/');

/**
 * Progress Type
 */
define('TYPE_PROGRESS_RESERVE',			0);
define('TYPE_PROGRESS_INPROGRESS',		1);
define('TYPE_PROGRESS_COMPLETE',		2);
define('TYPE_PROGRESS_DECLINE',			10);
define('TYPE_PROGRESS_CUSTOMMER_CANCEL',100);
define('TYPE_PROGRESS_STYLER_CANCEL',	101);

/**
 * App Error Codes
 */
// Packet Error
define('ERR_PACKET_NO_FIELD',		1000);

// User Error
define('ERR_USER_SIGNUP_FAILED',		600);
define('ERR_USER_EMAIL_DUPLICATE',		601);
define('ERR_USER_INVALID_PASSWORD',		602);
define('ERR_USER_NOT_FOUND',			603);
define('ERR_USER_UPDATE_FAILED',		604);
define('ERR_USER_INVALID_VERIFYCODE',		605);
define('ERR_USER_FB_SIGNUP_REQUIRE',		606);
define('ERR_USER_LIKE_FEED_FAILED',		611);
define('ERR_USER_FOLLOW_FAILED',		612);

// Service Error
define('ERR_SERVICE_NOT_FOUND',				700);
define('ERR_SERVICE_NOT_YET_COMPETED',		701);
define('ERR_SERVICE_NOT_YOURS',				702);
define('ERR_SERVICE_LEAVE_FEEDBACK',		703);
define('ERR_SERVICE_ALREADY_PROCESSED',		704);
define('ERR_SERVICE_PROCESS_FAILED',		705);
define('ERR_SERVICE_CANT_COMPLETE',			706);
define('ERR_SERVICE_REVERSE_FAILED',		707);
define('ERR_SERVICE_READ_NOTIF_FAILED',		708);




// Test Error
//define('ERR_TEST',		702);
//define('ERR_TEST',			703);










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
define('FOPEN_READ',							'rb');
define('FOPEN_READ_WRITE',						'r+b');
define('FOPEN_WRITE_CREATE_DESTRUCTIVE',		'wb'); // truncates existing file data, use with care
define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE',	'w+b'); // truncates existing file data, use with care
define('FOPEN_WRITE_CREATE',					'ab');
define('FOPEN_READ_WRITE_CREATE',				'a+b');
define('FOPEN_WRITE_CREATE_STRICT',				'xb');
define('FOPEN_READ_WRITE_CREATE_STRICT',		'x+b');


/* End of file constants.php */
/* Location: ./application/config/constants.php */