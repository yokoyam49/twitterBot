<?php
require_once(_TWITTER_API_PATH."friendships/friendships_destroy.php");
require_once(_TWITTER_API_PATH."users/users_lookup.php");
require_once(_TWITTER_CLASS_PATH."Api_Error.php");

use Abraham\TwitterOAuth\TwitterOAuth;

class Cron_Follower_ExRemove_Logic
{

    // 入力情報
    //処理を実行するアカウントID
    private $Account_ID;

    // DBより取得するパラメーター
    //アカウント情報
    private $AccountInfo = null;

    // 固定設定
    //一日にリムーブする最大数設定
    private $MaxRemove_inDay = 3;
    //これよりフォロワー数が多いアカウントをリムーブ
    private $LargeThanForrow_num = 500;


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
        //$this->DBgetFollowParm();
    }

    // private function DBgetFollowParm()
    // {
    //     $sql = "SELECT follownum_inday, difference_follow, last_active_daypast FROM dt_follower_action WHERE account_id = ?";
    //     $res = $this->DBobj->query($sql, array($this->Account_ID));
    //     //一日にフォローする数
    //     $this->Follow_Num_Inday = $res[0]->follownum_inday;
    //     //フォロー数とフォロワー数の差の最大値
    //     $this->Difference_Follow = $res[0]->difference_follow;
    //     //最終アクティブ判定日 この日数以上動きがないユーザーはアタックしない
    //     $this->Last_Active_Daypast = $res[0]->last_active_daypast;
    // }

    //対象ユーザー情報を取得し、設定フォロワー数を超えているものをDBにフラグセット
    public function getTargetUserAndSetDB()
    {
        $sql = "SELECT user_id FROM dt_follower_cont WHERE account_id = ? AND following = 1 AND followed = 1 AND follower_count IS NULL";
        $res = $this->DBobj->query($sql, array($this->Account_ID));

        if($res and count($res)){
            $mes = "調査対象ユーザー件数:".count($res)."件\n";
            error_log($mes, 3, _TWITTER_LOG_PATH.'log_'.date("Y_m_d").".log");
            $users = array();
            foreach($res as $rec){
                $users[] = $rec->user_id;
            }
            //対象ユーザーobj取得
            $this->setFollowerNum($users);
        }else{
            $mes = "調査対象ユーザー件数:0件\n";
            error_log($mes, 3, _TWITTER_LOG_PATH.'log_'.date("Y_m_d").".log");
        }
    }

    //DBより処理対象を取得し、規定件数だけRemove
    public function execRemove()
    {
        $sql = "SELECT user_id, followed_date, follower_count FROM dt_follower_cont WHERE account_id = ? AND following = 1 AND followed_date <= ? AND follower_count >= ? ORDER BY followed_date ASC";
        $following_date = date("Y-m-d H:is", strtotime("-1 week"));
        $res = $this->DBobj->query($sql, array($this->Account_ID, $following_date, $this->LargeThanForrow_num));

        if($res and count($res)){
            $remove_users = array();
            for($i = 0; $i < $this->MaxRemove_inDay and $i < count($res); $i++){
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
            $mes = $removed_num."件 リムーブ処理実行\n";
            error_log($mes, 3, _TWITTER_LOG_PATH.$this->logFile);
            $mes = implode(", ", $remove_users)."\n";
            error_log($mes, 3, _TWITTER_LOG_PATH.$this->logFile);
        }else{
            $mes = "0件 リムーブ処理実行\n";
            error_log($mes, 3, _TWITTER_LOG_PATH.$this->logFile);
        }
    }

    //apiよりフォロワー数を取得
    private function setFollowerNum($user_ids)
    {
        //仕様上一度に100が最大
        if(count($user_ids) > 100){
            $user_ids = array_slice($user_ids, 0, 100);
        }
        $option = array(
                            'user_id' => implode(",", $user_ids),
                            'include_entities' => false
                        );
        $UsersLookup = new users_lookup($this->twObj);
        $api_res = $UsersLookup->setOption($option)->Request();
        //エラーチェック
        $apiErrorObj = new Api_Error($api_res);
        if($apiErrorObj->error){
            //エラーのときは処理終了
            throw new Exception($apiErrorObj->errorMes_Str);
        }
        unset($apiErrorObj);

        foreach($api_res as $user){
            //フォロワー数セット
            $sql = "UPDATE dt_follower_cont SET follower_count = ? WHERE account_id = ? AND user_id = ?";
            $upcount = $this->DBobj->execute($sql, array($user->followers_count, $this->Account_ID, (string)$user->id));
        }
    }

    //クリックカウント用固体識別情報テーブル 古いデータ削除処理
    public function deleteClickcountHosts()
    {
        $delete_since_date = date("Y-m-d H:i:s", (time() - 86400 * 3));//3日前より古いデータは削除
        $sql = "DELETE FROM rss_clickcount_hosts WHERE create_date <= ?";
        $delete_count = $this->DBobj->execute($sql, array($delete_since_date));

        $delete_count = $delete_count ? $delete_count : 0;
        $mes = date("Y-m-d H:i:s")." rss_clickcount_hostsテーブル ".(string)$delete_count."件 データ削除処理実行\n";
        error_log($mes, 3, _TWITTER_LOG_PATH.$this->logFile);
    }

}

