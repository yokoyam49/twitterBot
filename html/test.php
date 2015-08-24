<?php
require_once("../conf.php");
require_once(_TWITTER_CLASS_PATH."Cron_Tweets_Popularity_Logic.php");
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

$Popularity_logic = new Cron_Tweets_Popularity_Logic();
//リツイート動作有効になっているアカウント取得
$AccountObj = new MS_Account();
$Account = $AccountObj->getAccountById($AccountID);

$mes = date("Y-m-d H:i:s")." test: ".$Account->account_name." 処理開始\n";
error_log($mes, 3, _TWITTER_LOG_PATH.'log_'.date("Y_m_d").".log");

//ロジックにアカウントセット
$Popularity_logic->setAccountId($Account->id);

try{
    //検索 人気順並び替え
    $Popularity_logic->SearchTweets();
    //重複していないID取得
    $tweetId = $Popularity_logic->getAnDuplicateTweetID();
    if(is_null($tweetId)){
        $overlapIDs = implode(",", $overlapID_Arr);
        $mes = "TwieetID:".$overlapIDs." 全て重複"."\n";
        throw new Exception($mes);
    }
    //テストなのでリツイートしない
    //$Popularity_logic->Retweets($tweetId);

}catch(Exception $e){
    //ログ出力
    error_log($e->getMessage(), 3, _TWITTER_LOG_PATH.'log_'.date("Y_m_d").".log");
}

echo '判定ID：'.$tweetId."<br><br>\n";
foreach ($Popularity_logic->getSearch_Res() as $result){
    $id = $result->id;
    $retweet_count = $result->retweet_count;
    $favorite_count = $result->favorite_count;
    $name = $result->user->name;
    $link = $result->user->profile_image_url;
    $content = $result->text;
    $updated = $result->created_at;
    $time = $time = date("Y-m-d H:i:s",strtotime($updated));

    echo "<img src='".$link."''>"." | ".$id." | ".$favorite_count." | ".$retweet_count." | ".$name." | ".$content." | ".$time;
    echo '<br>';
}

$mes = date("Y-m-d H:i:s")." test: ".$Account->account_name." 処理終了\n";
error_log($mes, 3, _TWITTER_LOG_PATH.'log_'.date("Y_m_d").".log");

