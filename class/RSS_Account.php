<?php

require_once(_TWITTER_CLASS_PATH."DB_Base.php");

class RSS_Account
{
    private $DBobj;

    public function __construct()
    {
        $this->DBobj = new DB_Base();
    }

    //全件取得
    public function getAllAccount()
    {
        return $this->DBobj->find('rss_feed_account');
    }

    //有効なものだけ取得
    public function getValidAccount()
    {
        $sql = "SELECT * FROM rss_feed_account WHERE use_flg = 1";
        return $this->DBobj->query($sql);
    }

    //指定IDアカウント取得
    public function getAccountById($id)
    {
        $res = $this->DBobj->find('rss_feed_account', $id);
        return $res[0];
    }

}

