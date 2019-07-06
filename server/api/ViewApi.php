<?php

class ViewApi
{
    private $format = '';

    public function __construct()
    {
        header("Access-Control-Allow-Orgin: *");
        header("Access-Control-Allow-Methods: *");
    }

    public function setFormat($format)
    {
        $this->format = substr($format, 1);
        header("Content-Type: application/".$this->format);
    }

    public function requestStatus($code)
    {
        $status = array(
            200 => 'OK',
            401 => 'Unauthorized',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            500 => 'Internal Server Error',
        );
        return ($status[$code]) ? $status[$code] : $status[500];
    }

    public function response($data, $status = 500)
    {
        header("HTTP/1.1 " . $status . " " . $this->requestStatus($status));
        switch ($this->format) {
            case "txt":
                return 'pre'.print_r($data, true).'/pre';
            case "xml":
                return 'pre'.print_r($data, true).'/pre';
            case "html":
                return 'pre'.print_r($data, true).'/pre';
            default:
                return json_encode($data);

        }
    }
}