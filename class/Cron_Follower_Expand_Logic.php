<?php
require_once(_TWITTER_API_PATH."followers/followers_ids.php");
require_once(_TWITTER_API_PATH."friendships/friendships_create.php");
require_once(_TWITTER_API_PATH."friendships/friendships_destroy.php");
require_once(_TWITTER_API_PATH."users/users_lookup.php");
require_once(_TWITTER_CLASS_PATH."Api_Error.php");

use Abraham\TwitterOAuth\TwitterOAuth;

class Cron_Follower_Expand_Logic
{

    // 入力情報
    //処理を実行するアカウントID
    private $Account_ID;

    // DBより取得するパラメーター
    //アカウント情報
    private $AccountInfo = null;
    //一日にフォローする数
    private $Follow_Num_Inday;
    //フォロー数とフォロワー数の差の最大値
    private $Difference_Follow;
    //最終アクティブ判定日 この日数以上動きがないユーザーはアタックしない
    private $Last_Active_Daypast;


    // apiから取得情報
    //自分のフォロワー一覧
    //private $Follower_List = array();

    //OAuthオブジェクト
    private $twObj = null;
    //DBオブジェクト
    private $DBobj;

    private $logFile;

    private $alert_mail_add = array('taroimotaro2222@docomo.ne.jp', '96kanabe@ezweb.ne.jp');

    public function __construct()
    {
        $this->DBobj = new DB_Base();
        $this->logFile = 'log_'.date("Y_m_d").".log";
    }

    //アカウントset OAuthオブジェクト再接続
    public function setAccountId($id)
    {
        $this->Account_ID = $id;

        //前のアカウント情報破棄
        if(!is_null($this->twObj)){
            unset($this->twObj);
        }
        if(!is_null($this->AccountInfo)){
            unset($this->AccountInfo);
        }

        $MS_AccountObj = new MS_Account();
        $this->AccountInfo = $MS_AccountObj->getAccountById($id);

        $this->twObj = new TwitterOAuth(
                                $this->AccountInfo->consumer_key,
                                $this->AccountInfo->consumer_secret,
                                $this->AccountInfo->access_token,
                                $this->AccountInfo->access_token_secret
                                );

        //DBよりパラメーター取得
        $this->DBgetFollowParm();
    }

    private function DBgetFollowParm()
    {
        $sql = "SELECT follownum_inday, difference_follow, last_active_daypast FROM dt_follower_action WHERE account_id = ?";
        $res = $this->DBobj->query($sql, array($this->Account_ID));
        //一日にフォローする数
        $this->Follow_Num_Inday = $res[0]->follownum_inday;
        //フォロー数とフォロワー数の差の最大値
        $this->Difference_Follow = $res[0]->difference_follow;
        //最終アクティブ判定日 この日数以上動きがないユーザーはアタックしない
        $this->Last_Active_Daypast = $res[0]->last_active_daypast;
    }

    //apiよりフォロワーリスト取得しフォロー状況をDBに反映
    public function getMyFollwerList()
    {
        $follower_count = 0;
        $cursor = null;
        for($i = 0; $i < 20; $i++){
            $option = array(
                                'screen_name' => $this->AccountInfo->screen_name,
                                'stringify_ids' => true,
                                'count' => 5000
                            );
            if(!is_null($cursor)){
                $option['cursor'] = $cursor;
            }
            $FollowersIds_obj = new followers_ids($this->twObj);
            $api_res = $FollowersIds_obj->setOption($option)->Request();

            //エラーチェック
            $apiErrorObj = new Api_Error($api_res);
            if($apiErrorObj->error){
                throw new Exception($apiErrorObj->errorMes_Str);
            }
            unset($apiErrorObj);
            //APIのレスポンスより、DBにフォロー状況反映
            $this->DBsetFollowersData($api_res->ids);
            $follower_count += count($api_res->ids);

            if(isset($api_res->next_cursor) and $api_res->next_cursor > 0){
                $cursor = $api_res->next_cursor;
            }else{
                break;
            }
        }
        $mes = date("Y-m-d H:i:s")." 総取得フォロワー数 ".$follower_count."件\n";
        error_log($mes, 3, _TWITTER_LOG_PATH.$this->logFile);
    }

