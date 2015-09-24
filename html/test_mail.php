<?php
require_once("../conf.php");
require_once(_TWITTER_CLASS_PATH."Mail.php");

$alert_mail_add = array('taroimotaro2222@docomo.ne.jp');
$subject = "テスト2";
$body = "テストてすとtest2";
Alert_Mail::sendAlertMail($alert_mail_add, $subject, $body);

