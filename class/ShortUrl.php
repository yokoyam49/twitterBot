<?php
require_once(_TWITTER_CLASS_PATH."DB_Base.php");

class ShortUrl
{
    const SHORT_STR_LENGTH = 6;
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

    //ショートURL生成
    public function make_short_url($redirect_url)
    {
        for($i = 0; $i < 100; $i++){
            $short_str = $this->makeRandStr(self::SHORT_STR_LENGTH);
            $sql = "SELECT redirect_url FROM aff_short_url WHERE short_str = ?";
            $res = $this->DBobj->query($sql, array($short_str));
            if(!$res){
                break;
            }
        }
        $short_url_fields = array(
                        'short_str' => $short_str,
                        'redirect_url' => $redirect_url
                    );
        $sql = "INSERT INTO aff_short_url
                (".implode(", ", array_keys($short_url_fields)).", create_date)
                VALUES (".implode(", ", array_fill(0, count($short_url_fields), '?')).", now())";
        $in_count = $this->DBobj->execute($sql, $short_url_fields);

        return 'http://'._SHORT_URL_DOMAIN.'/'.$short_str.'/';
    }

    private function makeRandStr($length) {
        $str = array_merge(range('a', 'z'), range('0', '9'), range('A', 'Z'));
        $r_str = null;
        for ($i = 0; $i < $length; $i++) {
            $r_str .= $str[rand(0, count($str))];
        }
        return $r_str;
    }

}