    //フォロー状況をDBに反映
    private function DBsetFollowersData($follower_list){
        $followed_num = 0;
        $removed_num = 0;
        $listMap = array();
        if(is_array($follower_list) and count($follower_list)){
            //マッピング配列
            $listMap = array_flip($follower_list);
            //フォローされている
            $follows = "(".implode(",", $follower_list).")";
            $sql = "UPDATE dt_follower_cont SET followed = 1, followed_date = now() WHERE account_id = ? AND followed = 0 AND user_id IN ".$follows;
            $res = $this->DBobj->execute($sql, array($this->Account_ID));
            if($res){
            	$followed_num = $res;
            }
        }
        //リムーブチェック
        $removed_user = array();
        $sql = "SELECT user_id FROM dt_follower_cont WHERE account_id = ? AND followed = 1";
        $res = $this->DBobj->query($sql, array($this->Account_ID));
        //フォロー数が突然0になっている 凍結の疑い
        if($res and count($res) and !count($follower_list)){
            $mes = "フォロワー数が０になっています。アカウントを確認してください。\n";
            $sql = "INSERT INTO dt_message ( account_id, message1, check_flg, create_date) VALUES ( ?, ?, 0, now())";
            $in_count = $this->DBobj->execute($sql, array($this->Account_ID, $mes));
            //メール送信
            $subject = 'アカウント：'.$this->AccountInfo->notice;
            $this->sendAlertMail($this->alert_mail_add, $subject, $mes);
            //処理中止
            throw new Exception($mes);
        }
        if($res and count($res)){
	        foreach($res as $user){
	            if(!isset($listMap[$user->user_id])){
	                //リムーブされている
	                $removed_user[] = $user->user_id;
	            }
	        }
	    }
        //リムーブ状況反映
        if(count($removed_user)){
            $removed = "(".implode(",", $removed_user).")";
            $sql = "UPDATE dt_follower_cont SET followed = 0, removed_date = now() WHERE account_id = ? AND user_id IN ".$removed;
            $removed_num = $this->DBobj->execute($sql, array($this->Account_ID));
        }

        $mes = date("Y-m-d H:i:s")." フォロー状況を反映しました 新規followed件数 ".$followed_num."件 removed件数 ".$removed_num."件\n";
        error_log($mes, 3, _TWITTER_LOG_PATH.$this->logFile);
    }

    //フォローを返さないuserを抽出し、規定数リムーブ
    public function FollowingRemove()
    {
        //不具合が出ないよう設定調整
        if($this->Follow_Num_Inday > $this->Difference_Follow){
            $this->Follow_Num_Inday = $this->Difference_Follow;
        }
        //リムーブしなくてはならない数
        $must_remove_num = 0;
        //対象抽出
        $sql = "SELECT user_id, following_date FROM dt_follower_cont WHERE account_id = ? AND following = 1 AND followed = 0 ORDER BY following_date ASC";
        $res = $this->DBobj->query($sql, array($this->Account_ID));
        if(!$res or !count($res)){
            //リムーブする必要なし
            return;
        }
        //リムーブ必要数計算
        $must_remove_num = count($res) + $this->Follow_Num_Inday - $this->Difference_Follow;
        if($must_remove_num <= 0){
            //リムーブする必要なし
            return;
        }
        //リムーブ数が妙に増大
        if($must_remove_num > ($this->Follow_Num_Inday * 2)){
            $must_remove_num = $this->Follow_Num_Inday * 2;
            $mes = 'リムーブ数が増大しています。アカウントを確認してください。';
            $sql = "INSERT INTO dt_message ( account_id, message1, check_flg, create_date) VALUES ( ?, ?, 0, now())";
            $in_count = $this->DBobj->execute($sql, array($this->Account_ID, $mes));
            //メール送信
            $subject = 'アカウント：'.$this->AccountInfo->notice;
            $this->sendAlertMail($this->alert_mail_add, $subject, $mes);
        }
        $remove_users = array();
        for($i = 0; $i < $must_remove_num and $i < count($res); $i++){
            //リムーブ
            $option = array(
                                'user_id' => $res[$i]->user_id,
                            );
            $FriendshipsDestroy = new friendships_destroy($this->twObj);
            $api_res = $FriendshipsDestroy->setOption($option)->Request();
            //エラーチェック リムーブできなくてもDBセット
            $apiErrorObj = new Api_Error($api_res);
            if($apiErrorObj->error){
                $mes = $apiErrorObj->errorMes_Str;
                error_log($mes, 3, _TWITTER_LOG_PATH.$this->logFile);
            }
            unset($apiErrorObj);
            $remove_users[] = $res[$i]->user_id;
        }
        //リムーブ情報DBセット
        $sql = "UPDATE dt_follower_cont SET following = 0, removing_date = now() WHERE account_id = ? AND user_id IN ";
        $sql .= "( ".implode(", ", $remove_users)." )";
        $removed_num = $this->DBobj->execute($sql, array($this->Account_ID));
    }

