<?php
require_once(_TWITTER_CLASS_PATH."Api_Error.php");
require_once(_TWITTER_CLASS_PATH."RSS_Data_Container.php");
require_once(_TWITTER_CLASS_PATH."Image.php");
//require_once(_RSS_FEED_PATH."Feed.php");

class Cron_Rss_GetSource_Logic
{

    // 入力情報
    //処理を実行するアカウントID
    private $RSS_Account_ID;

    // DBより取得するパラメーター
    //アカウント情報
    private $RSS_AccountInfo = null;

    //RSS
    private $Source = null;
    //RSSコンテナ配列
    private $RSS_Cont_Arr = array();
    //DBオブジェクト
    private $DBobj;
    //ログ
    private $logFile;

    //デバッグ用
    private $debug_flg = false;

    public function __construct()
    {
        $this->DBobj = new DB_Base();
        $this->logFile = 'rss_log_'.date("Y_m_d").".log";
    }

    public function setDebug()
    {
        $this->debug_flg = true;
    }

    //アカウントset
    public function setAccountId($id)
    {
        $this->RSS_Account_ID = $id;
        //RSSソースリセット
        $this->Source = null;

        //$FeedObj = new Feed();

        $RSS_AccountObj = new RSS_Account();
        $this->RSS_AccountInfo = $RSS_AccountObj->getAccountById($id);

        //RSSフィード取得
        // if($this->RSS_AccountInfo->feed_type === 'RSS2'){
        //     $this->Source = $FeedObj->loadRss($this->RSS_AccountInfo->rssfeed_url);
        // }elseif($this->RSS_AccountInfo->feed_type === 'Atom'){
        //     $this->Source = $FeedObj->loadAtom($this->RSS_AccountInfo->rssfeed_url);
        // }elseif($this->RSS_AccountInfo->feed_type === 'RSS1'){
        //     //$this->Source = simplexml_load_string(file_get_contents($this->RSS_AccountInfo->rssfeed_url));
        //     $this->Source = simplexml_load_file($this->RSS_AccountInfo->rssfeed_url);
        // }
        // else{
        //     $msg = 'RSS_TYPEの設定が不正です';
        //     throw new Exception($msg);
        // }
        $this->Source = simplexml_load_file($this->RSS_AccountInfo->rssfeed_url);
        if($this->Source === false){
            $msg = 'RSSフィードの取得に失敗しました';
            throw new Exception($msg);
        }
    }

    public function test_outputFeed()
    {
        //$res = array();
        //echo json_encode($this->Source);
        var_dump($this->RSS_Cont_Arr);
        // foreach($this->Source->item as $feed_data){
        //     //echo json_encode((string)$feed_data->children('http://purl.org/rss/1.0/modules/content/')->encoded)."<br><br>";
        //     echo $feed_data->title;
        //     var_dump((string)$feed_data->children('http://purl.org/rss/1.0/modules/content/')->encoded);
        // }
    }

    //画像をリサイズして_IMAGE_PATH以下に保存
    private function makeImage($image_url, $image_date)
    {
        $make_image_size = array();
        $make_image_size[] = array(
                                    "width" => 200,
                                    "hight" => 200
                                    );
        $image_file_urls = array();
        // 拡張子を取得
        $path_parts = pathinfo($image_url);
        $image_extension = $path_parts['extension'];

        $image_path = _IMAGE_PATH.$this->RSS_AccountInfo->name;

        if(!file_exists($image_path)){
            mkdir($image_path, 0777);
        }

        $imageObj = new Image();
        if(!$imageObj->setImage($image_url)){
            //画像取得失敗
            return array();
        }
        foreach($make_image_size as $image_size){
            $image_file_name = $this->RSS_AccountInfo->name."_".date("ymd_His", strtotime($image_date))."_".$image_size['width']."x".$image_size['hight'].".".$image_extension;
            $output_image_path = $image_path."/".$image_file_name;
            $ret = $imageObj->resizeImage($image_size['width'], $image_size['hight'])
                     ->output_ImageResource($output_image_path);
            if($ret){
                $image_file_urls[] = _IMAGE_URL.$this->RSS_AccountInfo->name."/".$image_file_name;
            }
        }
        return $image_file_urls;
    }

