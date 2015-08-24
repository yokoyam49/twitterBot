<?php
require_once(_TWITTER_CLASS_PATH."Cron_Follower_Expand_Logic.php");
require_once(_TWITTER_CLASS_PATH."MS_Account.php");
require_once(_TWITTER_CLASS_PATH."DB_Base.php");

class Cron_Follower_Expand
{

    //アカウント情報
    private $Accounts;

    private $LogicObj;

    public function __construct()
    {
        //フォロー動作有効になっているアカウント取得
        $AccountObj = new MS_Account();
        $this->Accounts = $AccountObj->getFollowValidAccount();

        $this->LogicObj = new Cron_Follower_Expand_Logic();

        $this->logFile = 'log_'.date("Y_m_d").".log";
    }

    private function Exec()
    {
        foreach($this->Accounts as $Account){
            //ロジックにアカウントセット
            $this->LogicObj->setAccountId($Account->id);




        }
    }




}



