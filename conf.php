<?php

//システムルート
define('_SYSTEM_ROOT', '/var/www/html/');
//twitteroauth
define('_TWITTER_OAUTH_PATH', _SYSTEM_ROOT.'twitteroauth/');
//twitterapi
define('_TWITTER_API_PATH', _SYSTEM_ROOT.'twitterapi/');

//TwitterAPI
define('CONSUMER_KEY', 'kWasFaoJzD2X6a5b3Zm8U4K2x');
define('CONSUMER_SECRET', '8w6Q0lKeJ2pJgZukYe9biKO46WIJBeE6UvAk5pweDvgGexGSDW');
define('ACCESS_TOKEN', '3252741534-ZEUvpj0JbtFxo9w7i1wZVwzCEuwQIweWrGImXQt');
define('ACCESS_TOKEN_SECRET', 'OJQFcixC8siZSj3OR7xUHz6zwedT8nZOLe4v9xUE7KrkB');

//require
//require_once(_TWITTER_OAUTH_PATH."src/TwitterOAuth.php");
// OAuthライブラリの読み込み
require_once _TWITTER_OAUTH_PATH.'autoload.php';
