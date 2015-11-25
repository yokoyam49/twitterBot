<?php
require_once("../conf.php");
require_once(_TWITTER_CLASS_PATH."Cron_Article_MakeArticle.php");
require_once(_TWITTER_CLASS_PATH."DB_Base.php");

// if(!isset($_REQUEST['id'])){
// 	exit();
// }
// if(is_numeric($_REQUEST['id'])){
//     $RSS_AccountID = $_REQUEST['id'];
// }else{
//     echo 'idが不正';
//     exit();
// }

$mes = date("Y-m-d H:i:s")." TEST: 記事生成処理 開始\n";
error_log($mes, 3, _RSS_LOG_PATH.'rss_log_'.date("Y_m_d").".log");

$Article_MakeArticle = new Cron_Article_MakeArticle();
$Article_MakeArticle->Exec();

$mes = date("Y-m-d H:i:s")." TEST: 記事生成処理 終了\n";
error_log($mes, 3, _RSS_LOG_PATH.'rss_log_'.date("Y_m_d").".log");

?>
<html lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>RSSテスト</title>
</head>
<body>

<?php
echo '完了';
?>
</body>
</html>
