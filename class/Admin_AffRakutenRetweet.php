<?php

require_once(_TWITTER_CLASS_PATH."DB_Base.php");
require_once(_TWITTER_CLASS_PATH."MS_Account.php");
require_once(_TWITTER_CLASS_PATH."Request.php");
require_once(_TWITTER_CLASS_PATH."ShortUrl.php");
require_once(_TWITTER_CLASS_PATH."View.php");
require_once(_RAKUTEN_SDK_PATH."autoload.php");
//require(_TWITTER_OAUTH_PATH.'src/TwitterOAuth.php');
require_once(_TWITTER_API_PATH."statuses/statuses_update.php");
require_once(_TWITTER_API_PATH."statuses/statuses_destroy.php");
require_once(_TWITTER_API_PATH."media/media_upload.php");
require_once(_TWITTER_CLASS_PATH."Api_Error.php");

use Abraham\TwitterOAuth\TwitterOAuth;

class Admin_AffRakutenRetweet
{
    //アプリID
    private $rakuten_apuri_id = '1055279728800101163';
    //アフェリエイトID
    private $afferiate_id = '149211ea.72c5d66e.149211eb.93a8db3b';

    private $aff_rakuten_account_info = null;

    private $ViewObj;
    private $RequestObj;
    private $DBobj;
    private $logFile;

    private $Session = array(
                    'aff_rakuten_account_id' => null,
                    'aff_rakuten_account_name' => null,
                    'aff_retweet_reserve_info' => array(),
                    'search_api' => null,//0->商品検索 1->ランキング
                    'search_api_parms_list' => array(),
                    'search_api_parms' => array(),
                    'search_item_result' => array(),
                    'search_item_result_max_page' => null,
                    'search_item_result_now_page' => null,
                    'select_item_index' => null,
                    'select_img' => array(),
                    'retweet_comment' => null,
                    'retweet_link' => null,
                    'retweet_time' => null,
                    'result' => null,
                    'result_message' => null,
                    'tweet_id' => null,
                    'reserve_id' => null,
                    'error' => null,
                    'error_message' => null
            );

    private $api_select = array(
                    0 => '楽天市場商品検索API',
                    1 => '楽天市場ランキングAPI'
            );
    private $api_serch_method = array(
                    0 => 'itemSerch',
                    1 => 'ranking'
            );
    private $request_parms = array(
                    0 => array(
                        array('name' => 'keyword', 'mb_name' => 'キーワード'),
                        array('name' => 'shopCode', 'mb_name' => 'ショップコード'),
                        array('name' => 'itemCode', 'mb_name' => 'アイテムコード'),
                        array('name' => 'genreId', 'mb_name' => 'ジャンルID'),
                        array('name' => 'tagId', 'mb_name' => 'タグID'),
                        array('name' => 'page', 'mb_name' => 'ページ'),
                        array('name' => 'sort', 'mb_name' => 'ソート'),
                        array('name' => 'carrier', 'mb_name' => 'キャリア'),
                        array('name' => 'field', 'mb_name' => 'フィールド'),
                        array('name' => 'NGKeyword', 'mb_name' => 'NGワード'),
                        array('name' => 'minAffiliateRate', 'mb_name' => '最低アフェリエイト率')
                        ),
                    1 => array(
                        array('name' => 'genreId', 'mb_name' => 'ジャンルID'),
                        array('name' => 'age', 'mb_name' => '年齢'),
                        array('name' => 'sex', 'mb_name' => '性別'),
                        array('name' => 'carrier', 'mb_name' => 'キャリア'),
                        array('name' => 'page', 'mb_name' => 'ページ')
                        )
                );

    public function __construct()
    {
        $this->ViewObj = new View();
        $this->RequestObj = new Request();
        $this->DBobj = new DB_Base();
        $this->logFile = 'debug_log_'.date("Y_m_d").".log";

        $this->Session = $_SESSION['aff_rakuten_data'];

        //affアカウントIDがセットされていればアカウント情報取得
        if(!is_null($this->Session['aff_rakuten_account_id'])){
            $this->aff_rakuten_account_info = $this->get_RakutenAccountInfo($this->Session['aff_rakuten_account_id']);
        }
    }

