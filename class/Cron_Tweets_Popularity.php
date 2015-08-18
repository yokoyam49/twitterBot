<?php
//
require_once(_TWITTER_API_PATH."search/search_tweets.php");

class Cron_Tweets_Popularity
{
    // 入力設定======
    //検索文字
    private $SearchStr;
    //指定時間より過去のを取得（現在からの秒数指定）
    private $Until_Time;
    //取得する件数
    private $Count;

    // 固定設定======
    private $Result_Type = 'mixed';
    //最大取得件数
    private $MaxCount = 1000;
    //cron何秒毎実行かをセット 重複を取得しないために設定
    private $Bitween_Time = 3600;

    //検索結果
    private $Search_Res;


    public function setInit($SearchStr, $Until_Time, $Count)
    {
        $this->SearchStr = $SearchStr;
        $this->Until_Time = $Until_Time;
        $this->Count = $Count;
    }

    public function Exec()
    {

    }

    private function SearchTweets()
    {
        $since_id = null;
        for($i = 0; $i < $this->MaxCount; $i += $this->Count){

            $option = array(
                                'count' => $this->Count,
                                
                            );
            $SearchTweets_obj = new search_tweets();
            $res = $SearchTweets_obj->setSearchArr($this->SearchStr)->setOption(array('count'=>'10'))->Request();
        }
    }


}


