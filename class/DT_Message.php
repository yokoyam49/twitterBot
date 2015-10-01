<?php

require_once(_TWITTER_CLASS_PATH."DB_Base.php");

class DT_Message
{

    private $DBobj;

    public function __construct()
    {
        $this->DBobj = new DB_Base();
    }

    public function addMessage($mes, $account_id = 0, $type = '', $process = '')
    {
        $sql = "INSERT INTO dt_message ( account_id, type, process, message1, check_flg, create_date) VALUES ( ?, ?, ?, ?, 0, now())";
        return $this->DBobj->execute($sql, array($account_id, $type, $process, $mes));
    }

}

