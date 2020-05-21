<?php


namespace Vod\Model;


class VodUploadHttpProfile
{
    public $Proxy;

    function __construct($proxy = '')
    {
        $this->Proxy = $proxy;
    }
}