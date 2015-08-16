<?php
require_once("../twitterapi/search/search_tweets.php");

$SearchTweets_obj = new search_tweets();

$res = $SearchTweets_obj->setSearchArr(array('beeworks'))->setOption(array('count'=>'30'))->Request();

foreach ($res['statuses'] as $result){
    $name = $result['user']['name'];
    $link = $result['user']['profile_image_url'];
    $content = $result['text'];
    $updated = $result['created_at'];
    $time = $time = date("Y-m-d H:i:s",strtotime($updated));

    echo "<img src='".$link."''>"." | ".$name." | ".$content." | ".$time;
	echo '<br>';
}
