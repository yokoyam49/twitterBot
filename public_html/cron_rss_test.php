<?php

//set_time_limit(0);

require_once("../conf.php");
require_once(_TWITTER_CLASS_PATH."Cron_Rss_GetSource.php");

$mes = date("Y-m-d H:i:s")." CRON: RSS取得処理 開始\n";
error_log($mes, 3, _RSS_LOG_PATH.'rss_log_'.date("Y_m_d").".log");

$Rss_GetSource = new Cron_Rss_GetSource();
$Rss_GetSource->Exec();

$mes = date("Y-m-d H:i:s")." CRON: RSS取得処理 終了\n";
error_log($mes, 3, _RSS_LOG_PATH.'rss_log_'.date("Y_m_d").".log");



