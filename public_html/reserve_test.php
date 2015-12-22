<?php
require_once("../conf.php");
require_once(_TWITTER_CLASS_PATH."Cron_Retweet_Reserve.php");

//リツイート予約実行-------------------------------------------------------
$mes = date("Y-m-d H:i:s")." TEST: リツイート予約処理 開始\n";
error_log($mes, 3, _TWITTER_LOG_PATH.'log_'.date("Y_m_d").".log");

$Article_MakeArticle = new Cron_Retweet_Reserve();
$Article_MakeArticle->Exec();

$mes = date("Y-m-d H:i:s")." TEST: リツイート予約処理 終了\n";
error_log($mes, 3, _TWITTER_LOG_PATH.'log_'.date("Y_m_d").".log");

?>
<html lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>リツイート予約テスト</title>
</head>
<body>

</body>
</html>
