<?php

require_once(_TWITTER_CLASS_PATH."DB_Base.php");
require_once(_TWITTER_CLASS_PATH."MS_Account.php");
require_once(_TWITTER_API_PATH."statuses/statuses_retweet.php");
require_once(_TWITTER_CLASS_PATH."DT_Message.php");
require_once(_TWITTER_CLASS_PATH."Api_Error.php");

use Abraham\TwitterOAuth\TwitterOAuth;

class Cron_Retweet_Reserve
{
    private $DBobj;
    private $AccountObj;
    private $logFile;

    private $Reserve_Info;

    public function __construct()
    {
        $this->DBobj = new DB_Base();
        $this->AccountObj = new MS_Account();
        $this->logFile = 'log_'.date("Y_m_d").".log";
    }

    public function Exec()
    {
        //リツイート予約取得
        $this->getReserve();

        foreach($this->Reserve_Info as $reserve){
            $mes = 'リツイート予約 ReserveID: '.$reserve->id.' RtwAccountID: '.$reserve->rtw_account_id."\n";
            error_log($mes, 3, _TWITTER_LOG_PATH.$this->logFile);
            try{
                //リツイート実行
                $this->RetweetReserve($reserve);

            }catch(Exception $e){
                //ログ出力
                error_log($e->getMessage(), 3, _TWITTER_LOG_PATH.$this->logFile);
                //メッセージテーブルに書き出し
                $MTobj = new DT_Message();
                $MTobj->addMessage($e->getMessage(), $reserve->id, 'error', $e->getFile().':'.$e->getLine());
            }
        }
    }

    private function getReserve()
    {
        $sql = "SELECT *
                FROM aff_retweet_reserve
                WHERE del_flg = 0
                    AND retweeted_flg = 0
                    AND retweet_datetime <= now()";
        $res = $this->DBobj->query($sql);
        $this->Reserve_Info = $res;
    }

    private function RetweetReserve($reserve)
    {
        if(is_null($reserve->tweet_id) or !strlen($reserve->tweet_id)){
            $mes = 'ツイートIDがセットされていませんでした ReserveID: '.$reserve->id."\n";
            throw new Exception($mes);
        }
        //リツイート先アカウント情報取得
        $account_info = $this->getRetweetAccount($reserve->rtw_account_id);
        if(!$account_info){
            $mes = 'リツイート先アカウント情報取得が取得できませんでした AccountID: '.$reserve->rtw_account_id."\n";
            throw new Exception($mes);
        }

        $twObj = new TwitterOAuth(
                                $account_info->consumer_key,
                                $account_info->consumer_secret,
                                $account_info->access_token,
                                $account_info->access_token_secret
                                );
        $retweetObj = new statuses_retweet($twObj);
        $apires = $retweetObj->setRetweetId($reserve->tweet_id)->Request();
        $apiErrorObj = new Api_Error($apires);
        if($apiErrorObj->error){
            $error_msg = $apiErrorObj->errorMes_Str;
            $mes = 'リツイート失敗: '.$error_msg."\n";
            throw new Exception($mes);
        }
        //リツイート済みフラグセット
        $this->setRetweetResult($reserve);

        $mes = 'リツイート成功 RetweetID: '.$apires->id_str."\n";
        error_log($mes, 3, _TWITTER_LOG_PATH.$this->logFile);
    }

    private function getRetweetAccount($account_id)
    {
        return $this->AccountObj->getAccountById($account_id);
    }

    private function setRetweetResult($reserve)
    {
        $sql = "UPDATE aff_retweet_reserve SET retweeted_flg = 1 WHERE id = ?";
        $res = $this->DBobj->execute($sql, array($reserve->id));
    }

}
