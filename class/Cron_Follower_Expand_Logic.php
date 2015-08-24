<?php
require_once(_TWITTER_API_PATH."search/search_tweets.php");
require_once(_TWITTER_CLASS_PATH."Api_Error.php");

use Abraham\TwitterOAuth\TwitterOAuth;

class Cron_Follower_Expand_Logic
{

    // 入力情報
    //処理を実行するアカウントID
    private $Account_ID;

    private $AccountInfo = null;

    //OAuthオブジェクト
    private $twObj = null;

    private $logFile;

    public function __construct()
    {
        $this->logFile = 'log_'.date("Y_m_d").".log";
    }

    //アカウントset OAuthオブジェクト再接続
    public function setAccountId($id)
    {
        $this->Account_ID = $id;

        //前の情報破棄
        if(!is_null($this->twObj)){
            unset($this->twObj);
        }
        if(!is_null($this->AccountInfo)){
            unset($this->AccountInfo);
        }

        $MS_AccountObj = new MS_Account();
        $this->AccountInfo = $MS_AccountObj->getAccountById($id);
        if(!is_null($this->twObj)){
            unset($this->twObj);
        }
        $this->twObj = new TwitterOAuth(
                                $this->AccountInfo->consumer_key,
                                $this->AccountInfo->consumer_secret,
                                $this->AccountInfo->access_token,
                                $this->AccountInfo->access_token_secret
                                );
    }



    public function getMyFollwerList()
    {

    }




}

