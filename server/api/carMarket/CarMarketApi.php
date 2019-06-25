<?php
include_once "config.php";
include_once "libs/carMarket.php";

class CarMarketApi
{
    public $requestUri = [];
    public $requestParams = [];

    protected $action = ''; //Название метод для выполнения
    protected $method = ''; //GET|POST|PUT|DELETE

    protected $carMarket;
    protected $test;


    public function __construct()
    {
        header("Access-Control-Allow-Orgin: *");
        header("Access-Control-Allow-Methods: *");
        header("Content-Type: application/json");

        $url = trim($_SERVER['REQUEST_URI']);
        $this->requestUri = explode('/', $url);
        array_splice($this->requestUri,4,1);
        $this->requestParams = $_REQUEST;

        $this->test = $url;//print_r($this->requestUri,true);

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
        $carMarket  = new carMarket;
    }

    public function run()
    {
        $d = array_shift($this->requestUri);
        if($d !== 'api' || array_shift($this->requestUri) !== get_class($this))
        {
            throw new RuntimeException('API Not Found _'.$this->test, 404);
        }

        $this->action = ($this->requestUri ? array_shift($this->requestUri) : null);

        if((!$this->action) || !method_exists($this->action))
        {
            throw new RuntimeException('Invalid Method', 405);
        }
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

    protected function returnResult($res)
    {
        if($res){
            return $this->response($res, 200);
        }
        return $this->response('Data not found', 404);
    }

    public function listCars()//get
    {
        return $this->returnResult($this->carMarket->listCars());
    }

    public function getInfo() //get
    {
        $idCar = array_shift($this->requestUri);
        $color = array_shift($this->requestUri);

        return $this->returnResult($this->carMarket->getInfo($idCar, $color));
    }

    public function findCars()//get
    {
        return $this->returnResult($this->carMarket->findCars($this->requestParams));
    }

    public function getOrders()//get
    {
        return $this->returnResult($this->carMarket->getOrders());
    }

    public function getDataDescription()//get
    {
        return $this->returnResult($this->carMarket->getDataDescription());
    }

    public function setOrder()//post
    {
        $idCar = (array_key_exists('idCar', $this->requestParams) ?  $this->requestParams['idCar']  : '');
        $name = (array_key_exists('name', $this->requestParams) ?  $this->requestParams['name']  : '');
        $surName = (array_key_exists('surName', $this->requestParams) ?  $this->requestParams['surName']  : '');
        $paymentMethod = (array_key_exists('paymentMethod', $this->requestParams) ?  $this->requestParams['paymentMethod']  : '');
        
        if($idCar && $name && $surName && $paymentMethod)
        {
            $res = $this->carMarket->setOrder($idCar, $name, $surName, $paymentMethod);
            if($res)
            {
                return $this->response('Data saved.', 200);
            }
        }
        return $this->response("Saving error", 500);
    }

}
