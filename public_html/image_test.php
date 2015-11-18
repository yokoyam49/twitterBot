<?php
require_once("../conf.php");

require_once(_TWITTER_CLASS_PATH."Image.php");

if(!isset($_REQUEST['image']) or !strlen($_REQUEST['image'])){
    exit();
}

header('Content-Type: image/jpeg');

$imageObj = new Image($_REQUEST['image']);
$imageObj->resizeImage(200, 200)
         ->output_ImageResource();