    //フォローターゲットを抽出し、規定数フォローしに行く
    public function GoFollowing()
    {
        //フォローターゲット取得
        //$TagetUsers = $this->getTargetUser();
        list($ActiveUser, $NonActiveUser) = $this->getTargetUser();
        if(count($ActiveUser)){
        	$insert_value = array();
            foreach($ActiveUser as $user){
                $option = array(
                                    'user_id' => $user->id,
                                    'follow' => false
                                );
                $FriendshipsCreate = new friendships_create($this->twObj);
                $api_res = $FriendshipsCreate->setOption($option)->Request();
                //エラーチェック フォローできなくてもDBセットする
                $apiErrorObj = new Api_Error($api_res);
                if($apiErrorObj->error){
                    //throw new Exception($apiErrorObj->errorMes_Str);
                    $mes = $apiErrorObj->errorMes_Str;
                    error_log($mes, 3, _TWITTER_LOG_PATH.$this->logFile);
                }
                unset($apiErrorObj);
                //インサートバリュー生成
                $values = array((string)$this->Account_ID, (string)$user->id, "1", "'".$this->getUserLastActiveTime($user)."'", "1", "0");
                $insert_value[] = "( ".implode(", ", $values).", now(), now() )";
            }
            //フォロー情報DBセット
            $sql = "INSERT INTO dt_follower_cont ( account_id, user_id, active_user_flg, last_active_time, following, followed, following_date, create_date ) VALUES ";
            $sql .= implode(", ", $insert_value);
            $res = $this->DBobj->exec($sql);
        }
        $mes = date("Y-m-d H:i:s")." ".count($ActiveUser)."件 フォローしました\n";
        error_log($mes, 3, _TWITTER_LOG_PATH.$this->logFile);
        
        //ノンアクティブユーザー情報セット
        if(count($NonActiveUser)){
            $insert_value = array();
            foreach($NonActiveUser as $user){
                $values = array((string)$this->Account_ID, (string)$user->id, "0", "'".$this->getUserLastActiveTime($user)."'", "0", "0");
                $insert_value[] = "( ".implode(", ", $values).", now() )";
            }
            $sql = "INSERT INTO dt_follower_cont ( account_id, user_id, active_user_flg, last_active_time, following, followed, create_date ) VALUES ";
            $sql .= implode(", ", $insert_value);
            $res = $this->DBobj->exec($sql);
            $mes = date("Y-m-d H:i:s")." ノンアクティブ判定ユーザー： ".count($NonActiveUser)."件 \n";
            error_log($mes, 3, _TWITTER_LOG_PATH.$this->logFile);
        }
    }