    public function analysis_oretekigame()
    {
        $this->RSS_Cont_Arr = array();
        $get_count = 0;
        foreach($this->Source->item as $feed_data){
            $RSS_cont_obj = new RSS_Data_Container();
            $RSS_cont_obj->rss_account_id = $this->RSS_Account_ID;
            $RSS_cont_obj->date = date("Y-m-d H:i:s", strtotime((string)$feed_data->children('http://purl.org/dc/elements/1.1/')->date));
            $RSS_cont_obj->title = (string)$feed_data->title;
            $RSS_cont_obj->link_url = (string)$feed_data->link;
            $RSS_cont_obj->html_content = (string)$feed_data->children('http://purl.org/rss/1.0/modules/content/')->encoded;
            $RSS_cont_obj->subject = (string)$feed_data->children('http://purl.org/dc/elements/1.1/')->subject;
            $RSS_cont_obj->del_flg = 0;

            if($RSS_cont_obj->checkDB_RssData()){
                //既に保存済みの記事でないとき
                //画像取得
                if(preg_match('/<img.*src\s*=\s*[\"|\'](.*?\.(?:jpg|jpeg|png|gif))[\"|\'].*>/i', $RSS_cont_obj->html_content, $m)){
                    $image_file_urls = $this->makeImage($m[1], $RSS_cont_obj->date);
                    if(count($image_file_urls)){
                        $RSS_cont_obj->image_url = $image_file_urls[0];
                    }
                }
                //DBセット
                $RSS_cont_obj->setDB();

                $get_count++;
            }
            //デバッグ用
            if($this->debug_flg){
                $this->RSS_Cont_Arr[] = $RSS_cont_obj;
            }
            unset($RSS_cont_obj);
        }
        $mes = "取得件数 ".$get_count."件\n";
        error_log($mes, 3, _RSS_LOG_PATH.$this->logFile);
    }

    public function analysis_4games_topics()
    {
        $this->RSS_Cont_Arr = array();
        $get_count = 0;
        foreach($this->Source->item as $feed_data){
            $RSS_cont_obj = new RSS_Data_Container();
            $RSS_cont_obj->rss_account_id = $this->RSS_Account_ID;
            $RSS_cont_obj->date = date("Y-m-d H:i:s", strtotime((string)$feed_data->children('http://purl.org/dc/elements/1.1/')->date));
            $RSS_cont_obj->title = (string)$feed_data->title;
            $RSS_cont_obj->link_url = (string)$feed_data->link;
            $RSS_cont_obj->content = (string)$feed_data->description;
            $RSS_cont_obj->del_flg = 0;

            if($RSS_cont_obj->checkDB_RssData()){
                //既に保存済みの記事でないとき
                //DBセット
                $RSS_cont_obj->setDB();

                $get_count++;
            }
            //デバッグ用
            if($this->debug_flg){
                $this->RSS_Cont_Arr[] = $RSS_cont_obj;
            }
            unset($RSS_cont_obj);
        }
        $mes = "取得件数 ".$get_count."件\n";
        error_log($mes, 3, _RSS_LOG_PATH.$this->logFile);
    }


    public function analysis_dengekionline()
    {
var_dump($this->Source);
        // $this->RSS_Cont_Arr = array();
        // $get_count = 0;
        // foreach($this->Source->item as $feed_data){
        //     $RSS_cont_obj = new RSS_Data_Container();
        //     $RSS_cont_obj->rss_account_id = $this->RSS_Account_ID;
        //     $RSS_cont_obj->date = date("Y-m-d H:i:s", strtotime((string)$feed_data->children('http://purl.org/dc/elements/1.1/')->date));
        //     $RSS_cont_obj->title = (string)$feed_data->title;
        //     $RSS_cont_obj->link_url = (string)$feed_data->link;
        //     $RSS_cont_obj->html_content = (string)$feed_data->children('http://purl.org/rss/1.0/modules/content/')->encoded;
        //     $RSS_cont_obj->subject = (string)$feed_data->children('http://purl.org/dc/elements/1.1/')->subject;
        //     $RSS_cont_obj->del_flg = 0;

        //     if($RSS_cont_obj->checkDB_RssData()){
        //         //既に保存済みの記事でないとき
        //         //画像取得
        //         if(preg_match('/<img.*src\s*=\s*[\"|\'](.*?\.(?:jpg|jpeg|png|gif))[\"|\'].*>/i', $RSS_cont_obj->html_content, $m)){
        //             $image_file_urls = $this->makeImage($m[1], $RSS_cont_obj->date);
        //             if(count($image_file_urls)){
        //                 $RSS_cont_obj->image_url = $image_file_urls[0];
        //             }
        //         }
        //         //DBセット
        //         $RSS_cont_obj->setDB();

        //         $get_count++;
        //     }
        //     //デバッグ用
        //     if($this->debug_flg){
        //         $this->RSS_Cont_Arr[] = $RSS_cont_obj;
        //     }
        //     unset($RSS_cont_obj);
        // }
        // $mes = "取得件数 ".$get_count."件\n";
        // error_log($mes, 3, _RSS_LOG_PATH.$this->logFile);
    }



}


