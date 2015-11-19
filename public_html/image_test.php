<?php
require_once("../conf.php");

require_once(_TWITTER_CLASS_PATH."Image.php");

if(!isset($_REQUEST['image']) or !strlen($_REQUEST['image'])){
    exit();
}

header('Content-Type: image/jpeg');

$imageObj = new Image();
$imageObj->setImage($_REQUEST['image']);
$imageObj->resizeImage(200, 200)
         ->output_ImageResource('/home/ainyan/blog.graph.jp/public_html/img/oretekigame/oretekigame_151119_132925_200x200.jpg');