    private function setSession()
    {
        unset($_SESSION['aff_rakuten_data']);
        $_SESSION['aff_rakuten_data'] = $this->Session;
    }

    public function index()
    {
        //全アカウント取得
        $all_aff_rakuten_account_info = $this->get_ALLRakutenAccountInfo();

        $rakuten_account = array();
        foreach($all_aff_rakuten_account_info as $account){
            $rakuten_account[$account->id] = $account->account_name_mb;
        }

        $this->ViewObj->assign('data', $this->Session);
        $this->ViewObj->assign('rakuten_account', $rakuten_account);
        $this->ViewObj->assign('api_select', $this->api_select);
        $this->ViewObj->left_delimiter = '<!--{';
        $this->ViewObj->right_delimiter = '}-->';
        $this->ViewObj->display('Admin_Aff_Rakuten_Retweet.tpl');
    }

    //ajax アカウント選択
    public function ajax_account_select()
    {
        if(!$this->isAjax()){
            echo 'no ajax';
            exit();
        }

        $request = $this->RequestObj;
        $aff_rakuten_account_id = $request->aff_rakuten_account_id;
        $this->Session['aff_rakuten_account_id'] = $aff_rakuten_account_id;
        $this->aff_rakuten_account_info = $this->get_RakutenAccountInfo($aff_rakuten_account_id);
        $this->Session['aff_rakuten_account_name'] = $this->aff_rakuten_account_info->account_name_mb;
        $this->Session['aff_retweet_reserve_info'] = $this->get_RetweetReserveInfo($aff_rakuten_account_id);

        $this->setSession();
        header('Content-Type: application/json');
        echo json_encode($this->Session);
    }

    //リツイート予約削除
    public function ajax_reserve_delete()
    {
        if(!$this->isAjax()){
            echo 'no ajax';
            exit();
        }
        $request = $this->RequestObj;
        $reserve_id = $request->reserve_id;
        $this->Session['result'] = null;
        $this->Session['result_message'] = null;

        try{
            $this->deleteReserveRetweet($reserve_id);
        }catch(Exception $e){
            $this->Session['result'] = 'Error';
            $this->Session['result_message'] = $e->getMessage();
        }

        $this->Session['aff_retweet_reserve_info'] = $this->get_RetweetReserveInfo($this->Session['aff_rakuten_account_id']);
        $this->setSession();

        header('Content-Type: application/json');
        echo json_encode($this->Session);
    }

    //ajax api選択
    public function ajax_api_select()
    {
        if(!$this->isAjax()){
            echo 'no ajax';
            exit();
        }

        $request = $this->RequestObj;
        $search_api = $request->api_select_id;

        $this->Session['search_api'] = $search_api;
        $this->Session['search_api_parms_list'] = $this->request_parms[$search_api];
        $this->Session['search_api_parms'] = array();
        $this->Session['search_item_result'] = array();

        $this->setSession();

        header('Content-Type: application/json');
        echo json_encode($this->Session);
    }

