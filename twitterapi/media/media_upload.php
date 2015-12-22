<?php

class media_upload
{
    private $twObj;
    private $ApiUrl = 'media/upload';

    private $Response = null;
    private $Options = array();

    private $media_data = null;
    private $media = null;

    public function __construct($twObj)
    {
        $this->twObj = $twObj;
    }

    public function setMedia($media)
    {
        $this->media = $media;
        return $this;
    }

    public function setMediaData($media_data)
    {
        $this->media_data = $media_data;
        return $this;
    }

    public function setOption($options)
    {
        $this->Options = $options;
        return $this;
    }

    public function getResponse()
    {
        return $this->Response;
    }

    //実行
    public function Request()
    {
        if(!count($this->Options)){
            if(!is_null($this->media)){
                $this->Options = array(
                        'media' => $this->media
                    );
            }elseif(!is_null($this->media_data)){
                $this->Options = array(
                        'media_data' => $this->media_data
                    );
            }
        }
        $res = $this->twObj->upload(
            $this->ApiUrl,
            $this->Options
        );

        $this->Response = $res;

        return $this->Response;
    }

    public function getMediaId()
    {
        return $this->Response->media_id_string;
    }

	public function resetLastResponse()
	{
		$this->twObj->resetLastResponse();
	}

}


