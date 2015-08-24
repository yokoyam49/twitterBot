<?php

require_once(_TWITTER_CLASS_PATH."DB_Base.php");

class MS_Account
{
    private $DBobj;

    public function __construct()
    {
        $this->DBobj = new DB_Base();
    }

    //全件取得
    public function getAllAccount()
    {
        return $this->DBobj->find('ms_account');
    }

    //有効なものだけ取得
    public function getValidAccount()
    {
        $sql = "SELECT * FROM ms_account WHERE use_flg = 1";
        return $this->DBobj->query($sql);
    }

    //リツイート動作が有効なものだけ取得
    public function getRetweetValidAccount()
    {
        $sql = "SELECT * FROM ms_account WHERE use_flg = 1 AND retweet_on_flg = 1";
        return $this->DBobj->query($sql);
    }

    //フォロー動作が有効なものだけ取得
    public function getFollowValidAccount()
    {
        $sql = "SELECT * FROM ms_account WHERE use_flg = 1 AND follow_on_flg = 1";
        return $this->DBobj->query($sql);
    }

    //指定IDアカウント取得
    public function getAccountById($id)
    {
        $res = $this->DBobj->find('ms_account', $id);
        return $res[0];
    }

}

