#!/usr/bin/php5.4
<?php
set_time_limit(0);

require_once("../../conf.php");
require_once(_TWITTER_CLASS_PATH."Cron_Rss_GetSource.php");
require_once(_TWITTER_CLASS_PATH."Cron_Article_MakeArticle.php");
require_once(_TWITTER_CLASS_PATH."Cron_Retweet_Reserve.php");

//--------------------------------------------------------------------
$mes = date("Y-m-d H:i:s")." CRON: RSS取得処理 開始\n";
error_log($mes, 3, _RSS_LOG_PATH.'rss_log_'.date("Y_m_d").".log");

$Rss_GetSource = new Cron_Rss_GetSource();
$Rss_GetSource->Exec();

$mes = date("Y-m-d H:i:s")." CRON: RSS取得処理 終了\n";
error_log($mes, 3, _RSS_LOG_PATH.'rss_log_'.date("Y_m_d").".log");

//--------------------------------------------------------------------
$mes = date("Y-m-d H:i:s")." CRON: 記事生成処理 開始\n";
error_log($mes, 3, _RSS_LOG_PATH.'rss_log_'.date("Y_m_d").".log");

$Article_MakeArticle = new Cron_Article_MakeArticle();
$Article_MakeArticle->Exec();

$mes = date("Y-m-d H:i:s")." CRON: 記事生成処理 終了\n";
error_log($mes, 3, _RSS_LOG_PATH.'rss_log_'.date("Y_m_d").".log");

//リツイート予約実行-------------------------------------------------------
// $mes = date("Y-m-d H:i:s")." CRON: リツイート予約処理 開始\n";
// error_log($mes, 3, _TWITTER_LOG_PATH.'log_'.date("Y_m_d").".log");

// $Article_MakeArticle = new Cron_Retweet_Reserve();
// $Article_MakeArticle->Exec();

// $mes = date("Y-m-d H:i:s")." CRON: リツイート予約処理 終了\n";
// error_log($mes, 3, _TWITTER_LOG_PATH.'log_'.date("Y_m_d").".log");
