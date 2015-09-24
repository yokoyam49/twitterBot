<?php

class Alert_Mail
{
    public static $sys_mail_add = "twitter_admin@ainyan.minibird.jp";


    public static function sendAlertMail($to, $subject, $body)
    {
        if(!is_array($to)){
            $to_arr = array($to);
        }else{
            $to_arr = $to;
        }
        foreach($to_arr as $to_ad){
            //送信
            mb_send_mail($to_ad, $subject, $body, "From:".self::$sys_mail_add);
        }
    }
}



