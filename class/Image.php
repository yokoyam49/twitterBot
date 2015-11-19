<?php
/***************************
画像リサイズクラス

$imageObj = new Image();
$imageObj->setImage(元画像URL);
$imageObj->resizeImage(200, 200)
         ->output_ImageResource(保存先[省略でその場で出力]);
***************************/
class Image
{

    private $File_Path = null;
    private $Extension = null;
    private $ImageResource = null;
    private $OutImageResource = null;

    public function __construct()
    {

    }

    public function setImage($file_path)
    {
        $this->File_Path = $file_path;
        $this->Extension = null;
        $this->ImageResource = null;
        $this->OutImageResource = null;

        if(!$this->set_ImageResource()){
            return false;
        }else{
            return true;
        }
    }

    private function set_ImageResource()
    {
        // 拡張子を取得
        $path_parts = pathinfo($this->File_Path);
        $this->Extension = $path_parts['extension'];

        $image_resource = null;
        switch($this->Extension) {
            case 'jpg':
            case 'jpeg':
                $image_resource = imagecreatefromjpeg($this->File_Path);
                break;

            case 'png':
                $image_resource = imagecreatefrompng($this->File_Path);
                break;

            case 'gif':
                $image_resource = imagecreatefromgif($this->File_Path);
                break;
        }
        if(!$image_resource){
            $this->ImageResource = null;
            return false;
        }else{
            $this->ImageResource = $image_resource;
            return true;
        }
    }

    public function output_ImageResource($output_file_path=null)
    {
        if(!$this->OutImageResource){
            return false;
        }

        if(!is_null($output_file_path)){
            //出力予定ファイル名より拡張子を取得
            $path_parts = pathinfo($this->File_Path);
            $extension = $path_parts['extension'];
        }else{
            //元画像ファイルと同じ拡張子を使用
            $extension = $this->Extension;
        }

        if(!$extension){
            $extension = 'jpg';
        }

        switch($extension) {
            case 'jpg':
            case 'jpeg':
                $ret = imagejpeg($this->OutImageResource, $output_file_path);
                break;

            case 'png':
                $ret = imagepng($this->OutImageResource, $output_file_path);
                break;

            case 'gif':
                $ret = imagegif($this->OutImageResource, $output_file_path);
                break;
        }
        return $ret;
    }

    public function resizeImage($width, $hight)
    {

        if(!$this->ImageResource){
            $this->OutImageResource = null;
            return $this;
        }

        $from_width = imagesx($this->ImageResource);
        $from_hight = imagesy($this->ImageResource);
        $from_x_y_ratio = $from_width / $from_hight;
        $to_x_y_ratio = $width / $hight;

        //縦幅を基準に左右をトリミング
        if($from_x_y_ratio >= $to_x_y_ratio){
            $from_x = floor(($from_width - ($from_hight * $to_x_y_ratio)) / 2);
            $from_y = 0;
            $to_width = floor($from_hight * $to_x_y_ratio);
            $to_hight = $from_hight;
        }
        //横幅を基準に上下をトリミング
        else{
            $from_x = 0;
            $from_y = floor(($from_hight - ($from_hight / $to_x_y_ratio)) / 2);
            $to_width = $from_width;
            $to_hight = floor($from_width * $to_x_y_ratio);
        }


        $out = imagecreatetruecolor($width, $hight);
        //ブレンドモードを無効にする
        imagealphablending($out, false);
        //完全なアルファチャネル情報を保存するフラグをonにする
        imagesavealpha($out, true);

        $ret = imagecopyresampled($out, $this->ImageResource, 0, 0, $from_x, $from_y, $width, $hight, $to_width, $to_hight);
        if($ret){
            $this->OutImageResource = $out;
        }else{
            $this->OutImageResource = null;
        }

        return $this;
    }


}

