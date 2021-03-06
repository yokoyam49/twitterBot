<?php
require_once(_TWITTER_CLASS_PATH."DB_Base.php");

class Site
{
    private $Site_Id;
    private $Site_Info;
    private $Site_Attribute = array();

    private $DBobj;

    public function __construct($site_id)
    {
        //サイトIDセット
        $this->Site_Id = $site_id;

        $this->DBobj = new DB_Base();
        //サイト情報取得
        $this->setMySiteInfo();
        //自サイトの属性情報取得
        $this->setMySiteAttribute();
    }

    public function getMySiteInfo()
    {
        return $this->Site_Info;
    }

    public function getMySiteAttribute()
    {
        return $this->Site_Attribute;
    }

    private function setMySiteInfo()
    {
        $sql = "SELECT *
                FROM rss_site_account
                WHERE id = ?";
        $res = $this->DBobj->query($sql, array($this->Site_Id));
        $this->Site_Info = $res[0];
    }

    //自サイトの属性情報取得(array)
    private function setMySiteAttribute()
    {
        $sql = "SELECT ai.*
                FROM rss_site_attribute AS sa
                LEFT JOIN rss_attribute_info AS ai ON sa.attribute_id = ai.id
                WHERE sa.del_flg = 0
                    AND sa.site_id = ?";
        $this->Site_Attribute = $this->DBobj->query($sql, array($this->Site_Id));
    }

    //記事100件取得 割合だまし
    //$ratio = 0 <= 100
    //{$ratio}%の確率で本物リンク
    //戻り値 $articles[0..n]->feed：記事情報
    //                    ->link：フェイク、当たり含めたURL
    public function getArticle_FakeLink_Ratio($ratio)
    {
        $articles = array();
        $sql = "SELECT sa.attribute_id, fd.*
                FROM rss_site_article AS sa
                LEFT JOIN rss_feed_date AS fd ON sa.feed_id = fd.id
                WHERE sa.del_flg = 0
                    AND sa.site_id = ?
                ORDER BY fd.date DESC LIMIT 100";
        $feeds = $this->DBobj->query($sql, array($this->Site_Id));
        foreach($feeds as $feed){
            $article = new stdClass();
            $article->feed = $feed;
            if($ratio <= rand(0,100)){
                $fake_urls = $this->getFakeLinkUrl($feed->attribute_id);
                if(count($fake_urls)){
                    $article->link = $fake_urls[array_rand($fake_urls)]->site_url;
                }else{
                    $article->link = $feed->link_url;
                }
            }else{
                $article->link = $feed->link_url;
            }
            $articles[] = $article;
        }
        return $articles;
    }

    //記事100件取得
    //だましとあたりはサイト側で管理
    //戻り値 $articles[0..n]->feed：記事情報
    //                    ->link：記事URL
    //                    ->fake_link:はずれＵＲＬ
    public function getArticle()
    {
        $articles = array();
        $sql = "SELECT sa.attribute_id, fd.*
                FROM rss_site_article AS sa
                LEFT JOIN rss_feed_date AS fd ON sa.feed_id = fd.id
                WHERE sa.del_flg = 0
                    AND sa.site_id = ?
                ORDER BY fd.date DESC LIMIT 100";
        $feeds = $this->DBobj->query($sql, array($this->Site_Id));
        foreach($feeds as $feed){
            $article = new stdClass();
            $article->feed = $feed;
            $article->link = $feed->link_url;

            $fake_urls = $this->getFakeLinkUrl($feed->attribute_id);
            if(count($fake_urls)){
                $article->fake_link = $fake_urls[array_rand($fake_urls)]->site_url;
            }else{
                $article->fake_link = null;
            }
            $articles[] = $article;
        }
        return $articles;
    }

