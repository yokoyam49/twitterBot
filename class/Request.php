<?php

class Request
{
    private $RequestArr = array();

    public function __construct($type='REQUEST')
    {
        if($type === 'POST'){
            $this->RequestArr = $_POST;
        }elseif($type === 'GET'){
            $this->RequestArr = $_GET;
        }else{
            $this->RequestArr = $_REQUEST;
        }
    }

    public function __get($name)
    {
        if(isset($this->RequestArr[$name]) and strlen($this->RequestArr[$name])){
            return $this->RequestArr[$name];
        }
        return false;
    }

}
