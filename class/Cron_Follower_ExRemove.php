<?php
require_once(_TWITTER_CLASS_PATH."Cron_Follower_ExRemove_Logic.php");
require_once(_TWITTER_CLASS_PATH."MS_Account.php");
require_once(_TWITTER_CLASS_PATH."DT_Message.php");
require_once(_TWITTER_CLASS_PATH."DB_Base.php");

class Cron_Follower_ExRemove
{

    //アカウント情報
    private $Accounts;

    private $LogicObj;

    public function __construct()
    {
        //フォロー動作有効になっているアカウント取得
        $AccountObj = new MS_Account();
        $this->Accounts = $AccountObj->getExtraRemoveValidAccount();

        $this->LogicObj = new Cron_Follower_ExRemove_Logic();

        $this->logFile = 'log_'.date("Y_m_d").".log";
    }

    public function Exec()
    {
        foreach($this->Accounts as $Account){

            $mes = date("Y-m-d H:i:s")." Account: ".$Account->account_name." 特殊リムーブ処理開始\n";
            error_log($mes, 3, _TWITTER_LOG_PATH.'log_'.date("Y_m_d").".log");

            //ロジックにアカウントセット
            $this->LogicObj->setAccountId($Account->id);

            try{
                //対象を取得しDBへセット
                $this->LogicObj->getTargetUserAndSetDB();
                //リムーブ処理
                $this->LogicObj->execRemove();
                //クリックカウント用固体識別情報テーブル 古いデータ削除処理
                $this->LogicObj->deleteClickcountHosts();

            }catch(Exception $e){
                //ログ出力
                error_log($e->getMessage(), 3, _TWITTER_LOG_PATH.$this->logFile);
                //メッセージテーブルに書き出し
                $MTobj = new DT_Message();
                $MTobj->addMessage($e->getMessage(), $Account->id, 'error', $e->getFile().':'.$e->getLine());
            }

            $mes = date("Y-m-d H:i:s")." Account: ".$Account->account_name." 特殊リムーブ処理終了\n";
            error_log($mes, 3, _TWITTER_LOG_PATH.'log_'.date("Y_m_d").".log");

        }
    }

}



