<?php
require_once(_TWITTER_API_PATH."followers/followers_ids.php");
require_once(_TWITTER_API_PATH."friendships/friendships_create.php");
require_once(_TWITTER_API_PATH."friendships/friendships_destroy.php");
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

    // apiから取得情報
    //自分のフォロワー一覧
    //private $Follower_List = array();

    //OAuthオブジェクト
    private $twObj = null;
    //DBオブジェクト
    private $DBobj;

    private $logFile;

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
        $sql = "SELECT follownum_inday, difference_follow FROM dt_follower_action WHERE account_id = ?";
        $res = $this->DBobj->query($sql, array($this->Account_ID));
        //一日にフォローする数
        $this->Follow_Num_Inday = $res[0]->follownum_inday;
        //フォロー数とフォロワー数の差の最大値
        $this->Difference_Follow = $res[0]->difference_follow;
    }

    //apiよりフォロワーリスト取得しフォロー状況をDBに反映
    public function getMyFollwerList()
    {
        $follower_count = 0;
        $cursor = null;
        $this->Follower_List = array();
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
            $res = $FollowersIds_obj->setOption($option)->Request();
            //エラーチェック
            $apiErrorObj = new Api_Error($res);
            if($apiErrorObj->error){
                throw new Exception($apiErrorObj->errorMes_Str);
            }
            unset($apiErrorObj);
            //APIのレスポンスより、DBにフォロー状況反映
            $this->DBsetFollowersData($res->ids);
            $follower_count += count($res->ids);

            if(isset($res->next_cursor) and $res->next_cursor > 0){
                $cursor = $res->next_cursor;
            }else{
                break;
            }
        }
        $mes = date("Y-m-d H:i:s")." 総取得フォロワー数 ".$follower_count."件\n";
        error_log($mes, 3, _TWITTER_LOG_PATH.$this->logFile);
    }

    //フォロー状況をDBに反映
    private function DBsetFollowersData($follower_list){
        //フォローされている
        $follows = "(".implode(",", $follower_list).")";
        $sql = "UPDATE dt_follower_cont SET followed = 1, followed_date = now() WHERE account_id = ? AND followed = 0 AND user_id IN ".$follows;
        $followed_num = $this->DBobj->execute($sql, array($this->Account_ID));
        //リムーブチェック
        $removed_user = array();
        $sql = "SELECT user_id FROM dt_follower_cont WHERE account_id = ? AND followed = 1";
        $res = $this->DBobj->query($sql, array($this->Account_ID));
        $listMap = array_flip($follower_list);
        foreach($res as $user){
            if(!isset($listMap[$user->user_id])){
                //リムーブされている
                $removed_user[] = $user->user_id;
            }
        }
        //リムーブ状況反映
        $removed = "(".implode(",", $removed_user).")";
        $sql = "UPDATE dt_follower_cont SET followed = 0 removed_date = now() WHERE account_id = ? AND user_id IN ".$removed;
        $removed_num = $this->DBobj->execute($sql, array($this->Account_ID));

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
        if(!count($res)){
            //リムーブする必要なし
            return;
        }
        //リムーブ必要数計算
        $must_remove_num = count($res) + $this->Follow_Num_Inday - $this->Difference_Follow;
        if($must_remove_num <= 0){
            //リムーブする必要なし
            return;
        }
        for($i = 0; $i < $must_remove_num; $i++){
            //リムーブ
            $option = array(
                                'user_id' => $res[$i]->user_id,
                            );
            $FriendshipsDestroy = new friendships_destroy($this->twObj);
            $res = $FriendshipsDestroy->setOption($option)->Request();
            //エラーチェック
            $apiErrorObj = new Api_Error($res);
            if($apiErrorObj->error){
                throw new Exception($apiErrorObj->errorMes_Str);
            }
            unset($apiErrorObj);
        }
    }

    //フォローターゲットを抽出し、規定数フォローしに行く
    public function GoFollowing()
    {
        //フォローターゲット取得
        $TagetUsers = $this->getTargetUser();

        if(count($TagetUsers)){
            foreach($TagetUsers as $user_id){
                $option = array(
                                    'user_id' => $user_id,
                                    'follow' => false
                                );
                $FriendshipsCreate = new friendships_create($this->twObj);
                $res = $FriendshipsCreate->setOption($option)->Request();
                //エラーチェック
                $apiErrorObj = new Api_Error($res);
                if($apiErrorObj->error){
                    throw new Exception($apiErrorObj->errorMes_Str);
                }
                unset($apiErrorObj);
            }
        }
        $mes = date("Y-m-d H:i:s").count($TagetUsers)."件 フォローしました\n";
        error_log($mes, 3, _TWITTER_LOG_PATH.$this->logFile);
    }

    private function getTargetUser()
    {
        $TagetUsers = array();
        $sql = "SELECT target_user_id, target_screen_name FROM dt_follower_target WHERE account_id = ?";
        $res = $this->DBobj->query($sql, array($this->Account_ID));
        foreach($res as $rec){
            $cursor = null;
            for($i = 0; $i < 20; $i++){
                $option = array(
                                    'user_id' => $rec->target_user_id,
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
                        //ターゲット取得完了
                        return $TagetUsers;
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
        $mes = "フォローターゲットが枯渇しました。新たなターゲットを設定してください。";
        $sql = "INSERT INTO dt_message ( account_id, message1, check_flg, create_date) VALUES ( ?, ?, 0, now())";
        $res = $this->DBobj->execute($sql, array($this->Account_ID, $mes));
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


}

