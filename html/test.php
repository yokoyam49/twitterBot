<?php
require_once("../conf.php");
require_once(_TWITTER_CLASS_PATH."Cron_Tweets_Popularity.php");

$Popularity = new Cron_Tweets_Popularity();
$Popularity->setInit('#DQ', 0, 100)->Exec();



foreach ($Popularity->Search_Res as $result){
	$id = $result->id;
	$retweet_count = $result->retweet_count;
	$favorite_count = $result->favorite_count;
    $name = $result->user->name;
    $link = $result->user->profile_image_url;
    $content = $result->text;
    $updated = $result->created_at;
    $time = $time = date("Y-m-d H:i:s",strtotime($updated));

    echo "<img src='".$link."''>"." | ".$id." | ".$favorite_count." | ".$retweet_count." | ".$name." | ".$content." | ".$time;
	echo '<br>';
}