    //日付指定で記事を取得
    //YYYY-mm-dd
    public function getArticle_InDay($date, $fakeUrl_flg=true)
    {
        $articles = array();
        $start = $date.' 00:00:00';
        $end = $date.' 23:59:59';
        $sql = "SELECT sa.attribute_id, fa.*, fd.*
                FROM rss_site_article AS sa
                LEFT JOIN rss_feed_date AS fd ON sa.feed_id = fd.id
                LEFT JOIN rss_feed_account AS fa ON fd.rss_account_id = fa.id
                WHERE sa.del_flg = 0
                    AND sa.site_id = ?
                    AND fd.date BETWEEN ? AND ?
                ORDER BY fd.date DESC";
        $feeds = $this->DBobj->query($sql, array($this->Site_Id, $start, $end));
        if(!$feeds){
            return $articles;
        }
        foreach($feeds as $feed){
            $article = new stdClass();
            $article->feed = $feed;
            $article->link = $feed->link_url;

            if($fakeUrl_flg){
                $fake_urls = $this->getFakeLinkUrl($feed->attribute_id);
                if($fake_urls and count($fake_urls)){
                    $article->fake_link = $fake_urls[array_rand($fake_urls)]->site_url;
                }else{
                    $article->fake_link = null;
                }
            }else{
                $article->fake_link = null;
            }

            $articles[] = $article;
        }
        return $articles;
    }

    //件数縛り取得 指定時間から指定件数取得
    public function getArticle_InCount($limit_count = 100, $date = null, $fakeUrl_flg = true)
    {
        $articles = array();
        if(is_null($date)){
            $date = date("Y-m-d 23:59:59");
        }
        $sql = "SELECT sa.attribute_id, fa.*, fd.*, fd.id AS feed_id
                FROM rss_site_article AS sa
                LEFT JOIN rss_feed_date AS fd ON sa.feed_id = fd.id
                LEFT JOIN rss_feed_account AS fa ON fd.rss_account_id = fa.id
                WHERE sa.del_flg = 0
                    AND sa.site_id = ?
                    AND fd.date < ?
                ORDER BY fd.date DESC LIMIT ?";
        $feeds = $this->DBobj->query($sql, array($this->Site_Id, $date, $limit_count));
        if(!$feeds){
            return $articles;
        }
        foreach($feeds as $feed){
            $article = new stdClass();
            $article->feed = $feed;
            $article->link = $feed->link_url;

            if($fakeUrl_flg){
                $fake_urls = $this->getFakeLinkUrl($feed->attribute_id);
                if($fake_urls and count($fake_urls)){
                    $article->fake_link = $fake_urls[array_rand($fake_urls)]->site_url;
                }else{
                    $article->fake_link = null;
                }
            }else{
                $article->fake_link = null;
            }

            $articles[] = $article;
        }
        return $articles;
    }

    //指定属性記事 件数縛り取得 指定時間から指定件数取得
    public function getAttributeArticle_InCount($attribute_id, $limit_count = 100, $date = null, $fakeUrl_flg = true)
    {
        $articles = array();
        if(is_null($date)){
            $date = date("Y-m-d 23:59:59");
        }
        $sql = "SELECT sa.attribute_id, fa.*, fd.*, fd.id AS feed_id
                FROM rss_site_article AS sa
                LEFT JOIN rss_feed_date AS fd ON sa.feed_id = fd.id
                LEFT JOIN rss_feed_account AS fa ON fd.rss_account_id = fa.id
                WHERE sa.del_flg = 0
                    AND sa.site_id = ?
                    AND sa.attribute_id = ?
                    AND fd.date < ?
                ORDER BY fd.date DESC LIMIT ?";
        $feeds = $this->DBobj->query($sql, array($this->Site_Id, $attribute_id, $date, $limit_count));
        if(!$feeds){
            return $articles;
        }
        foreach($feeds as $feed){
            $article = new stdClass();
            $article->feed = $feed;
            $article->link = $feed->link_url;

            if($fakeUrl_flg){
                $fake_urls = $this->getFakeLinkUrl($feed->attribute_id);
                if($fake_urls and count($fake_urls)){
                    $article->fake_link = $fake_urls[array_rand($fake_urls)]->site_url;
                }else{
                    $article->fake_link = null;
                }
            }else{
                $article->fake_link = null;
            }

            $articles[] = $article;
        }
        return $articles;
    }