    //ajax 商品検索
    public function ajax_search_items()
    {
        if(!$this->isAjax()){
            echo 'no ajax';
            exit();
        }

        $this->Session['search_api_parms'] = array();
        $this->Session['search_item_result'] = array();

        //検索パラメーター生成
        $request = $this->RequestObj;
        $search_parms = array();
        foreach($this->Session['search_api_parms_list'] as $parm){
            $req_param = $request->$parm['name'];
            if($req_param and strlen($req_param)){
                $search_parms[$parm['name']] = $req_param;
            }
        }
        $this->Session['search_api_parms'] = $search_parms;

        //検索メソッド実行
        $serch_method = 'search_'.$this->api_serch_method[$this->Session['search_api']];
        if(method_exists($this, $serch_method)){
            $this->$serch_method($search_parms);
        }else{
            header('Content-Type: application/json');
            echo 'Error search_api no_set';
            exit();
        }

        $this->setSession();
        header('Content-Type: application/json');
        echo json_encode($this->Session);
    }
    //ページ移動
    public function ajax_move_pages()
    {
        if(!$this->isAjax()){
            echo 'no ajax';
            exit();
        }

        $this->Session['search_item_result'] = array();

        $search_parms = $this->Session['search_api_parms'];
        $request = $this->RequestObj;
        $search_parms['page'] = $request->page;
        //検索メソッド実行
        $serch_method = 'search_'.$this->api_serch_method[$this->Session['search_api']];
        if(method_exists($this, $serch_method)){
            $this->$serch_method($search_parms);
        }else{
            header('Content-Type: application/json');
            echo 'Error search_api no_set';
            exit();
        }

        $this->setSession();
        header('Content-Type: application/json');
        echo json_encode($this->Session);
    }
    //APIレスポンスをSESSIONセットのため配列化
    private function convArr_RakutenApiResponce($responce){
        $search_item_result = array();
        foreach($responce as $item){
            $search_item_result[] = $item;
            // $item_result = array();
            // foreach($item as $sec => $value){
            //     $item_result[$sec] = $value;
            // }
        }
//var_dump($search_item_result);
        return $search_item_result;
    }
    //商品検索------------------------
    private function search_itemSerch($search_parms)
    {
        $rakuten_client = new RakutenRws_Client();
        $rakuten_client->setApplicationId($this->rakuten_apuri_id);
        $rakuten_client->setAffiliateId($this->afferiate_id);

        $search_parms['imageFlag'] = 1;
        $response = $rakuten_client->execute('IchibaItemSearch', $search_parms);
        if ($response->isOk()){
            //$this->Session['search_item_result'] = $response;
            $this->Session['search_item_result'] = $this->convArr_RakutenApiResponce($response);
            $this->Session['search_item_result_max_page'] = $response['pageCount'];
            $this->Session['search_item_result_now_page'] = $response['page'];
        } else {
            header('Content-Type: application/json');
            echo json_encode(array('error' => 'search_error', 'error_message' => $response->getMessage()));
            exit();
        }
    }
    //ランキング検索-----------------------
    private function search_ranking($search_parms)
    {
        $rakuten_client = new RakutenRws_Client();
        $rakuten_client->setApplicationId($this->rakuten_apuri_id);
        $rakuten_client->setAffiliateId($this->afferiate_id);

        $response = $rakuten_client->execute('IchibaItemRanking', $search_parms);
        if ($response->isOk()){
            //$this->Session['search_item_result'] = $response;
            $this->Session['search_item_result'] = $this->convArr_RakutenApiResponce($response);
            $this->Session['search_item_result_max_page'] = $response['pageCount'];
            $this->Session['search_item_result_now_page'] = $response['page'];
        } else {
            header('Content-Type: application/json');
            echo json_encode(array('error' => 'search_error', 'error_message' => $response->getMessage()));
            exit();
        }
    }

    //ajax 商品選択
    public function ajax_item_select()
    {
        if(!$this->isAjax()){
            echo 'no ajax';
            exit();
        }

        $request = $this->RequestObj;
        $this->Session['select_item_index'] = $request->item_index;
        $this->Session['select_img'] = array();
        $this->Session['retweet_comment'] = null;
        $this->Session['retweet_link'] = null;
        $this->Session['retweet_time'] = null;

        $this->setSession();
        header('Content-Type: application/json');
        echo json_encode($this->Session);
    }

