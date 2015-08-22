<?php
require_once("../conf.php");
require_once(_TWITTER_CLASS_PATH."Cron_Tweets_Popularity.php");

$Popularity = new Cron_Tweets_Popularity();
$Popularity->setInit('#DQ OR #DQ1 OR #DQ2 OR #DQ3 OR #DQ4 OR #DQ5 OR #DQ6 OR #DQ7 OR #DQ8 OR #DQ9 OR #DQ10 OR #DQ11 OR #ドラクエ -rt', 0, 100)->setViewMode()->Exec();


echo 'リツイート判定ID：'.$Popularity->getTweetId()."<br><br>\n";
foreach ($Popularity->getSearch_Res() as $result){
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
