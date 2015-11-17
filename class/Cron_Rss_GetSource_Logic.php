<?php
require_once(_TWITTER_CLASS_PATH."Api_Error.php");
require_once(_TWITTER_CLASS_PATH."RSS_Data_Container.php");
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
    //RSSコンテナ配列
    private $RSS_Cont_Arr = array();
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
        if($this->RSS_AccountInfo->feed_type === 'RSS2'){
            $this->Source = $FeedObj->loadRss($this->RSS_AccountInfo->rssfeed_url);
        }elseif($this->RSS_AccountInfo->feed_type === 'Atom'){
            $this->Source = $FeedObj->loadAtom($this->RSS_AccountInfo->rssfeed_url);
        }elseif($this->RSS_AccountInfo->feed_type === 'RSS1'){
            //$this->Source = simplexml_load_string(file_get_contents($this->RSS_AccountInfo->rssfeed_url));
            $this->Source = simplexml_load_file($this->RSS_AccountInfo->rssfeed_url);
        }
        else{
            $msg = 'RSS_TYPEの設定が不正です';
            throw new Exception($msg);
        }
        if($this->Source === false){
            $msg = 'RSSフィードの取得に失敗しました';
            throw new Exception($msg);
        }
    }

    public function setDB_FeedData()
    {
        foreach($this->RSS_Cont_Arr as $RSS_Container){

        }


    }

    public function test_outputFeed()
    {
        $res = array();
        //echo json_encode($this->Source);
        var_dump($this->RSS_Cont_Arr);
        // foreach($this->Source->item as $feed_data){
        //     //echo json_encode((string)$feed_data->children('http://purl.org/rss/1.0/modules/content/')->encoded)."<br><br>";
        //     echo $feed_data->title;
        //     var_dump((string)$feed_data->children('http://purl.org/rss/1.0/modules/content/')->encoded);
        // }
    }

    public function analysis_oretekigame()
    {
        $this->RSS_Cont_Arr = array();
        foreach($this->Source->item as $feed_data){
            $RSS_cont_obj = new RSS_Data_Container();
            $RSS_cont_obj->rss_account_id = $this->RSS_Account_ID;
            $RSS_cont_obj->date = (string)$feed_data->children('http://purl.org/dc/elements/1.1/')->date;
            $RSS_cont_obj->title = (string)$feed_data->title;
            $RSS_cont_obj->link_url = (string)$feed_data->link;
            $RSS_cont_obj->html_content = (string)$feed_data->children('http://purl.org/rss/1.0/modules/content/')->encoded;
            $RSS_cont_obj->subject = (string)$feed_data->children('http://purl.org/dc/elements/1.1/')->subject;
            $this->RSS_Cont_Arr[] = $RSS_cont_obj;
            unset($RSS_cont_obj);
        }
    }

    public function analysis_4games_topics()
    {
        $this->RSS_Cont_Arr = array();
        foreach($this->Source->item as $feed_data){
            $RSS_cont_obj = new RSS_Data_Container();
            $RSS_cont_obj->rss_account_id = $this->RSS_Account_ID;
            $RSS_cont_obj->date = (string)$feed_data->children('http://purl.org/dc/elements/1.1/')->date;
            $RSS_cont_obj->title = (string)$feed_data->title;
            $RSS_cont_obj->link_url = (string)$feed_data->link;
            $RSS_cont_obj->content = (string)$feed_data->description;
            $this->RSS_Cont_Arr[] = $RSS_cont_obj;
            unset($RSS_cont_obj);
        }
    }



}


