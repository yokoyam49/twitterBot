<?php
//
require_once(_TWITTER_API_PATH."search/search_tweets.php");
require_once(_TWITTER_API_PATH."statuses/statuses_homeTimeline.php");
require_once(_TWITTER_API_PATH."statuses/statuses_retweet.php");
require_once(_TWITTER_CLASS_PATH."Api_Error.php");

use Abraham\TwitterOAuth\TwitterOAuth;

class Cron_Tweets_Popularity_Logic
{
        // 入力情報
    //処理を実行するアカウントID
    private $Account_ID;
    private $AccountInfo = null;

    // 入力設定======
    //全取得件数
    private $MaxCount = 1000;
    //trueのときリツイートを実行しない
    private $viewMode = false;

    // 固定設定======
    //一回に取得する件数
    private $Count = 100;
    //cron何秒毎実行かをセット 重複を取得しないために設定
    private $Bitween_Time = 3600;

    //検索結果
    private $Search_Res;
    //リツイートするID
    private $TweetId;

    //検索オプション
    private $SerchAction;
    //リツイート済みリスト
    private $RetweetedList;

    //OAuthオブジェクト
    private $twObj;
    //DBオブジェクト
    private $DBObj;

    private $logFile;

    public function __construct()
    {
        $this->DBObj = new DB_Base();
        $this->logFile = 'log_'.date("Y_m_d").".log";
    }

    //アカウントset OAuthオブジェクト再接続
    public function setAccountId($id)
    {
        $this->Account_ID = $id;

        //前の情報破棄
        if(!is_null($this->twObj)){
            unset($this->twObj);
        }
        if(!is_null($this->AccountInfo)){
            unset($this->AccountInfo);
        }

        $MS_AccountObj = new MS_Account();
        $this->AccountInfo = $MS_AccountObj->getAccountById($id);
        if(!is_null($this->twObj)){
            unset($this->twObj);
        }
        $this->twObj = new TwitterOAuth(
                                $this->AccountInfo->consumer_key,
                                $this->AccountInfo->consumer_secret,
                                $this->AccountInfo->access_token,
                                $this->AccountInfo->access_token_secret
                                );

        //初期設定取得
        $this->DBgetInitInfo();
    }

    private function DBgetInitInfo()
    {
        //検索オプション取得
        $sql = "SELECT search_str_1, result_type FROM dt_search_action WHERE account_id = ?";
        $this->SerchAction = $this->DBobj->query($sql, array($this->Account_ID));
        //リツイートリスト取得
        $sql = "SELECT tweet_id, create_date FROM dt_retweet_list WHERE account_id = ?";
        $this->RetweetedList = $this->DBobj->query($sql, array($this->Account_ID));
    }

    public function setViewMode($id){
        $this->setAccountId($id);
    	$this->viewMode = true;
    	return $this;
    }
    public function getSearch_Res(){
    	return $this->Search_Res;
    }
    public function getTweetId(){
    	return $this->TweetId;
    }
/*
    public function Exec()
    {
        try{
            $tweetId = null;
            //検索 人気順並び替え
            $this->SearchTweets();
            //重複チェック
            $overlapID_Arr = array();
            foreach($this->Search_Res as $tweet){
                if($this->checkRetweeted($tweet->id)){
                    $tweetId = $tweet->id;
                    break;
                }
                $overlapID_Arr[] = $tweet->id;
            }
            if(is_null($tweetId)){
            	$overlapIDs = implode(",", $overlapID_Arr);
                $mes = "TwieetID:".$overlapIDs." 全て重複"."\n";
                throw new Exception($mes);
            }
            //リツイート
            //viewModeのときはツイートしない
            if(!$this->viewMode){
            	$this->Retweets($tweetId);
            }
            $this->TweetId = $tweetId;

        }catch(Exception $e){
            //ログ出力
            error_log($e->getMessage(), 3, _TWITTER_LOG_PATH.$this->logFile);
        }
    }
*/
    //重複していないID取得 全て重複時、nullリターン
    public function getAnDuplicateTweetID(){
        $tweetId = null;
        $overlapID_Arr = array();
        foreach($this->Search_Res as $tweet){
            if($this->checkRetweeted($tweet->id)){
                $tweetId = $tweet->id;
                break;
            }
            $overlapID_Arr[] = $tweet->id;
        }

        return $tweetId;
    }

