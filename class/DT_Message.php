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

    //メッセージ取得 checked=trueで確認済みも取得
    public function getMessages($checked = false, $Account_ID = null, $limit = 50, $offset = 0)
    {
        $whs = array();
        $parm = array();
        $cols = "SELECT a.account_name, m.type, m.process, m.message1, m.check_flg, m.create_date FROM dt_message AS m LEFT JOIN ms_account AS a ON m.account_id = a.id";
        if(!$checked){
            $whs[] = "m.check_flg = 0";
        }
        if(!is_null($Account_ID)){
            $whs[] = "m.account_id = ?";
            $parm[] = $Account_ID;
        }
        if(count($whs)){
            $where = ' WHERE '.implode(" AND ", $whs);
        }

        $orderby = " ORDER BY m.create_date DESC";
        $limit_str = " LIMIT ".(string)$limit." OFFSET ".(string)$offset;

        $sql = $cols.$where.$orderby.$limit_str;
        return $this->DBobj->query($sql, $parm);
    }

    //メッセージ件数取得
    public function getMessageCount($checked = false, $Account_ID = null)
    {
        $parm = array();
        $cols = "SELECT id FROM dt_message";
        if(!$checked){
            $whs[] = "check_flg = 0";
        }
        if(!is_null($Account_ID)){
            $whs[] = "account_id = ?";
            $parm[] = $Account_ID;
        }
        if(count($whs)){
            $where = ' WHERE '.implode(" AND ", $whs);
        }

        $sql = $cols.$where;
        $rec = $this->DBobj->query($sql, $parm);
        return count($rec);
    }

}

