<?php

class statuses_update
{
    private $twObj;
    private $ApiUrl = 'statuses/update';

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

    public function getResponse()
    {
        return $this->Response;
    }

    //実行
    public function Request()
    {

        $res = $this->twObj->post(
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


