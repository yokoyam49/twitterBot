<?php
//
require_once(_TWITTER_API_PATH."search/search_tweets.php");
require_once(_TWITTER_API_PATH."statuses/statuses_homeTimeline.php");
require_once(_TWITTER_CLASS_PATH."Api_Error.php");

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
    private $MaxCount = 1000;
    //cron何秒毎実行かをセット 重複を取得しないために設定
    private $Bitween_Time = 3600;

    //検索結果
    private $Search_Res;


    public function setInit($SearchStr, $Until_Time, $Count)
    {
        $this->SearchStr = $SearchStr;
        //$this->Until_Time = $Until_Time;
        $this->Count = $Count;
    }

    public function Exec()
    {

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
            $SearchTweets_obj = new search_tweets();
            $res = $SearchTweets_obj->setSearchArr($this->SearchStr)->setOption($option)->Request();
            //エラーチェック
            $apiErrorObj = new Api_Error($res);
            if($apiErrorObj->error){
                throw new Exception($apiErrorObj->errorMes_Str);
            }
            unset($apiErrorObj);

            $tweetsData = array_merge($tweetsData, $res->statuses);

            if($res->search_metadata->count < $this->Count){
                break;
            }

            $max_id = $res->search_metadata->max_id;
        }

        //並び替え
        usort($tweetsData, array($this, 'usortRetweetCountCmp'));
        $this->Search_Res = $tweetsData;

        return $this;
    }

    private function usortRetweetCountCmp($a, $b){
        $a_reco = $a->retweet_count;
        $b_reco = $b->retweet_count;
        if ($a_reco == $b_reco) {
            return 0;
        }
        return ($a_reco < $b_reco) ? -1 : 1;
    }



}


