<?php

class RSS_Data_Container
{
    private $fields = array(
            'rss_account_id' => 'INT',
            'date' => 'STR',
            'title' => 'STR',
            'content' => 'STR',
            'html_content' => 'STR',
            'link_url' => 'STR',
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
            'memo10' => 'STR'
        );
    private $data = array();

    public function __set($name, $value)
    {
        if(isset($this->fields[$name])){
            $this->data[$name] = $value;
        }else{
            trigger_error("RSS_Data_Container: 存在しないフィールドに値がセットされました", E_USER_ERROR);
        }
    }

}



