<?php

use Abraham\TwitterOAuth\TwitterOAuth;

class search_tweets
{
	private $twObj;
	private $ApiUrl = 'search/tweets';
	
	private $SearchArr = array();
	private $Response = null;
	private $Options = array();
	
	public function __construct()
	{
		$this->twObj = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, ACCESS_TOKEN, ACCESS_TOKEN_SECRET);
		
	}
	
	public function setSearchArr($search_arr)
	{
		$this->SearchArr = $search_arr;
		return $this;
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
	
	//���s
	public function Request()
	{
		$this->Options['q'] = implode(' AND ', $this->SearchArr);

		$res = $this->twObj->get(
			$this->ApiUrl,
			$this->Options
		);
		
		//$res = $this->twObj->post("statuses/update", array("status" => "�e�X�g���b�Z�[�W"));
		
		//$this->Response = json_decode($res, true);
		$this->Response = $res;
		
		return $this->Response;
	}
	
}
