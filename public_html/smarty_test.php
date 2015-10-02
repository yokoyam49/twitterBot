<?php
require_once("../conf.php");
require_once(_TWITTER_CLASS_PATH."View.php");

$ViewObj = new View();
$ViewObj->assign('name', '太郎');
$ViewObj->display('test.tpl');
