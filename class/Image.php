<?php


class Image
{

    private $File_Path = null;
    private $Extension = null;
    private $ImageResource = null;
    private $OutImageResource = null;

    public function __construct($file_path)
    {
        $this->File_Path = $file_path;
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

        switch($this->Extension) {
            case 'jpg':
            case 'jpeg':
                imagejpeg($this->OutImageResource, $output_file_path);
                break;

            case 'png':
                imagepng($this->OutImageResource, $output_file_path);
                break;

            case 'gif':
                imagegif($this->OutImageResource, $output_file_path);
                break;
        }
    }

    public function resizeImage($width, $hight)
    {

        if(!$this->set_ImageResource()){
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