    private function getTargetUser()
    {
        $TagetUsers = array();
        $ActiveUser = array();
        $NonActiveUser = array();
        $sql = "SELECT target_user_id, target_screen_name FROM dt_follower_target WHERE account_id = ?";
        $res = $this->DBobj->query($sql, array($this->Account_ID));
        foreach($res as $rec){
            $cursor = null;
            for($i = 0; $i < 20; $i++){
                $option = array(
                                    'screen_name' => $rec->target_screen_name,
                                    'stringify_ids' => true,
                                    'count' => 5000
                                );
                if(!is_null($cursor)){
                    $option['cursor'] = $cursor;
                }
                $FollowersIds_obj = new followers_ids($this->twObj);
                $api_res = $FollowersIds_obj->setOption($option)->Request();
                //エラーチェック
                $apiErrorObj = new Api_Error($api_res);
                if($apiErrorObj->error){
                    throw new Exception($apiErrorObj->errorMes_Str);
                }
                unset($apiErrorObj);

                foreach($api_res->ids as $user_id){
                    if($this->checkAlreadyFollowing($user_id)){
                        $TagetUsers[] = $user_id;
                    }
                    if(count($TagetUsers) >= $this->Follow_Num_Inday){
                        //アクティブ抽出処理
                        list($actives, $nonacs) = $this->checkActiveUser($TagetUsers);
                        $NonActiveUser = array_merge($NonActiveUser, $nonacs);
                        foreach($actives as $active){
                            array_push($ActiveUser, $active);
                            if(count($ActiveUser) >= $this->Follow_Num_Inday){
                                //ターゲット取得完了
                                return array($ActiveUser, $NonActiveUser);
                            }
                        }
                        $TagetUsers = array();
                    }
                }

                if(isset($api_res->next_cursor) and $api_res->next_cursor > 0){
                    $cursor = $api_res->next_cursor;
                }else{
                    break;
                }
            }
        }
        //ターゲット枯渇
        $mes = "フォローターゲットが枯渇しました。新たなターゲットを設定してください。\n";
        $sql = "INSERT INTO dt_message ( account_id, message1, check_flg, create_date) VALUES ( ?, ?, 0, now())";
        $res = $this->DBobj->execute($sql, array($this->Account_ID, $mes));
        error_log($mes, 3, _TWITTER_LOG_PATH.$this->logFile);
        return $TagetUsers;
    }

    //既にアタック済みかチェック アタックしていない：true アタック済み：false
    private function checkAlreadyFollowing($user_id)
    {
        $sql = "SELECT user_id FROM dt_follower_cont WHERE account_id = ? AND user_id = ?";
        $res = $this->DBobj->query($sql, array($this->Account_ID, $user_id));
        if(!isset($res[0]->user_id)){
            return true;
        }else{
            return false;
        }
    }

    private function checkActiveUser($TagetUsers)
    {
        $ActveUser = array();
        $NonacUser = array();
        $option = array(
                            'user_id' => implode(",", $TagetUsers),
                            'include_entities' => false
                        );
        $UsersLookup = new users_lookup($this->twObj);
        $api_res = $UsersLookup->setOption($option)->Request();
        //エラーチェック
        $apiErrorObj = new Api_Error($api_res);
        if($apiErrorObj->error){
            //エラーのとき、チェックをあきらめる
            $resUsers = array();
            foreach($TagetUsers as $user){
                $resUser = new stdClass();
                $resUser->id = $user;
                $resUsers[] = $resUser;
            }
            $mes = $apiErrorObj->errorMes_Str."\n";
            error_log($mes, 3, _TWITTER_LOG_PATH.$this->logFile);
            return array($resUsers, array());
        }
        unset($apiErrorObj);

        foreach($api_res as $user){
            if( floor((time() - strtotime($user->created_at)) / 86400) < $this->Last_Active_Daypast
                or (isset($user->status->created_at) and floor((time() - strtotime($user->status->created_at)) / 86400) < $this->Last_Active_Daypast) ){
                $ActveUser[] = $user;
            }else{
                $NonacUser[] = $user;
            }
        }

        return array($ActveUser, $NonacUser);
    }

    private function getUserLastActiveTime($user){
        $res_time = "";
        if(!isset($user->status->created_at) and !isset($user->created_at)){
            return date("Y-m-d H:i:s");
        }
        if(!isset($user->status->created_at)){
            $res_time = $user->created_at;
        }elseif(strtotime($user->created_at) <= strtotime($user->status->created_at)){
            $res_time = $user->status->created_at;
        }else{
            $res_time = $user->created_at;
        }
        return date("Y-m-d H:i:s", strtotime($res_time));
    }

    private function sendAlertMail($to, $subject, $body)
    {
        if(!is_array($to)){
            $to_arr = array($to);
        }else{
            $to_arr = $to;
        }
        $from = "twitter_admin@ainyan.minibird.jp";
        foreach($to_arr as $to_ad){
            //送信
            mb_send_mail($to_ad, $subject, $body, "From:".$from);
        }
    }

}

