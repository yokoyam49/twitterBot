<?php

class statuses_destroy
{
    private $twObj;
    private $ApiUrl = 'statuses/destroy';
    private $TweetId;

    private $Response = null;
    private $Options = array();

    public function __construct($twObj)
    {
        $this->twObj = $twObj;
    }

    public function setTweetId($TweetId){
        $this->TweetId = $TweetId;
        return $this;
    }

    public function setOption($options)
    {
        $this->Options = $options;
        return $this;
    }

    //実行
    public function Request()
    {

        $res = $this->twObj->post(
            $this->ApiUrl.'/'.(string)$this->TweetId
            );

        $this->Response = $res;

        return $this->Response;
    }
}

