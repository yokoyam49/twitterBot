<?php
require_once(_TWITTER_CLASS_PATH."DB_Base.php");
require_once(_TWITTER_CLASS_PATH."MS_Account.php");
require_once(_TWITTER_API_PATH."media/media_upload.php");
require_once(_TWITTER_API_PATH."statuses/statuses_update.php");
require_once(_TWITTER_API_PATH."statuses/statuses_retweet.php");
require_once(_TWITTER_CLASS_PATH."Api_Error.php");

class Article_Tweet
{
    private $Site_Id = null;
    private $Site_Info = null;
    private $Tw_Account_Info = null;

    private $Article_Id = null;
    private $Article_Info = null;
    //ツイート成功時、ツイートID保存
    private $tweet_id = null;


    private $DBobj;
    //OAuthオブジェクト
    private $twObj;
    private $logFile;

    public function __construct()
    {
        $this->DBobj = new DB_Base();
        $this->logFile = 'rss_log_'.date("Y_m_d").".log";
    }

    public function setSiteId($site_id)
    {
        $this->Site_Id = $site_id;
        $this->setSiteInfo();
        //前の情報破棄
        if(!is_null($this->twObj)){
            unset($this->twObj);
        }
    }

    public function setArticleId($article_id)
    {
        $this->Article_Id = $article_id;
        $this->setArticleInfo();
    }

    public function reset()
    {
        $this->Site_Id = null;
        $this->Site_Info = null;
        $this->Tw_Account_Info = null;
        $this->Article_Id = null;
        $this->Article_Info = null;
        $this->tweet_id = null;
    }

    private function setSiteInfo()
    {
        $sql = "SELECT *
                FROM rss_site_account
                WHERE id = ?";
        $res = $this->DBobj->query($sql, array($this->Site_Id));
        $this->Site_Info = $res[0];
    }

    private function getTwAccountInfo()
    {
        $AccountObj = new MS_Account();
        $this->Tw_Account_Info = $AccountObj->getAccountById($this->Site_Info->twitter_account_id);
    }

    private function setArticleInfo()
    {
        $sql = "SELECT fd.*, sa.attribute_id
                FROM rss_site_article AS sa
                LEFT JOIN rss_feed_date AS fd ON sa.feed_id = fd.id
                WHERE sa.id = ?";
        $res = $this->DBobj->query($sql, array($this->Article_Id));
        $this->Article_Info = $res[0];
    }

    private function setTwObj()
    {
        //twアカウント情報
        $this->getTwAccountInfo();
        $this->twObj = new TwitterOAuth(
                                $this->Tw_Account_Info->consumer_key,
                                $this->Tw_Account_Info->consumer_secret,
                                $this->Tw_Account_Info->access_token,
                                $this->Tw_Account_Info->access_token_secret
                                );

    }

    public function Article_Tweet()
    {
        //twOBJセット
        $this->setTwObj();
        //画像があればアップロードしてメディアID取得
        $media_id = null;
        if(!is_null($this->Article_Info->image_url) and strlen($this->Article_Info->image_url)){
            $media_id = $this->Media_Upload();
        }
        //ツイート処理
        $tw_mes = $this->Article_Info->title." ".$this->Site_Info->site_url;
        $statusesUpdateObj = new statuses_update($this->twObj);
        $option = array(
                            "status" => $tw_mes,
                            "possibly_sensitive" => false,
                            "trim_user" => true,
                    );
        if($media_id){
            $option["media_ids"] = $media_id;
        }
        $api_res = $statusesUpdateObj->setOption($option)->Request();
        $apiErrorObj = new Api_Error($api_res);
        if($apiErrorObj->error){
            $mes = $apiErrorObj->errorMes_Str;
            error_log($mes, 3, _TWITTER_LOG_PATH.$this->logFile);
        }else{
            $this->tweet_id = $api_res->id_str;
        }

        //tw済みフラグセット
        $this->updateTwtedFlg();
    }

    private function updateTwtedFlg()
    {
        $sql = "UPDATE rss_site_article SET tweeted_flg = 1, tweet_date = now() WHERE id = ?";
        $this->DBobj->execute($sql, array($this->Article_Id));
    }

    private function Media_Upload()
    {
        $media_data = file_get_contents($this->Article_Info->image_url);
        if(!$media_data){
            return false;
        }
        $mediaUploadObj = new media_upload($this->twObj);
        $mediaUploadObj->setMedia($media_data)->Request();
        $media_id = $mediaUploadObj->getMediaId();
        return $media_id;
    }

    public function Article_ReTweet()
    {
        if(!$this->tweet_id){
            return false;
        }

        $retwwt_acounts = $this->getRetweetAcounts();
        if($retwwt_acounts){
            foreach($retwwt_acounts as $account){
                $twObj = new TwitterOAuth(
                                $account->consumer_key,
                                $account->consumer_secret,
                                $account->access_token,
                                $account->access_token_secret
                                );
                $retweetObj = new statuses_retweet($this->twObj);
                $apires = $retweetObj->setRetweetId($this->tweet_id)->Request();
                $apiErrorObj = new Api_Error($apires);
                if($apiErrorObj->error){
                    $mes = 'リツイート失敗 feed_id:'.$this->Article_Info->id.' account:'.$account->account_name.' ['.$apiErrorObj->errorMes_Str."]\n";
                    error_log($mes, 3, _TWITTER_LOG_PATH.$this->logFile);
                }
            }
        }

    }

    //リツイートする側のアカウント情報取得
    private function getRetweetAcounts()
    {
        $sql = "SELECT a.*
                FROM rss_account_retweet_attribute AS ra
                LEFT JOIN ms_account AS a ON ra.tw_account_id = a.id
                WHERE
                    ra.use_flg = 1
                    AND ra.attribute_id = ?";
        $res = $this->DBobj->query($sql, array($this->Article_Info->attribute_id));
        return $res;
    }



}

