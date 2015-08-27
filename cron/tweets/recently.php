#!/usr/bin/php5.4
<?php
set_time_limit(0);

require_once("../../conf.php");
require_once(_TWITTER_CLASS_PATH."Cron_Tweets_Popularity.php");

$mes = date("Y-m-d H:i:s")." CRON: ".SCREEN_NAME." ツイート処理(recentのみ)開始\n";
error_log($mes, 3, _TWITTER_LOG_PATH.'log_'.date("Y_m_d").".log");

$Popularity = new Cron_Tweets_Popularity();
$Popularity->setResultType('recent')->Exec();

$mes = date("Y-m-d H:i:s")." CRON: ".SCREEN_NAME." ツイート処理(recentのみ)終了\n";
error_log($mes, 3, _TWITTER_LOG_PATH.'log_'.date("Y_m_d").".log");
