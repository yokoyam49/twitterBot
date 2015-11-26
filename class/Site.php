<?php
require_once(_TWITTER_CLASS_PATH."DB_Base.php");

class Site
{
    private $Site_Id;
    private $Site_Info;
    private $Site_Attribute = array();

    public function __construct($site_id)
    {
        //サイトIDセット
        $this->Site_Id = $site_id;

        //サイト情報取得
        $this->getMySiteInfo();
        //自サイトの属性情報取得
        $this->getMySiteAttribute();
    }

    private function getMySiteInfo()
    {
        $sql = "SELECT *
                FROM rss_site_account
                WHERE id = ?";
        $res = $this->DBobj->query($sql, array($this->Site_Id));
        $this->Site_Info = $res[0];
    }

    //自サイトの属性情報取得(array)
    private function getMySiteAttribute()
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


}