    //ajax リツイート予約
    public function ajax_reserve_retweet()
    {
        if(!$this->isAjax()){
            echo 'no ajax';
            exit();
        }

        $request = $this->RequestObj;
        $select_img = $request->select_img;
        foreach($select_img as &$img_url){
            list($img_url, ) = explode('?', $img_url);
        }

        $this->Session['select_img'] = $select_img;
        $this->Session['retweet_comment'] = $request->comment;
        $this->Session['retweet_link'] = $request->affiliateUrl;
        $this->Session['retweet_time'] = $request->retweet_time;
        $this->Session['aff_rakuten_account_id'] = $request->aff_rakuten_account_id;
        $this->Session['aff_retweet_reserve_info'] = $this->get_RetweetReserveInfo($this->Session['aff_rakuten_account_id']);
        $this->aff_rakuten_account_info = $this->get_RakutenAccountInfo($this->Session['aff_rakuten_account_id']);

        //affアカウントがセットされているかチェック
        $this->check_setAffAcount();

        $twObj = new TwitterOAuth(
                $this->aff_rakuten_account_info->tw_consumer_key,
                $this->aff_rakuten_account_info->tw_consumer_secret,
                $this->aff_rakuten_account_info->tw_access_token,
                $this->aff_rakuten_account_info->tw_access_token_secret
            );

        //画像セット
        $media_ids = array();
        foreach($this->Session['select_img'] as $select_img_url){
            $mes = '画像アップロード URI:'.$select_img_url."\n";
            error_log($mes, 3, _TWITTER_LOG_PATH.$this->logFile);
            if($media_id = $this->Media_Upload($twObj, $select_img_url)){
                $media_ids[] = $media_id;
            }
        }

        //ショートURL生成
        $short_url = $this->make_short_url($this->Session['retweet_link']);

        //ツイート処理
        $statusesUpdateObj = new statuses_update($twObj);
        $option = array(
                            "status" => $this->Session['retweet_comment'].$short_url,
                            "possibly_sensitive" => false,
                            "trim_user" => true,
                    );
        if(count($media_ids)){
            $option["media_ids"] = implode(',', $media_ids);
        }
//var_dump($option);
        $api_res = $statusesUpdateObj->setOption($option)->Request(); //テスト用
        $apiErrorObj = new Api_Error($api_res);
        if($apiErrorObj->error){
            $this->Session['result'] = 'Error';
            $this->Session['result_message'] = $apiErrorObj->errorMes_Str;
            $this->Session['tweet_id'] = '';
            $this->Session['reserve_id'] = null;
            $this->setSession();
            header('Content-Type: application/json');
            echo json_encode($this->Session);
            exit();
        }

        $item_info = $this->Session['search_item_result'][$this->Session['select_item_index']];
        $res = $this->setReserveRetweet(
                $api_res->id_str,
                $item_info['itemName'],
                $item_info['shopName']
            );
        if($res){
            $this->Session['result'] = 'Success';
            $this->Session['result_message'] = '正常にツイートできました';
            $this->Session['tweet_id'] = $api_res->id_str;
            $this->Session['reserve_id'] = $res;
            $this->setSession();
            header('Content-Type: application/json');
            echo json_encode($this->Session);
            exit();
        }else{
            $this->Session['result'] = 'Error';
            $this->Session['result_message'] = '予約テーブル挿入エラー';
            $this->Session['tweet_id'] = $api_res->id_str;
            $this->Session['reserve_id'] = null;
            $this->setSession();
            header('Content-Type: application/json');
            echo json_encode($this->Session);
            exit();
        }
    }

    private function check_setAffAcount()
    {
        if(is_null($this->aff_rakuten_account_info)){
            header('Content-Type: application/json');
            echo 'ERROR no_set_account';
            exit();
        }
    }

    private function setReserveRetweet($tweet_id, $item_name, $shop_name)
    {
        $reserve_fields = array(
                'aff_api'               => 'rakuten',
                'aff_api_account_id'    => $this->aff_rakuten_account_info->id,
                'rtw_account_id'        => $this->aff_rakuten_account_info->rtw_account_id,
                'tweet_id' => $tweet_id,
                'retweet_datetime' => $this->Session['retweet_time'],
                'retweeted_flg' => 0,
                'reserve_item_name_mb' => $item_name,
                'reserve_item_shop_name_mb' => $shop_name,
                'del_flg' => 0
            );
        //新規
        // if(is_null($id)){
            $sql = "INSERT INTO aff_retweet_reserve
                    (".implode(", ", array_keys($reserve_fields)).", create_date)
                    VALUES (".implode(", ", array_fill(0, count($reserve_fields), '?')).", now())";
            $res = $this->DBobj->execute($sql, $reserve_fields);
        // //更新
        // }else{
        //     $fields = array();
        //     foreach($reserve_fields as $name => $value){
        //         $fields[] = $name." = ?";
        //     }
        //     $sql = "UPDATE aff_retweet_reserve SET ".implode(", ", $fields)." WHERE id = ?";
        //     array_push($reserve_fields, $id);
        //     $res = $this->DBobj->execute($sql, $reserve_fields);
        // }

        //reserve_id返却
        return $this->DBobj->getlastInsertId();
    }

