<?php
//viewクラス
require_once(_TWITTER_SMARTY_PATH."Smarty.class.php");

class View extends Smarty
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplateDir(_TWITTER_TEMPLATE_PATH);
        $this->setConfigDir(_TWITTER_SMARTY_PATH."config/");
        $this->setPluginsDir(_TWITTER_SMARTY_PATH."plugins/");
        $this->setCompileDir(_TWITTER_SMARTY_PATH."templates_c/");
        $this->setCacheDir(_TWITTER_SMARTY_PATH."cache/");
    }
}




