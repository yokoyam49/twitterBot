<?php
require_once(_TWITTER_CLASS_PATH."Cron_Rss_GetSource_Logic.php");
require_once(_TWITTER_CLASS_PATH."RSS_Account.php");
require_once(_TWITTER_CLASS_PATH."DB_Base.php");

class Cron_Rss_GetSource
{

    //アカウント情報
    private $RSS_Accounts;

    private $LogicObj;

    private $logFile;

    public function __construct()
    {
        //動作有効になっているアカウント取得
        $RSS_AccountObj = new RSS_Account();
        $this->RSS_Accounts = $RSS_AccountObj->getValidAccount();

        $this->LogicObj = new Cron_Rss_GetSource_Logic();

        $this->logFile = 'rss_log_'.date("Y_m_d").".log";
    }

    public function Exec()
    {

        foreach($this->RSS_Accounts as $RSS_Account){
            $mes = date("Y-m-d H:i:s")." Account: ".$RSS_Account->name." 処理開始\n";
            error_log($mes, 3, _RSS_LOG_PATH.$this->logFile);

            try{
                $this->LogicObj->setAccountId($RSS_Account->id);

                $analysis_method = 'analysis_'.$RSS_Account->name;
                if(!method_exists($this->LogicObj, $analysis_method)){
                    $msg = "RSS解析メソッドが定義されていません";
                    throw new Exception($msg);
                }

                $this->LogicObj->$analysis_method();


            }catch(Exception $e){
                //ログ出力
                error_log($e->getMessage(), 3, _RSS_LOG_PATH.$this->logFile);


            }

            $mes = date("Y-m-d H:i:s")." Account: ".$RSS_Account->name." 処理終了\n";
            error_log($mes, 3, _RSS_LOG_PATH.$this->logFile);


        }


    }



}




