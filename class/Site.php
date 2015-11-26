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

    //記事取得 割合だまし
    public function getArticle_FakeLink_Ratio($ratio)
    {
        $sql = "SELECT ";
    }


}



