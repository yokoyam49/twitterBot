<?php
session_start();

require_once(_TWITTER_CLASS_PATH."DB_Base.php");
require_once(_TWITTER_CLASS_PATH."MS_Account.php");
require_once(_TWITTER_CLASS_PATH."Request.php");
require_once(_TWITTER_CLASS_PATH."View.php");
require_once(_RAKUTEN_SDK_PATH."autoload.php");
require_once(_TWITTER_API_PATH."statuses/statuses_update.php");
require_once(_TWITTER_CLASS_PATH."Api_Error.php");

class Admin_Aff_Rakuten_Retweet
{
    //アプリID
    private $rakuten_apuri_id = '1055279728800101163';
    //アフェリエイトID
    private $afferiate_id = '149211ea.72c5d66e.149211eb.93a8db3b';

    private $ViewObj;
    private $RequestObj;
    private $twObj;
    private $logFile;

    private $Session = array(
                    'aff_rakuten_account_info' => array(),
                    'aff_retweet_reserve_info' => array(),
                    'search_api' => null,//0->商品検索 1->ランキング
                    'search_api_parms' => array(),
                    'search_item_result' => array(),
                    'select_item' => null,
                    'retweet_img' => array(),
                    'retweet_comment' => null,
                    'retweet_time' => null,
            );

    private $api_select = array(
                    0 => '楽天市場商品検索API',
                    1 => '楽天市場ランキングAPI'
            );

    public function __construct()
    {
        $this->ViewObj = new View();
        $this->RequestObj = new Request();

        $this->Session = $_SESSION;
    }

    private function setSession()
    {
        unset($_SESSION);
        $_SESSION = $this->Session;
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
        $this->Session['aff_rakuten_account_info'] = $this->get_RakutenAccountInfo($aff_rakuten_account_id);
        $this->Session['aff_retweet_reserve_info'] = $this->get_RetweetReserveInfo($aff_rakuten_account_id);

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
        $search_api = $request->search_api;
        $this->Session['search_api'] = $search_api;
        $this->Session['search_api_parms'] = array();
        $this->Session['search_item_result'] = array();

        $this->setSession();
        header('Content-Type: application/json');
        echo json_encode($this->Session);
    }

    //ajax 商品検索
    public function ajax_search_items()
    {
        $request_parms = array(
                'keyword',
                'shopCode',
                'itemCode',
                'genreId',
                'tagId',
                'page',
                'sort',
                'carrier',
                'field',
                'NGKeyword',
                'minAffiliateRate'
            );

        if(!$this->isAjax()){
            echo 'no ajax';
            exit();
        }

        $this->Session['search_api_parms'] = array();
        $this->Session['search_item_result'] = array();

        $request = $this->RequestObj;
        $search_parms = array();
        foreach($request_parms as $parm){
            if(isset($request->$parm) and strlen($request->$parm)){
                $search_parms[$parm] = $request->$parm;
            }
            $search_parms['imageFlag'] = 1;
        }
        $this->Session['search_api_parms'] = $search_parms;

        $rakuten_client = new RakutenRws_Client();
        $rakuten_client->setApplicationId($this->rakuten_apuri_id);
        $rakuten_client->setAffiliateId($this->afferiate_id);

        $response = $client->execute('IchibaItemSearch', $search_parms);
        if ($response->isOk()){
            $this->Session['search_item_result'] = $response;
        } else {
            header('Content-Type: application/json');
            echo 'search_error '.$response->getMessage();
            exit();
        }

        $this->setSession();
        header('Content-Type: application/json');
        echo json_encode($this->Session);
    }

