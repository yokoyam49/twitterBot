<?php

//システムルート
define('_SYSTEM_ROOT', '/var/www/html/');
//twitteroauth
define('_TWITTER_OAUTH_PATH', _SYSTEM_ROOT.'twitteroauth/');
//twitterapi
define('_TWITTER_API_PATH', _SYSTEM_ROOT.'twitterapi/');
//class
define('_TWITTER_CLASS_PATH', _SYSTEM_ROOT.'class/');
//log
define('_TWITTER_LOG_PATH', _SYSTEM_ROOT.'log/');

//require
//require_once(_TWITTER_OAUTH_PATH."src/TwitterOAuth.php");
// OAuthライブラリの読み込み
require_once _TWITTER_OAUTH_PATH.'autoload.php';
require_once _TWITTER_API_PATH.'api_setting.php';
