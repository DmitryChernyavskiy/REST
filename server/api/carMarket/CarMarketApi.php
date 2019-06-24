<?php

abstract class CarMarketApi
{
    protected $method = ''; //GET|POST|PUT|DELETE

    public $requestUri = [];
    public $requestParams = [];

    protected $action = ''; //Название метод для выполнения


    public function __construct()
    {
        header("Access-Control-Allow-Orgin: *");
        header("Access-Control-Allow-Methods: *");
        header("Content-Type: application/json");

        $url = trim($_SERVER['REQUEST_URI'];
        $this->requestUri = explode('/', $url,'/'));
        $this->requestParams = $_REQUEST;

        $this->method = $_SERVER['REQUEST_METHOD'];
        if ($this->method == 'POST' && array_key_exists('HTTP_X_HTTP_METHOD', $_SERVER))
        {
            if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'DELETE')
            {
                $this->method = 'DELETE';
            } else if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'PUT')
            {
                $this->method = 'PUT';
            } else
            {
                throw new Exception("Unexpected Header");
            }
        }
    }

    public function run()
    {
        if(array_shift($this->requestUri) !== 'api' || array_shift($this->requestUri) !== get_class($this))
        {
            throw new RuntimeException('API Not Found', 404);
        }
        if($this->requestUri)
        {
            $this->action = array_shift($this->requestUri);
        }else
        {
            throw new RuntimeException('Invalid Method', 405);
        }
        $this->getAction();
         return $this->{$this->action}();
       
    }

    protected function response($data, $status = 500)
    {
        header("HTTP/1.1 " . $status . " " . $this->requestStatus($status));
        return json_encode($data);
    }

    private function requestStatus($code) {
        $status = array(
            200 => 'OK',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            500 => 'Internal Server Error',
        );
        return ($status[$code])?$status[$code]:$status[500];
    }


    public function listCars();//get
    public function getInfo($idCar, $color); //get
    public function findCars($var);//get
    public function setOrder($idCar, $name, $surName, $paymentMethod);//post
    public function getOrders();//get
    public function getDataDescription()//get

}
