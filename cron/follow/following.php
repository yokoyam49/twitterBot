#!/usr/bin/php5.4
<?php
set_time_limit(300);

require_once("../../conf.php");
require_once(_TWITTER_CLASS_PATH."Cron_Follower_Expand.php");


$mes = date("Y-m-d H:i:s")." CRON: フォロー日時処理開始\n";
error_log($mes, 3, _TWITTER_LOG_PATH.'log_'.date("Y_m_d").".log");

$FollowerExpand = new Cron_Follower_Expand();
$FollowerExpand->Exec();

$mes = date("Y-m-d H:i:s")." CRON: フォロー日時処理終了\n";
error_log($mes, 3, _TWITTER_LOG_PATH.'log_'.date("Y_m_d").".log");
