<?php

class followers_ids
{
    private $twObj;
    private $ApiUrl = 'followers/ids';

    private $Response = null;
    private $Options = array();

    private $user_id = null;
    private $screen_name = null;
    private $count = 5000;

    private $stringify_ids = true;
    private $cursor = null;

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

    public function setCount($count)
    {
        $this->count = $count;
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
            if(!is_null($this->cursor)){
                $this->Options['cursor'] = $this->cursor;
            }
            $this->Options['count'] = $this->count;
            $this->Options['stringify_ids'] = $this->stringify_ids;
        }

        $res = $this->twObj->get(
            $this->ApiUrl,
            $this->Options
        );

        $this->Response = $res;

        return $this->Response;
    }

}


