<?php
date_default_timezone_set('Asia/Tokyo');
ini_set( 'display_errors', 1 );

//�V�X�e�����[�g
//define('_SYSTEM_ROOT', '/var/www/html/');
define('_SYSTEM_ROOT', '/home/ainyan/ainyan.minibird.jp/');
//twitteroauth
define('_TWITTER_OAUTH_PATH', _SYSTEM_ROOT.'twitteroauth/');
//twitterapi
define('_TWITTER_API_PATH', _SYSTEM_ROOT.'twitterapi/');
//rssfeed
define('_RSS_FEED_PATH', _SYSTEM_ROOT.'rssphp/src/');
//class
define('_TWITTER_CLASS_PATH', _SYSTEM_ROOT.'class/');
//smarty
define('_TWITTER_SMARTY_PATH', _SYSTEM_ROOT.'smarty/');
//smarty�e���v���[�g
define('_TWITTER_TEMPLATE_PATH', _SYSTEM_ROOT.'templates/');
//�h�L�������g�p�X
define('_DOCUMENT_PATH', _SYSTEM_ROOT.'public_html/');
//log
define('_TWITTER_LOG_PATH', _SYSTEM_ROOT.'log/');

//require
//require_once(_TWITTER_OAUTH_PATH."src/TwitterOAuth.php");
// OAuth���C�u�����̓ǂݍ���
require_once _TWITTER_OAUTH_PATH.'autoload.php';
require_once _TWITTER_API_PATH.'api_setting.php';
require_once _SYSTEM_ROOT.'db_conf.php';
