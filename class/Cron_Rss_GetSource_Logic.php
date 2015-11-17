<?php
require_once(_TWITTER_CLASS_PATH."Api_Error.php");
require_once(_RSS_FEED_PATH."Feed.php");

class Cron_Rss_GetSource_Logic
{

    // 入力情報
    //処理を実行するアカウントID
    private $RSS_Account_ID;

    // DBより取得するパラメーター
    //アカウント情報
    private $RSS_AccountInfo = null;


    //RSS
    private $Source = null;
    //DBオブジェクト
    private $DBobj;
    //ログ
    private $logFile;

    public function __construct()
    {
        $this->DBobj = new DB_Base();
        $this->logFile = 'log_'.date("Y_m_d").".log";
    }

    //アカウントset
    public function setAccountId($id)
    {
        $this->RSS_Account_ID = $id;
        //RSSソースリセット
        $this->Source = null;

        $FeedObj = new Feed();

        $RSS_AccountObj = new RSS_Account();
        $this->RSS_AccountInfo = $RSS_AccountObj->getAccountById($id);

        //RSSフィード取得
        if($this->RSS_AccountInfo->feed_type === 'RSS'){
            $this->Source = $FeedObj->loadRss($this->RSS_AccountInfo->rssfeed_url);
        }elseif($this->RSS_AccountInfo->feed_type === 'Atom'){
            $this->Source = $FeedObj->loadAtom($this->RSS_AccountInfo->rssfeed_url);
        }
        else{
            //$msg = 'RSS_TYPEの設定が不正です';
            throw new Exception($msg);
        }
    }

    public function setDB_FeedData()
    {
        foreach($this->Source->item as $feed_data){

        }


    }

    public function test_outputFeed()
    {
        $res = array();
        foreach($this->Source->item as $feed_data){
            echo json_encode($feed_data)."<br><br>";
        }
    }

    private function analysis_hatenait()
    {
        foreach($this->Source->item as $feed_data){

        }
    }





}


