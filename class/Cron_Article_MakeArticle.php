<?php
require_once(_TWITTER_CLASS_PATH."DB_Base.php");
require_once(_TWITTER_CLASS_PATH."Cron_Article_MakeArticle_ArticleSearch.php");

class Cron_Article_MakeArticle
{
    private $Attributes;

    //サーチオブジェクト
    private $ArticleSearchObj;

    public function __construct()
    {
        $this->getAttributes();
        $this->ArticleSearchObj = new Cron_Article_MakeArticle_ArticleSearch();
        $this->logFile = 'article_log_'.date("Y_m_d").".log";
    }

    //属性情報取得
    private function getAttributes()
    {
        $sql = "SELECT * FROM rss_attribute_info WHERE del_flg = 0 ORDER BY rank ASC";
        $this->Attributes = $this->DBobj->query($sql);
    }

    public function Exec()
    {

        foreach($this->Attributes as $attribute){
            $mes = date("Y-m-d H:i:s")." 属性:".$attribute->attribute_name." 記事生成開始\n";
            error_log($mes, 3, _RSS_LOG_PATH.$this->logFile);

            try{
                    //属性に一致する記事を抽出
                    $this->ArticleSearchObj->setAttributeId($attribute->id);
                    $feed_ids = $this->ArticleSearchObj->ArticleSearch();
                    //記事生成
                    $this->make_Article($attribute->id, $feed_ids);

            }catch(Exception $e){
                //ログ出力
                error_log($e->getMessage(), 3, _RSS_LOG_PATH.$this->logFile);
            }

            $mes = date("Y-m-d H:i:s")." 属性:".$attribute->attribute_name." 記事生成終了\n";
            error_log($mes, 3, _RSS_LOG_PATH.$this->logFile);
        }

    }

    //記事生成
    private function make_Article($attribute_id, $feed_ids)
    {
        $site_ids = array();
        foreach($feed_ids as $feed_id){
            //site_id取得
            $sql = "SELECT sa.site_id AS site_id
                    FROM rss_site_attribute AS sa
                    WHERE attribute_id = ?
                        AND NOT EXISTS (
                            SELECT site_id
                            FROM rss_site_article
                            WHERE sa.site_id = site_id
                                AND feed_id = ?
                            )";
            $res = $this->DBobj->query($sql, array($attribute_id, $feed_id));
            if(!$res){
                continue;
            }
            //rss_site_articleへ記事生成
            foreach($res as $rec){
                $site_article_fields = array(
                        'site_id' => $rec->site_id,
                        'feed_id' => $feed_id,
                        'attribute_id' => $attribute_id,
                        'del_flg' => 0
                    );
                $sql = "INSERT INTO rss_site_article
                        (".implode(", ", array_keys($site_article_fields)).", create_date)
                        VALUES (".implode(", ", array_fill(0, count($site_article_fields), '?')).", now())";
                $in_count = $this->DBobj->execute($sql, $site_article_fields);

                $mes = "記事生成 site_id:".$rec->site_id." feed_id:".$feed_id."\n";
                error_log($mes, 3, _RSS_LOG_PATH.$this->logFile);
            }
        }
    }

}