    private function getFakeLinkUrl($attribute_id)
    {
        $sql = "SELECT sac.site_url
                FROM rss_site_attribute AS sat
                LEFT JOIN rss_site_account AS sac ON sat.site_id = sac.id
                WHERE sat.del_flg = 0
                    AND sac.use_flg = 1
                    AND sat.attribute_id = ?
                    AND sat.site_id <> ?";
        return $this->DBobj->query($sql, array($attribute_id, $this->Site_Id));
    }

    public function makeRandStr($length)
    {
        $str = array_merge(range('a', 'z'), range('0', '9'), range('A', 'Z'));
        $r_str = null;
        for ($i = 0; $i < $length; $i++) {
            $r_str .= $str[rand(0, count($str))];
        }
        return $r_str;
    }

    //クリックカウントインクリメント
    public function feed_clickcount($feed_id, $ua=null)
    {
        //IP重複チェック
        if(!$this->checkClickUnique($feed_id)){
            return;
        }

        $device_sql = '';
        if(!is_null($ua)){
            $device = $this->checkDevice($ua);
            if($device === 'tablet' or $device === 'others'){
                $device_sql = ', pc_click_count = pc_click_count + 1';
            }elseif($device === 'mobile'){
                $device_sql = ', smp_click_count = smp_click_count + 1';
            }
        }
        $sql = 'UPDATE rss_article_clickcount SET click_count = click_count + 1'.$device_sql.' WHERE site_id = ? AND feed_id = ?';
        $this->DBobj->execute($sql, array($this->Site_Id, $feed_id));
        //固体識別情報取得
        $clickcount_hosts_fields = array(
                "site_id" => $this->Site_Id,
                "feed_id" => $feed_id,
                "ip_address" => $_SERVER["REMOTE_ADDR"],
                "ua" => $ua
            );
        $sql = "INSERT INTO rss_clickcount_hosts
                (".implode(", ", array_keys($clickcount_hosts_fields)).", create_date)
                VALUES (".implode(", ", array_fill(0, count($clickcount_hosts_fields), '?')).", now())";
        $this->DBobj->execute($sql, $clickcount_hosts_fields);
    }

    //固体識別チェック IPのみ
    //重複がない、インクリメントしてOK->true
    private function checkClickUnique($feed_id)
    {
        $since_time = date("Y-m-d H:i:s", (time() - 86400));//一日以内
        $sql = "SELECT id FROM rss_clickcount_hosts WHERE site_id = ? AND feed_id = ? AND ip_address = ? AND create_date >= ?";
        $res = $this->DBobj->query($sql, array($this->Site_Id, $feed_id, $_SERVER["REMOTE_ADDR"], $since_time));
        if(!$res){
            return true;
        }else{
            return false;
        }
    }

    public function checkDevice($ua)
    {
        $device = '';
        if(strpos($ua,'iphone') !== false){
            $device = 'mobile';
        }elseif(strpos($ua,'ipod') !== false){
            $device = 'mobile';
        }elseif((strpos($ua,'android') !== false) and (strpos($ua, 'mobile') !== false)){
            $device = 'mobile';
        }elseif((strpos($ua,'windows') !== false) and (strpos($ua, 'phone') !== false)){
            $device = 'mobile';
        }elseif((strpos($ua,'firefox') !== false) and (strpos($ua, 'mobile') !== false)){
            $device = 'mobile';
        }elseif(strpos($ua,'blackberry') !== false){
            $device = 'mobile';
        }elseif(strpos($ua,'ipad') !== false){
            $device = 'tablet';
        }elseif(strpos($ua,'windows') !== false and strpos($ua, 'touch') !== false and strpos($ua, 'tablet pc') == false){
            $device = 'tablet';
        }elseif(strpos($ua,'android') !== false and strpos($ua, 'mobile') === false){
            $device = 'tablet';
        }elseif((strpos($ua,'firefox') !== false) and (strpos($ua, 'tablet') !== false)){
            $device = 'tablet';
        }elseif((strpos($ua,'kindle') !== false) or (strpos($ua, 'silk') !== false)){
            $device = 'tablet';
        }elseif(strpos($ua,'playbook') !== false){
            $device = 'tablet';
        }else{
            $device = 'others';
        }
        return $device;
    }

}



