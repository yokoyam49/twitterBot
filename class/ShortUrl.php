<?php
require_once(_TWITTER_CLASS_PATH."DB_Base.php");

class ShortUrl
{
    private $Short_Str;

    private $DBobj;

    public function __construct()
    {
        $this->DBobj = new DB_Base();

    }

    public function setParm($short_str)
    {
        $this->Short_Str = $short_str;
        return $this;
    }

    public function redirect()
    {
        $sql = "SELECT redirect_url FROM aff_short_url WHERE short_str = ?";
        $res = $this->DBobj->query($sql, array($this->Short_Str));
        if(!$res){
            $url = 'http://www.google.co.jp/';
        }else{
            $url = $res[0]->redirect_url;
        }
        header("Location: {$url}");
    }


}




