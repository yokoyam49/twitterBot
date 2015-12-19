<?php
date_default_timezone_set('Asia/Tokyo');
ini_set( 'display_errors', 1 );

//システムルート
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
//rakuten_sdk
define('_RAKUTEN_SDK_PATH', _SYSTEM_ROOT.'rakutensdk/');
//smarty
define('_TWITTER_SMARTY_PATH', _SYSTEM_ROOT.'smarty/');
//smartyテンプレート
define('_TWITTER_TEMPLATE_PATH', _SYSTEM_ROOT.'templates/');
//ドキュメントパス
define('_DOCUMENT_PATH', _SYSTEM_ROOT.'public_html/');
//ショートURL用ドメイン設定
define('_SHORT_URL_DOMAIN', 'blog.graph.jp');
//log
define('_TWITTER_LOG_PATH', _SYSTEM_ROOT.'log/');

//画像システムパス
define('_IMAGE_PATH', '/home/ainyan/blog.graph.jp/public_html/img/');
//画像URL
define('_IMAGE_URL', 'http://blog.graph.jp/img/');
//RSSlog
define('_RSS_LOG_PATH', _SYSTEM_ROOT.'log/');

//require
//require_once(_TWITTER_OAUTH_PATH."src/TwitterOAuth.php");
// OAuthライブラリの読み込み
require_once _TWITTER_OAUTH_PATH.'autoload.php';
require_once _TWITTER_API_PATH.'api_setting.php';
require_once _SYSTEM_ROOT.'db_conf.php';
