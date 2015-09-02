<?php

class users_lookup
{
    private $twObj;
    private $ApiUrl = 'users/lookup';

    private $Response = null;
    private $Options = array();

    //コンマ区切りで複数可 最大100件
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

    //配列渡し
    public function setUser_ids($user_ids)
    {
        $this->user_id = implode(",", $user_ids);
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

        $res = $this->twObj->get(
            $this->ApiUrl,
            $this->Options
        );

        $this->Response = $res;

        return $this->Response;
    }

	public function resetLastResponse()
	{
		$this->twObj->resetLastResponse();
	}

}


