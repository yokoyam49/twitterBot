<?php
require_once(_TWITTER_CLASS_PATH."DB_Base.php");

class RSS_Data_Container
{
    private $fields = array(
            'rss_account_id' => 'INT',
            'date' => 'STR',
            'title' => 'STR',
            'content' => 'STR',
            'html_content' => 'STR',
            'link_url' => 'STR',
            'image_url' => 'STR',
            'subject' => 'STR',
            'memo1' => 'STR',
            'memo2' => 'STR',
            'memo3' => 'STR',
            'memo4' => 'STR',
            'memo5' => 'STR',
            'memo6' => 'STR',
            'memo7' => 'STR',
            'memo8' => 'STR',
            'memo9' => 'STR',
            'memo10' => 'STR',
            'del_flg' => 'INT'
        );
    private $data = array();

    private $DBobj;

    public function __construct()
    {
        $this->DBobj = new DB_Base();
    }

    public function __set($name, $value)
    {
        if(isset($this->fields[$name])){
            $this->data[$name] = $value;
        }else{
            trigger_error("RSS_Data_Container: ".$name.":存在しないフィールドに値がセットされました", E_USER_ERROR);
        }
    }

    public function __get($name)
    {
        if(isset($this->fields[$name])){
            if(isset($this->data[$name])){
                return $this->data[$name];
            }else{
                return null;
            }
        }else{
            trigger_error("RSS_Data_Container: ".$name.":存在しないフィールドです", E_USER_ERROR);
        }
    }

    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    public function setDB()
    {
        //dateに正常な値が入っているか
        if(!isset($this->data['date']) or strtotime($this->data['date']) === false or strtotime($this->data['date']) <= 0){
            return false;
        }
        //LOGIC側で実行
        // //既に保存済みの記事ではないかチェック
        // if($this->checkDB_RssData()){
        //     return false;
        // }

        $set_cols = array();
        $pos = array();
        $set_vals = array();
        foreach($this->data as $name => $value){
            if(is_null($value)){
                continue;
            }
            $set_cols[] = $name;
            $pos[] = '?';
            $set_vals[] = $value;
        }

        $mes = "取得記事： ".$this->data['title']." date： ".$this->data['date']."\n";
        error_log($mes, 3, _RSS_LOG_PATH.'rss_log_'.date("Y_m_d").".log");

        $sql = "INSERT INTO rss_feed_date (".implode(", ", $set_cols).", create_date) VALUES (".implode(", ", $pos).", now())";
        $in_count = $this->DBobj->execute($sql, $set_vals);
        if($in_count){
            return true;
        }else{
            return false;
        }
    }

    //DBに同じ記事が保存済みではないかチェック
    //保存済みではない：true   保存済み：false
    public function checkDB_RssData()
    {
        $sql = "SELECT id FROM rss_feed_date WHERE rss_account_id = ? AND date = ?";
        $res = $this->DBobj->query($sql, array($this->data['rss_account_id'], $this->data['date']));

        if(!$res or !count($res)){
            return true;
        }else{
            return false;
        }
    }

}



