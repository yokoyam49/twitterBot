<?php
require_once("../conf.php");
require_once(_TWITTER_API_PATH."search/search_tweets.php");

$SearchTweets_obj = new search_tweets();

$res = $SearchTweets_obj->setSearchArr('beeworks')->setOption(array('count'=>'10'))->Request();

//var_dump($res);

foreach ($res->statuses as $result){
    $name = $result->user->name;
    $link = $result->user->profile_image_url;
    $content = $result->text;
    $updated = $result->created_at;
    $time = $time = date("Y-m-d H:i:s",strtotime($updated));

    echo "<img src='".$link."''>"." | ".$name." | ".$content." | ".$time;
	echo '<br>';
}
