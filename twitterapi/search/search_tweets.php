<?php

use Abraham\TwitterOAuth\TwitterOAuth;

class search_tweets
{
	private $twObj;
	private $ApiUrl = 'search/tweets';
	
	private $SearchStr = '';
	private $Response = null;
	private $Options = array();
	
	public function __construct()
	{
		$this->twObj = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, ACCESS_TOKEN, ACCESS_TOKEN_SECRET);
		
	}
	
	public function setSearchStr($search)
	{
		$this->SearchStr = $search;
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
	
	//実行
	public function Request()
	{
		$this->Options['q'] = $this->SearchStr;

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
