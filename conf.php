<?php

//�V�X�e�����[�g
define('_SYSTEM_ROOT', '/var/www/html/');
//twitteroauth
define('_TWITTER_OAUTH_PATH', _SYSTEM_ROOT.'twitteroauth/');
//twitterapi
define('_TWITTER_API_PATH', _SYSTEM_ROOT.'twitterapi/');


//require
//require_once(_TWITTER_OAUTH_PATH."src/TwitterOAuth.php");
// OAuth���C�u�����̓ǂݍ���
require_once _TWITTER_OAUTH_PATH.'autoload.php';
require_once _TWITTER_API_PATH.'api_setting.php';
