<?php
require_once("../conf.php");

class search_tweets
{
	private $twObj;
	private $ApiUrl = 'https://api.twitter.com/1.1/search/tweets.json';
	
	private $SearchArr = array();
	private $Response = null;
	private $Options = array();
	
	public __construct()
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
	
	//ŽÀs
	public function Request()
	{
		$this->Options['q'] = implode(' AND ', $this->SearchArr);
		
		$res = $this->twObj->OAuthRequest(
		    $this->ApiUrl,
		    'GET',
		    $this->Options
		);
		
		$this->Response = json_decode($res, true);
		
		return $this->Response;
	}
	
}
