<?php
//アクセス振り分けクラス
class Dispatcher
{
    public function dispatch()
    {
        $basePath = dirname($_SERVER['PHP_SELF']);//admin
        $uri = trim(str_replace($basePath, '', $_SERVER['REDIRECT_URL']), '/');

        $parms = explode("/", $uri);

        //クラス名取得

        if(!isset($parms[0])){
            $this->dispNotFound();
        }

        $class_name = ucfirst(trim($basePath, '/'))."_".ucfirst($parms[0]);
        if(!file_exists(_TWITTER_CLASS_PATH.$class_name.".php")){
            $this->dispNotFound();
        }

        require_once(_TWITTER_CLASS_PATH.$class_name.".php");

        if(!class_exists($class_name)){
            $this->dispNotFound();
        }

        $ClassObj = new $class_name();

        //アクション名取得

        if(!isset($parms[1]) or !strlen($parms[1])){
            $method_name = 'index';
        }
        else{
            $method_name = $parms[1];
        }

        //さらにパラメーターがあれば、setParmsメソッドで追加
        if(isset($parms[2]) and strlen($parms[2])){
            array_shift($parms);//$parms[0]$parms[1]削除
            array_shift($parms);
            if(method_exists($ClassObj, 'setParms')){
                $ClassObj->setParms($parms);
            }
        }

        //アクション実行

        if(!method_exists($ClassObj, $method_name)){
            $this->dispNotFound();
        }

        $ClassObj->$method_name();
    }

    private function dispNotFound()
    {
        header("HTTP/2.0 404 Not Found");
        print(file_get_contents(_DOCUMENT_PATH.'404.html'));
        exit;
    }
}

