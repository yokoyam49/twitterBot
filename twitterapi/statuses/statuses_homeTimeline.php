<?php

class statuses_homeTimeline
{
    private $twObj;
    private $ApiUrl = 'statuses/home_timeline';

    private $Response = null;
    private $Options = array();

    public function __construct($twObj)
    {
        $this->twObj = $twObj;
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

        $this->Response = $res;

        return $this->Response;
    }


}