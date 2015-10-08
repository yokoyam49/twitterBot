<?php
require_once(_TWITTER_CLASS_PATH."Request.php");
require_once(_TWITTER_CLASS_PATH."View.php");
require_once(_TWITTER_CLASS_PATH."DT_Message.php");


class Admin_Checkmessage
{
    private $ViewObj;
    private $MessageObj;
    private $RequestObj;

    private $parms;

    //固定設定
    private $display_num = 20;//一ページに表示する件数
    private $pagenation_num = 5;//ページネーションに何ページ分ボタンを表示させるか

    public function __construct()
    {
        $this->ViewObj = new View();
        $this->MessageObj = new DT_Message();
        $this->RequestObj = new Request();
    }

    //追加パラメーターがあれば取得
    public function setParms($parms)
    {
        $this->parms = $parms;
    }

    public function index()
    {
        $request = $this->RequestObj;

        //アカウントID
        if($request->account_id){
            $Account_ID = $request->account_id;
        }else{
            $Account_ID = null;
        }

        // ページ処理
        if(isset($this->parms[0])){
            $page = $this->parms[0];//uri渡し
        }else{
            $page = $request->page;//requestパラメーター渡し
        }

        if(!$page or !is_numeric($page)){
            $page = 0;
        }else{
            $page = $page - 1;
        }
        $limit = $this->display_num;
        $offset = $this->display_num * $page;

        //確認済みも表示チェック
        // if($request->checked_disp){
        //     $checkedDisp = true;
        // }else{
        //     $checkedDisp = false;
        // }
        $checkedDisp = true;

        //ページネーション用
        $mes_count = $this->MessageObj->getMessageCount($checkedDisp, $Account_ID);
        $page_count = (int)ceil($mes_count / $this->display_num);
        $min_page = ($page - floor($this->pagenation_num / 2)) > 0 ? (int)($page - floor($this->pagenation_num / 2)) : 1;
        $max_page = ($page + floor($this->pagenation_num / 2)) < $page_count ? (int)($page + floor($this->pagenation_num / 2)) : $page_count;
        $page_buttons = range($min_page, $max_page);
        //リスト取得
        $mes_list = $this->MessageObj->getMessages($checkedDisp, $Account_ID, $limit, $offset);

        $this->ViewObj->assign('pagenation', array('page' => ($page + 1), 'page_count' => $page_count, 'page_buttons' => $page_buttons, 'min_page' => $min_page, 'max_page' => $max_page));
        $this->ViewObj->assign('mes_list', $mes_list);
        $this->ViewObj->display('Admin_Checkmessage.tpl');
    }


}