    private function deleteReserveRetweet($id)
    {
        $sql = "SELECT *
                FROM aff_retweet_reserve
                WHERE id = ?";
        $res = $this->DBobj->query($sql, array($id));
        $aff_account_id = $res[0]->aff_api_account_id;
        $del_tweet_id = $res[0]->tweet_id;

        $sql = "UPDATE aff_retweet_reserve SET del_flg = 1 WHERE id = ?";
        $res = $this->DBobj->execute($sql, array($id));

        if($aff_account_id){
            $aff_account_info = $this->get_RakutenAccountInfo($aff_account_id);
            $twObj = new TwitterOAuth(
                    $aff_account_info->tw_consumer_key,
                    $aff_account_info->tw_consumer_secret,
                    $aff_account_info->tw_access_token,
                    $aff_account_info->tw_access_token_secret
                );
//var_dump($aff_account_info);
//var_dump($del_tweet_id);
            $deltweetObj = new statuses_destroy($twObj);
            $apires = $deltweetObj->setTweetId($del_tweet_id)->Request();
            $apiErrorObj = new Api_Error($apires);
            if($apiErrorObj->error){
                $error_msg = $apiErrorObj->errorMes_Str;
                $mes = 'ツイート削除失敗: '.$error_msg."\n";
                throw new Exception($mes);
            }
        }

        return $res;
    }

    private function Media_Upload($twObj, $retweet_img_url)
    {
        // $media_data = base64_encode(file_get_contents($retweet_img_url));
        // if(!$media_data){
        //     return false;
        // }
        $mediaUploadObj = new media_upload($twObj);
        $api_res = $mediaUploadObj->setMedia($retweet_img_url)->Request();
        $apiErrorObj = new Api_Error($api_res);
        if($apiErrorObj->error){
            $mes = $apiErrorObj->errorMes_Str;
            header('Content-Type: application/json');
            echo $mes;
            exit();
        }

        $media_id = $mediaUploadObj->getMediaId();
        return $media_id;
    }

    private function getTwAccountInfo($twitter_account_id)
    {
        $AccountObj = new MS_Account();
        return $AccountObj->getAccountById($twitter_account_id);
    }

    private function get_RakutenAccountInfo($aff_rakuten_account_id)
    {
        $sql = "SELECT *
                FROM aff_rakuten_account
                WHERE use_flg = 1 AND id = ?";
        $res = $this->DBobj->query($sql, array($aff_rakuten_account_id));
        return $res[0];
    }

    private function get_ALLRakutenAccountInfo()
    {
        $sql = "SELECT *
                FROM aff_rakuten_account
                WHERE use_flg = 1";
        $res = $this->DBobj->query($sql);
        return $res;
    }

    private function get_RetweetReserveInfo($aff_rakuten_account_id)
    {
        $sql = "SELECT *
                FROM aff_retweet_reserve
                WHERE del_flg = 0
                    AND retweeted_flg = 0
                    AND aff_api = 'rakuten'
                    AND aff_api_account_id = ?
                ORDER BY retweet_datetime ASC";
        $res = $this->DBobj->query($sql, array($aff_rakuten_account_id));
        if($res){
            return $res;
        }else{
            return null;
        }
    }

    private function make_short_url($redirect_url)
    {
        $ShortUrl_obj = new ShortUrl();
        $short_url = $ShortUrl_obj->make_short_url($redirect_url);
        return $short_url;
    }

    private function isAjax()
    {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) and (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')){
            return true;
        }else{
            return false;
        }
    }



}