    //ajax 商品選択
    public function ajax_item_select()
    {
        if(!$this->isAjax()){
            echo 'no ajax';
            exit();
        }

        $request = $this->RequestObj;
        $this->Session['select_item'] = $request->select_item;
        $this->Session['retweet_img'] = array();
        $this->Session['retweet_comment'] = null;
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
        $this->Session['retweet_img'] = $request->retweet_img;
        $this->Session['retweet_comment'] = $request->retweet_comment;
        $this->Session['retweet_time'] = $request->retweet_time;

        $twitter_account = getTwAccountInfo($this->Session['aff_rakuten_account_info']->tw_account_id);
        $twObj = new TwitterOAuth(
                $twitter_account->consumer_key,
                $twitter_account->consumer_secret,
                $twitter_account->access_token,
                $twitter_account->access_token_secret
            );

        //画像セット
        $media_ids = array();
        foreach($this->Session['retweet_img'] as $retweet_img_url){
            if($media_id = $this->Media_Upload($twObj)){
                $media_ids[] = $media_id;
            }
        }

        //ツイート処理
        $statusesUpdateObj = new statuses_update($twObj);
        $option = array(
                            "status" => $this->Session['retweet_comment'],
                            "possibly_sensitive" => false,
                            "trim_user" => true,
                    );
        if(count($media_ids)){
            $option["media_ids"] = implode(',', $media_ids);
        }
        $api_res = $statusesUpdateObj->setOption($option)->Request();
        $apiErrorObj = new Api_Error($api_res);
        if($apiErrorObj->error){
            $mes = $apiErrorObj->errorMes_Str;
            header('Content-Type: application/json');
            echo $mes;
            exit();
        }

        //リツイート予約処理
        $reserve_id = null;
        if(!is_null($this->Session['aff_retweet_reserve_info'])){
            $reserve_id = $this->Session['aff_retweet_reserve_info']->id;
        }
        $item_info = $this->Session['search_item_result'][$this->Session['select_item']];
        $res = $this->setReserveRetweet(
                $apires->id_str,
                $item_info['itemName'],
                $item_info['shopName'],
                $reserve_id
            );
        if($res){
            header('Content-Type: application/json');
            echo 'success';
        }else{
            header('Content-Type: application/json');
            echo 'Error setDB retweet_reserve';
        }
    }

    private function setReserveRetweet($tweet_id, $item_name, $shop_name, $id = null)
    {
        $reserve_fields = array(
                'aff_api' => 'rakuten',
                'aff_api_account_id' => $this->Session['aff_rakuten_account_info']->id,
                'rtw_account_id' => $this->Session['aff_rakuten_account_info']->rtw_account_id,
                'tweet_id' => $tweet_id,
                'retweet_datetime' => $this->Session['retweet_time'],
                'retweeted_flg' => 0,
                'reserve_item_name_mb' => $item_name,
                'reserve_item_shop_name_mb' => $shop_name,
                'del_flg' => 0
            );
        //新規
        if(is_null($id)){
            $sql = "INSERT INTO aff_retweet_reserve
                    (".implode(", ", array_keys($reserve_fields)).", create_date)
                    VALUES (".implode(", ", array_fill(0, count($reserve_fields), '?')).", now())";
            $res = $this->DBobj->execute($sql, $reserve_fields);
        //更新
        }else{
            $fields = array();
            foreach($reserve_fields as $name => $value){
                $fields[] = $name." = ?";
            }
            $sql = "UPDATE aff_retweet_reserve SET ".implode(", ", $fields)." WHERE id = ?";
            array_push($fields, $id);
            $res = $this->DBobj->execute($sql, $fields);
        }
        return $res;
    }

    private function Media_Upload($twObj, $retweet_img_url)
    {
        $media_data = file_get_contents($retweet_img_url);
        if(!$media_data){
            return false;
        }
        $mediaUploadObj = new media_upload($twObj);
        $mediaUploadObj->setMedia($media_data)->Request();
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
                FROM aff_rakuten
                WHERE id = ?";
        $res = $this->DBobj->query($sql, array($aff_rakuten_account_id));
        return $res[0];
    }

    private function get_RetweetReserveInfo($aff_rakuten_account_id)
    {
        $sql = "SELECT *
                FROM aff_retweet_reserve
                WHERE del_flg = 0
                    AND aff_api = 'rakuten'
                    AND aff_api_account_id = ?";
        $res = $this->DBobj->query($sql, array($aff_rakuten_account_id));
        if(isset($res[0])){
            return $res[0];
        }else{
            return null;
        }
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

