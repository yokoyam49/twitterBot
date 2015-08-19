<?php
use Abraham\TwitterOAuth\TwitterOAuth;

class statuses_homeTimeline
{
    private $twObj;
    private $ApiUrl = 'statuses/home_timeline';
    
    private $Response = null;
    private $Options = array();

    public function __construct()
    {
        $this->twObj = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, ACCESS_TOKEN, ACCESS_TOKEN_SECRET);
    }

    public function setOption($options)
    {
        $this->Options = $options;
        return $this;
    }

    //実行
    public function Request()
    {

        $res = $this->twObj->get(
            $this->ApiUrl,
            $this->Options
        );
        
        //$res = $this->twObj->post("statuses/update", array("status" => "テストメッセージ"));
        
        //$this->Response = json_decode($res, true);
        $this->Response = $res;
        
        return $this->Response;
    }
    

}