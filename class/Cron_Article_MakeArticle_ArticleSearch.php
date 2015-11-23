<?php

class Cron_Article_MakeArticle_ArticleSearch
{
    //属性情報
    private $Attribute_Id;
    private $Attribute_info;
    private $Search_word_arr = array();
    private $Search_hash_arr = array();

    //設定
    const SEARCH_PASTDAY = 86400;//一日のタイムスタンプ 一日以内の記事の中から検索

    //結果記事
    private $Res_Feeds = array();

    private $DBobj;

    public function __construct()
    {
        $this->DBobj = new DB_Base();
        $this->logFile = 'article_log_'.date("Y_m_d").".log";
    }

    public function setAttributeId($attribute_id)
    {
        $this->Res_Feeds = array();
        $this->Attribute_Id = $attribute_id;
        $this->Attribute_info = $this->getAttributeInfo($attribute_id);
        $this->separat_search_hash();
        if((!is_array($this->Search_word_arr) or !count($this->Search_word_arr)) and (!is_array($this->Search_hash_arr) or !count($this->Search_hash_arr))){
            $msg = 'rss_attribute_info: 検索文字列の形式が不正、もしくは空です';
            throw new Exception($msg);
        }
    }

    //検索文言をハッシュタグと一般検索文字列とに分離
    private function separat_search_hash()
    {
        $this->Search_word_arr = array();
        $this->Search_hash_arr = array();
        $word_arr = explode(" ", $this->Attribute_info->search_word);
        foreach($word_arr as $word){
            if(preg_match('/^#.+$/', $word)){
                $this->Search_hash_arr[] = $word;
            }else{
                $this->Search_word_arr[] = $word;
            }
        }
    }

    private function getAttributeInfo($attribute_id)
    {
        $sql = "SELECT * FROM rss_attribute_info WHERE id = ?";
        $res = $this->DBobj->query($sql, array($attribute_id));
        if($res){
            return $res[0];
        }else{
            return false;
        }
    }

    public function ArticleSearch()
    {
        $this->search_SameHashTag_Account();

        $this->search_feed_word();

        $this->Res_Feeds = array_values(array_unique($this->Res_Feeds));

        $mes = "抽出記事件数:".count($this->Res_Feeds)."件\n";
        error_log($mes, 3, _RSS_LOG_PATH.$this->logFile);

        return $this->Res_Feeds;
    }

    //一致する検索ハッシュタグを持っているFeedアカウントの記事を抽出
    private function search_SameHashTag_Account()
    {
        $past_time = date("Y-m-d H:i:s", time() - self::SEARCH_PASTDAY);

        if(!count($this->Search_hash_arr)){
            return ;
        }
        $hash_arr = $this->Search_hash_arr;

        //該当記事検索
        $where_hash_str = array();
        foreach($hash_arr as &$hash){
            $where_hash_str[] = "fa.search_hash like ?";
            $hash = '%'.$hash.'%';
        }
        $sql = "SELECT fd.id AS id
                FROM rss_feed_date AS fd
                LEFT JOIN rss_feed_account AS fa ON fd.rss_account_id = fa.id
                WHERE fd.del_flg = 0
                    AND (".implode(" OR ", $where_hash_str).")
                    AND fd.date > '".$past_time."'
                ORDER BY fd.date DESC";
        $res = $this->DBobj->query($sql, $hash_arr);
        if(!$res){
            return;
        }
        //抽出feedIDセット
        foreach($res as $rec){
            $this->Res_Feeds[] = $rec->id;
        }
    }

    //記事内容から検索
    private function search_feed_word()
    {
        $past_time = date("Y-m-d H:i:s", time() - self::SEARCH_PASTDAY);

        foreach($this->Search_word_arr as $search_word){
            $sql = "SELECT id
                    FROM rss_feed_date
                    WHERE del_flg = 0
                        AND (title LIKE ?
                            OR content LIKE ?
                            OR html_content LIKE ?)
                        AND date > '".$past_time."'
                    ORDER BY date DESC";
            $search_str = '%'.$search_word.'%';
            $res = $this->DBobj->query($sql, array($search_str, $search_str, $search_str));
            if(!$res){
                continue;
            }
            //抽出feedIDセット
            foreach($res as $rec){
                $this->Res_Feeds[] = $rec->id;
            }
        }
    }

}