    public function SearchTweets()
    {
        $tweetsData = array();
        $max_id = null;
        for($i = 0; $i < $this->MaxCount; $i += $this->Count){

            $option = array(
                                'count' => $this->Count,
                                'result_type' => $this->SerchAction->result_type,
                                'lang' => 'ja',
                                'locale' => 'ja'
                            );
            if(!is_null($max_id)){
                $option['max_id'] = $max_id;
            }
            $SearchTweets_obj = new search_tweets($this->twObj);
            $res = $SearchTweets_obj->setSearchStr($this->SerchAction->search_str_1)->setOption($option)->Request();
            //エラーチェック
            $apiErrorObj = new Api_Error($res);
            if($apiErrorObj->error){
                throw new Exception($apiErrorObj->errorMes_Str);
            }
            unset($apiErrorObj);
            //検索結果ログ出力
            if(!$res_count = count($res->statuses)){
            	$mes = "searchレスポンス 0件";
            	throw new Exception($mes);
            }else{
            	$mes = "searchレスポンス ".(string)$res_count."件"."\n";
            	error_log($mes, 3, _TWITTER_LOG_PATH.$this->logFile);
            }

            //確実に結合するためforeachで
            foreach($res->statuses as $tweet){
                //リツイートの場合、オリジナルを取得
                if(isset($tweet->retweeted_status) and isset($tweet->retweeted_status->id) and strlen($tweet->retweeted_status->id)){
                    $tweetsData[] = $tweet->retweeted_status;
                }else{
                    $tweetsData[] = $tweet;
                }
            }

            if($res->search_metadata->count < $this->Count){
                break;
            }

            $max_id = $this->getMaxId($res);
            if(!$max_id){
            	break;
                //$mes = "MaxId取得失敗";
                //throw new Exception($mes);
            }
        }

        //並び替え
        $tweetsData = $this->multisortRetweetCount($tweetsData);
        //usort($tweetsData, array($this, 'usortRetweetCountCmp'));
        $this->Search_Res = $tweetsData;

        return $this;
    }

    //MaxID取得
    private function getMaxId($recs)
    {
    	if(!isset($recs->search_metadata->next_results)){
    		return false;
    	}
        $next_results = $recs->search_metadata->next_results;
        $next_results = htmlspecialchars_decode($next_results);
        $next_results = str_replace("?", "", $next_results);
        $next_results = urldecode($next_results);
        $qus = explode("&", $next_results);
        foreach($qus as $qu){
            list($name, $value) = explode("=", $qu);
            if($name == "max_id" and strlen($value)){
                return $value;
            }
        }
        return false;
    }

    public function Retweets($id){
        $retweetObj = new statuses_retweet($this->twObj);
        $res = $retweetObj->setRetweetId($id)->Request();
        //エラーチェック
        $apiErrorObj = new Api_Error($res);
        if($apiErrorObj->error){
            throw new Exception($apiErrorObj->errorMes_Str);
        }
        unset($apiErrorObj);

        //リツイートリストに追加
        $sql = "INSERT INTO dt_retweet_list ( account_id, tweet_id, create_date ) VALUES ( ?, ?, now() )";
        $res = $this->DBobj->exec($sql, array($this->Account_ID, $id));

        return $this;
    }

    //既にリツイート済みでないかチェック
    //OK=>true 重複=>false
    private function checkRetweeted($id){

        foreach($this->RetweetedList as $Retweeted){
            if($Retweeted->tweet_id == $id){
                return false;
            }
        }

        return true;
    }

    private function usortRetweetCountCmp($a, $b){
        $a_reco = $a->retweet_count;
        $b_reco = $b->retweet_count;
        if ($a_reco == $b_reco) {
            return 0;
        }
        return ($a_reco > $b_reco) ? -1 : 1;
    }

    private function multisortRetweetCount($tweetsData){
        $cmplist1 = array();
        $cmplist2 = array();
        foreach($tweetsData as $tweet){
            $cmplist1[] = $tweet->retweet_count;
            $cmplist2[] = $tweet->favorite_count;
        }
        array_multisort(
        				$cmplist1, SORT_DESC,
        				$cmplist2, SORT_DESC,
        				$tweetsData
        				);
        return $tweetsData;
    }



}


