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
    //強制リザルトタイプ これに設定されたリザルトタイプのみを実行
    //実質、popular→動作に変化なし mixed→やる意味がない ので設定されるとしたらrecentのみ
    private $ResultType = null;

    // 入力設定======
    //全取得件数
    private $MaxCount = 400;
    //trueのときリツイートを実行しない
    private $viewMode = false;

    // 固定設定======
    //一回に取得する件数
    private $Count = 100;

    //検索結果
    private $Search_Res;
    //リツイートするID
    private $TweetId;

    //検索オプション
    private $SerchAction;
    //検索タイプごとの検索数
    private $ResultType_Search_Count = array(
    					'popular' => null,
    					'recent' => null
    					);
    //リツイート済みリスト
    //private $RetweetedList;

    //OAuthオブジェクト
    private $twObj;
    //DBオブジェクト
    private $DBobj;

    private $logFile;

    public function __construct()
    {
        $this->DBobj = new DB_Base();
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
        $sql = "SELECT search_str_1, ng_words, result_type, search_count_popular, search_count_recent, minimum_retweet_num FROM dt_search_action WHERE account_id = ?";
        $res = $this->DBobj->query($sql, array($this->Account_ID));
        $this->SerchAction = $res[0];
        $this->ResultType_Search_Count['popular'] = $res[0]->search_count_popular;
        $this->ResultType_Search_Count['recent'] = $res[0]->search_count_recent;
    }

    public function setResultType($ResultType)
    {
        $this->ResultType = $ResultType;
        return $this;
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

    //重複していないID取得 全て重複時、nullリターン
    public function getAnDuplicateTweetID(){
        foreach($this->Search_Res as $tweet){
            if($this->checkRetweeted((string)$tweet->id) and $this->checkNgWord($tweet)){
                return $tweet;
            }
        }
        return null;
    }

    public function SearchTweets()
    {
        $tweetsData = array();
        $max_id = null;
        foreach($this->ResultType_Search_Count as $result_type => $result_type_count){
            if(!is_null($this->ResultType) and $this->ResultType == 'recent' and $this->ResultType != $result_type){
                continue;
            }
	        for($i = 0; $i < $result_type_count; $i += $this->Count){

	            $option = array(
	                                'count' => $this->Count,
	                                'result_type' => $result_type,
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
	            	$mes = "searchレスポンス result_type:".$result_type." ".(string)count($res->statuses)."件"."\n";
	            	error_log($mes, 3, _TWITTER_LOG_PATH.$this->logFile);

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

    public function Retweets($tweet){
        if(!$this->checkRetweetNum($tweet)){
            $mes = $tweet->id.": retweet_count: ".$tweet->retweet_count." 最低リツイート数設定に達しなかったため、リツイート未実行"."\n";
            error_log($mes, 3, _TWITTER_LOG_PATH.$this->logFile);
            return $this;
        }
        $retweetObj = new statuses_retweet($this->twObj);
        $apires = $retweetObj->setRetweetId($tweet->id)->Request();
        //リツイートリストに追加 エラーチェックする前に追加（エラー時でも追加される）
        $sql = "INSERT INTO dt_retweet_list ( account_id, tweet_id, search_str, tweet_text, retweet_count, create_date ) VALUES ( ?, ?, ?, ?, ? now() )";
        $res = $this->DBobj->execute($sql, array((int)$this->Account_ID, $tweet->id, $this->SerchAction->search_str_1, $tweet->text, $tweet->retweet_count));

        //エラーチェック
        $apiErrorObj = new Api_Error($apires);
        if($apiErrorObj->error){
            throw new Exception($apiErrorObj->errorMes_Str);
        }
        unset($apiErrorObj);

        return $this;
    }

    //最小リツイート数をクリアしているかチェック クリア:true アウト:false
    private function checkRetweetNum($tweet)
    {
        if($tweet->retweet_count < $this->SerchAction->minimum_retweet_num){
            return false;
        }else{
            return true;
        }
    }

    //既にリツイート済みでないかチェック
    //OK=>true 重複=>false
    private function checkRetweeted($id){
        $sql = "SELECT tweet_id FROM dt_retweet_list WHERE account_id = ? AND tweet_id = ?";
        $res = $this->DBobj->query($sql, array($this->Account_ID, $id));
        if(!isset($res[0])){
            return true;
        }else{
            return false;
        }
    }

    //NGワードチェック OK時tureリターン
    private function checkNgWord($tweet){
        //NGワード設定なし
        if(is_null($this->SerchAction->ng_words)){
            return true;
        }

        $ng_words = explode(' ', $this->SerchAction->ng_words);
        foreach($ng_words as $ng_word){
            if(mb_stripos($tweet->text, $ng_word) !== false ){
                return false;
            }
            if( isset($tweet->id_str) and $tweet->id_str === $ng_word ){
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


