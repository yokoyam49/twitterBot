#!/usr/bin/php5.4
<?php
set_time_limit(0);

require_once("../../conf.php");
require_once(_TWITTER_CLASS_PATH."Cron_Article_MakeArticle.php");

$mes = date("Y-m-d H:i:s")." CRON: 記事生成処理 開始\n";
error_log($mes, 3, _RSS_LOG_PATH.'article_log_'.date("Y_m_d").".log");

$Article_MakeArticle = new Cron_Article_MakeArticle();
$Article_MakeArticle->Exec();

$mes = date("Y-m-d H:i:s")." CRON: 記事生成処理 終了\n";
error_log($mes, 3, _RSS_LOG_PATH.'article_log_'.date("Y_m_d").".log");