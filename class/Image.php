<?php


class Image
{

    private $File_Path = null;
    private $Extension = null;
    private $ImageResource = null;

    public function __construct($file_path)
    {
        $this->File_Path = $file_path;
    }

    private function set_ImageResource()
    {
        // 拡張子を取得
        $path_parts = pathinfo($this->File_Path);
        $this->Extension = $path_parts['extension'];

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
        $this->ImageResource = $image_resource;
    }

    private function output_ImageResource($output_file_path, $out_resource)
    {
        switch($this->Extension) {
            case 'jpg':
            case 'jpeg':
                imagejpeg($out_resource, $output_file_path);
                break;

            case 'png':
                imagepng($out_resource, $output_file_path);
                break;

            case 'gif':
                imagegif($out_resource, $output_file_path);
                break;
        }
    }

    public function resizeImage($output_file_path, $width, $hight)
    {
        if(!file_exists($this->File_Path)){
            return false;
        }
        $this->set_ImageResource();

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
        //$ret = imagecopyresampled($out, $this->ImageResource, 0, 0, 0, 0, $x, $y, $to_x, $to_y);
var_dump(array($from_x, $from_y, $width, $hight, $to_width, $to_hight));
        $ret = imagecopyresampled($out, $this->ImageResource, 0, 0, $from_x, $from_y, $width, $hight, $to_width, $to_hight);
        if($ret){
            //ファイル出力
            $this->output_ImageResource($output_file_path, $out);
        }
        return $ret;
    }


}

