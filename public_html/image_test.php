<?php
require_once("../conf.php");

require_once(_TWITTER_CLASS_PATH."Image.php");

$imageObj = new Image("./img/Chrysanthemum.jpg");
$imageObj->resizeImage("./img/Chrysanthemum_200_200.jpg", 200, 200);


