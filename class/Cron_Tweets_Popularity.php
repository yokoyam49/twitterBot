<?php
//
require_once(_TWITTER_API_PATH."search/search_tweets.php");
require_once(_TWITTER_API_PATH."statuses/statuses_homeTimeline.php");
require_once(_TWITTER_API_PATH."statuses/statuses_retweet.php");
require_once(_TWITTER_CLASS_PATH."Api_Error.php");

use Abraham\TwitterOAuth\TwitterOAuth;

class Cron_Tweets_Popularity
{
    // 入力設定======
    //検索文字
    private $SearchStr;
    //指定時間より過去のを取得（現在からの秒数指定）※時間指定できないっぽいので中止
    //private $Until_Time;
    //一回に取得する件数
    private $Count;

    // 固定設定======
    private $Result_Type = 'mixed';
    //全取得件数
    private $MaxCount = 100;
    //cron何秒毎実行かをセット 重複を取得しないために設定
    private $Bitween_Time = 3600;
    //既にリツイート済みでないか確認する時、ホームタイムライン幾つまでさかのぼってチェックするか
    private $checkHomeTimeline_Num = 50;

    //検索結果
    public $Search_Res;
    //タイムライン重複チェック用
    private $TimeLine_List = null;

    //OAuthオブジェクト
    private $twObj;

    // 認証情報 ==========
    private $CONSUMER_KEY = null;
    private $CONSUMER_SECRET = null;
    private $ACCESS_TOKEN = null;
    private $ACCESS_TOKEN_SECRET = null;

    private $logFile;

    public function __construct($CONSUMER_KEY = null, $CONSUMER_SECRET = null, $ACCESS_TOKEN = null, $ACCESS_TOKEN_SECRET = null)
    {
        if(!is_null($CONSUMER_KEY) and !is_null($CONSUMER_SECRET) and !is_null($ACCESS_TOKEN) and !is_null($ACCESS_TOKEN_SECRET)){
            $this->twObj = new TwitterOAuth($CONSUMER_KEY, $CONSUMER_SECRET, $ACCESS_TOKEN, $ACCESS_TOKEN_SECRET);
        }else{
            $this->twObj = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, ACCESS_TOKEN, ACCESS_TOKEN_SECRET);
        }

        $this->logFile = 'log_CronTweetsPopularity_'.date("Y_m_d").".log";
    }

    public function setInit($SearchStr, $Until_Time, $Count)
    {
        $this->SearchStr = $SearchStr;
        //$this->Until_Time = $Until_Time;
        $this->Count = $Count;
        return $this;
    }

    //認証セット
    public function setAuthInfo($CONSUMER_KEY, $CONSUMER_SECRET, $ACCESS_TOKEN, $ACCESS_TOKEN_SECRET){
        $this->CONSUMER_KEY = $CONSUMER_KEY;
        $this->CONSUMER_SECRET = $CONSUMER_SECRET;
        $this->ACCESS_TOKEN = $ACCESS_TOKEN;
        $this->ACCESS_TOKEN_SECRET = $ACCESS_TOKEN_SECRET;
        return $this;
    }

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
            //$this->Retweets($tweetId);

        }catch(Exception $e){
            //ログ出力
            error_log($e->getMessage(), 3, _TWITTER_LOG_PATH.$this->logFile);
        }
    }

    private function SearchTweets()
    {
        $tweetsData = array();
        $max_id = null;
        for($i = 0; $i < $this->MaxCount; $i += $this->Count){

            $option = array(
                                'count' => $this->Count,
                                'result_type' => $this->Result_Type,
                                'lang' => 'ja',
                                'locale' => 'ja'
                            );
            if(!is_null($max_id)){
                $option['max_id'] = $max_id;
            }
            $SearchTweets_obj = new search_tweets($this->twObj);
            $res = $SearchTweets_obj->setSearchStr($this->SearchStr)->setOption($option)->Request();
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

    private function Retweets($id){
        $retweetObj = new statuses_retweet($this->twObj);
        $res = $retweetObj->setRetweetId($id)->Request();
        //エラーチェック
        $apiErrorObj = new Api_Error($res);
        if($apiErrorObj->error){
            throw new Exception($apiErrorObj->errorMes_Str);
        }
        unset($apiErrorObj);
        return $this;
    }

    //既にリツイート済みでないかチェック
    //OK=>true 重複=>false
    private function checkRetweeted($id){
        //初回タイムライン取得
        if(is_null($this->TimeLine_List)){
            $this->TimeLine_List = array();
            $option = array(
                                'count' => $this->checkHomeTimeline_Num
                            );
            $homeTimelineObj = new statuses_homeTimeline($this->twObj);
            $res = $homeTimelineObj->setOption($option)->Request();
            //エラーチェック
            $apiErrorObj = new Api_Error($res);
            if($apiErrorObj->error){
                throw new Exception($apiErrorObj->errorMes_Str);
            }
            $this->TimeLine_List = $res;
        }
        foreach($this->TimeLine_List as $line){
            if(!isset($line->retweeted_status)){
                continue;
            }
            if($line->retweeted_status->id == $id){
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


