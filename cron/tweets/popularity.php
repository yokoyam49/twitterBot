#!/usr/bin/php5.4
<?php
set_time_limit(300);

require_once("../../conf.php");
require_once(_TWITTER_CLASS_PATH."Cron_Tweets_Popularity.php");

$mes = date("Y-m-d H:i:s")." CRON: ".SCREEN_NAME." ツイート処理開始\n";
error_log($mes, 3, _TWITTER_LOG_PATH.'log_'.date("Y_m_d").".log");

$Popularity = new Cron_Tweets_Popularity();
//$Popularity->setInit('#DQ OR #DQ1 OR #DQ2 OR #DQ3 OR #DQ4 OR #DQ5 OR #DQ6 OR #DQ7 OR #DQ8 OR #DQ9 OR #DQ10 OR #DQ11 OR #ドラクエ -rt', 1000)->Exec();
$Popularity->setInit('艦これ OR #艦これ OR #kancolle -rt', 1000)->Exec();

$mes = date("Y-m-d H:i:s")." CRON: ".SCREEN_NAME." ツイート処理終了\n";
error_log($mes, 3, _TWITTER_LOG_PATH.'log_'.date("Y_m_d").".log");
