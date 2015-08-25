<?php

class friendships_destroy
{
    private $twObj;
    private $ApiUrl = 'friendships/destroy';

    private $Response = null;
    private $Options = array();

    private $user_id = null;
    private $screen_name = null;

    public function __construct($twObj)
    {
        $this->twObj = $twObj;
    }

    public function setUser_id($user_id)
    {
        $this->user_id = $user_id;
    }

    public function setScreen_name($screen_name)
    {
        $this->screen_name = $screen_name;
    }

    public function setOption($options)
    {
        $this->Options = $options;
        return $this;
    }

    public function getResponse()
    {
        return $this->Response;
    }

    //実行
    public function Request()
    {
        if(!count($this->Options)){
            if(!is_null($this->screen_name)){
                $this->Options['screen_name'] = $this->screen_name;
            }elseif(!is_null($this->user_id)){
                $this->Options['user_id'] = $this->user_id;
            }
        }

        $res = $this->twObj->post(
            $this->ApiUrl,
            $this->Options
        );

        $this->Response = $res;

        return $this->Response;
    }

}
