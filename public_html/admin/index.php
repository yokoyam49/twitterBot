<?php
session_start();
require_once("../../conf.php");
require_once(_TWITTER_CLASS_PATH."Dispatcher.php");

$DispatchObj = new Dispatcher();
$DispatchObj->dispatch();

