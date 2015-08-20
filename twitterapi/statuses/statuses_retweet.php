<?php

class statuses_retweet
{
    private $twObj;
    private $ApiUrl = 'statuses/retweet';
    private $RetweetId;

    private $Response = null;
    private $Options = array();

    public function __construct($twObj)
    {
        $this->twObj = $twObj;
    }

    public function setRetweetId($RetweetId){
        $this->RetweetId = $RetweetId;
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
            $this->ApiUrl.'/'.(string)$this->RetweetId
            );

        $this->Response = $res;

        return $this->Response;
    }
}

