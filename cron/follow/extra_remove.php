#!/usr/bin/php5.4
<?php
set_time_limit(0);

require_once("../../conf.php");
require_once(_TWITTER_CLASS_PATH."Cron_Follower_ExRemove.php");


$mes = date("Y-m-d H:i:s")." CRON: 特殊リムーブ日時処理開始\n";
error_log($mes, 3, _TWITTER_LOG_PATH.'log_'.date("Y_m_d").".log");

$FollowerExRemove = new Cron_Follower_ExRemove();
$FollowerExRemove->Exec();

$mes = date("Y-m-d H:i:s")." CRON: 特殊リムーブ日時処理終了\n";
error_log($mes, 3, _TWITTER_LOG_PATH.'log_'.date("Y_m_d").".log");
