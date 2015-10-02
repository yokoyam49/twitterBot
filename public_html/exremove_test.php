<?php
require_once("../conf.php");
require_once(_TWITTER_CLASS_PATH."Cron_Follower_ExRemove_Logic.php");
require_once(_TWITTER_CLASS_PATH."MS_Account.php");
require_once(_TWITTER_CLASS_PATH."DB_Base.php");

if(!isset($_REQUEST['id'])){
	exit();
}
if(is_numeric($_REQUEST['id'])){
    $AccountID = $_REQUEST['id'];
}else{
    echo 'idが不正';
    exit();
}

$ExRemove_logic = new Cron_Follower_ExRemove_Logic();
//リツイート動作有効になっているアカウント取得
$AccountObj = new MS_Account();
$Account = $AccountObj->getAccountById($AccountID);

$mes = date("Y-m-d H:i:s")." test_exremove: ".$Account->account_name." 処理開始\n";
error_log($mes, 3, _TWITTER_LOG_PATH.'log_'.date("Y_m_d").".log");

//ロジックにアカウントセット
$ExRemove_logic->setAccountId($Account->id);

try{
    //対象を取得しDBへセット
    $ExRemove_logic->getTargetUserAndSetDB();
    //リムーブ処理
    $ExRemove_logic->execRemove();

}catch(Exception $e){
    //ログ出力
    error_log($e->getMessage(), 3, _TWITTER_LOG_PATH.'log_'.date("Y_m_d").".log");
}

$mes = date("Y-m-d H:i:s")." test_exremove: ".$Account->account_name." 処理終了\n";
error_log($mes, 3, _TWITTER_LOG_PATH.'log_'.date("Y_m_d").".log");

