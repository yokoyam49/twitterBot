<?php
require_once(_TWITTER_CLASS_PATH."DB_Base.php");
require_once(_TWITTER_CLASS_PATH."Request.php");
require_once(_TWITTER_CLASS_PATH."View.php");
require_once(_TWITTER_CLASS_PATH."Article_Tweet.php");

class Admin_Articletweet
{
    private $ViewObj;
    private $RequestObj;
    private $DBobj;

    private $mes = '';

    public function __construct()
    {
        $this->DBobj = new DB_Base();
        $this->ViewObj = new View();
        $this->RequestObj = new Request();
    }

    public function index()
    {
        $request = $this->RequestObj;

        //
        if(isset($request->mode) and strlen($request->mode)){
            $action = 'action_'.$request->mode;
            if(method_exists($this, $action)){
                $this->$action();
            }
        }

        $site_info = $this->getSiteInfo();

        $this->ViewObj->assign('site_info', $site_info);
        $this->ViewObj->assign('mes', $this->mes);
        $this->ViewObj->display('Admin_Articletweet.tpl');

    }

    private function action_article_tweet()
    {
        $request = $this->RequestObj;
        if(!$this->checkArticle()){
            $this->mes = "指定の記事が存在しません";
            return;
        }

        $ArticleTweet_Obj = new Article_Tweet();

        $ArticleTweet_Obj->setSiteId($request->site_id);
        $ArticleTweet_Obj->setArticleId($request->article_id);

        //記事がツイート済みでないか
        if($ArticleTweet_Obj->isArticleTweeted()){
            //DBより取得
            $ArticleTweet_Obj->setTweetedId();
        }else{
            //ツイート
            $ArticleTweet_Obj->Article_Tweet();
            sleep(3);
        }

        $ArticleTweet_Obj->Article_ReTweet();

        $this->mes = '処理終了';
    }

    private function checkArticle()
    {
        $request = $this->RequestObj;
        $sql = "SELECT id FROM rss_site_article WHERE id = ? AND site_id = ?";
        $res = $this->DBobj->query($sql, array($request->article_id, $request->site_id));
        if($res){
            return true;
        }else{
            return false;
        }
    }

    private function getSiteInfo()
    {
        $sql = "SELECT *
                FROM rss_site_account";
        $res = $this->DBobj->query($sql);
        $site_info = array();
        foreach($res as $account){
            $site_info[$account->id] = $account->site_name_mb;
        }
        return $site_info;
    }

}





