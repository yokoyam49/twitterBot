<?php
require_once(_TWITTER_CLASS_PATH."Cron_Tweets_Popularity_Logic.php");
require_once(_TWITTER_CLASS_PATH."MS_Account.php");
require_once(_TWITTER_CLASS_PATH."DB_Base.php");

class Cron_Tweets_Popularity
{
    //アカウント情報
    private $Accounts;

    private $LogicObj;

    public function __construct()
    {
        //リツイート動作有効になっているアカウント取得
        $AccountObj = new MS_Account();
        $this->Accounts = $AccountObj->getRetweetValidAccount();

        $this->LogicObj = new Cron_Tweets_Popularity_Logic();

        $this->logFile = 'log_'.date("Y_m_d").".log";
    }

    public function Exec()
    {
        foreach($this->Accounts as $Account){
            $mes = date("Y-m-d H:i:s")." CRON: ".$Account->account_name." ツイート処理開始\n";
            error_log($mes, 3, _TWITTER_LOG_PATH.'log_'.date("Y_m_d").".log");

            //ロジックにアカウントセット
            $this->LogicObj->setAccountId($Account->id);

            try{
                //検索 人気順並び替え
                $this->LogicObj->SearchTweets();
                //重複していないもの取得
                $tweet = $this->LogicObj->getAnDuplicateTweetID();
                if(is_null($tweet)){
                    $mes = "TwieetID: 全て重複"."\n";
                    throw new Exception($mes);
                }
                //リツイート
                $this->LogicObj->Retweets($tweet);

            }catch(Exception $e){
                //ログ出力
                error_log($e->getMessage(), 3, _TWITTER_LOG_PATH.$this->logFile);
            }

            $mes = date("Y-m-d H:i:s")." CRON: ".$Account->account_name." ツイート処理終了\n";
            error_log($mes, 3, _TWITTER_LOG_PATH.'log_'.date("Y_m_d").".log");
        }
    }

}


