<?php
require_once("../conf.php");
require_once(_TWITTER_CLASS_PATH."Cron_Rss_GetSource_Logic.php");
require_once(_TWITTER_CLASS_PATH."RSS_Data_Container.php");
require_once(_TWITTER_CLASS_PATH."RSS_Account.php");
require_once(_TWITTER_CLASS_PATH."DB_Base.php");

if(!isset($_REQUEST['id'])){
	exit();
}
if(is_numeric($_REQUEST['id'])){
    $RSS_AccountID = $_REQUEST['id'];
}else{
    echo 'idが不正';
    exit();
}

$ligicObj = new Cron_Rss_GetSource_Logic();

$ligicObj->setAccountId($RSS_AccountID);
?>
<html lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>RSSテスト</title>
</head>
<body>

<?php
$ligicObj->analysis_oretekigame();
$ligicObj->test_outputFeed();
?>
</body>
</html>
