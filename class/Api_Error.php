<?php

class Api_Error
{
    private $res;
    public $error = false;
    public $errorMes_Str;
    public $errorMes_Arr = array();

    public function __construct($res)
    {
        $this->res = $res;

        if(isset($res->errors)){
            $this->errorMes_Str = '';
            $this->error = true;
            $this->errorMes_Arr = $res->errors;
            foreach($res->errors as $error){
                $this->errorMes_Str .= $error->message."\n";
            }
        }
    }

    public function setApiRes($res)
    {
        $this->res = $res;
    }

    public function getRes(){
        return $res;
    }

}