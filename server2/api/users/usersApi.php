<?php
include_once "config.php";
include_once "libs/carMarket.php";
class carMarketApi
{
    public $requestUri = [];
    public $requestParams = [];
    protected $action = ''; 
    protected $method = ''; //GET|POST|PUT|DELETE
    protected $carMarket;
    protected $test = ['user10'=>'123']; //test auturisation
    public function __construct()
    {
        header("Access-Control-Allow-Orgin: *");
        header("Access-Control-Allow-Methods: *");
        header("Content-Type: application/json");
        $url = trim($_SERVER['REQUEST_URI']);
        if ($str=strpos($url, "?")){
            $url=substr($url, 0, $str);
        };
        $this->requestUri = explode('/', $url);
        $this->requestUri = array_splice($this->requestUri,4);
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
        $user = $_SERVER['PHP_AUTH_USER'];
        $pass = $_SERVER['PHP_AUTH_PW'];
        $validated = (isset($user) && $pass == $this->test[$user]);
        if (!$validated) {
            header('WWW-Authenticate: Basic realm="My Realm"');
            $this->response('', 401);
            exit;
        }
        $this->carMarket  = new carMarket;
    }
    public function run()
    {
        if(array_shift($this->requestUri) !== 'api' || array_shift($this->requestUri) !== get_class($this))
        {
            throw new RuntimeException('API Not Found', 404);
        }
        $this->action = ($this->requestUri ? array_shift($this->requestUri) : null);
        if((!$this->action) || !method_exists($this, $this->action))
        {
            throw new RuntimeException('Invalid Method '.$this->action, 405);
        }
        return $this->{$this->action}();
       
    }
    private function requestStatus($code) {
        $status = array(
            200 => 'OK',
            401 => 'Unauthorized',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            500 => 'Internal Server Error',
        );
        return ($status[$code]) ? $status[$code] : $status[500];
    }
    protected function response($data, $status = 500)
    {
        header("HTTP/1.1 " . $status . " " . $this->requestStatus($status));
        return json_encode($data);
    }
    protected function returnResult($res)
    {
        if($res)
        {
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
        $idCar = $this->requestParams['idCar'];
        $name = $this->requestParams['name'];
        $surName = $this->requestParams['surName'];
        $paymentMethod = $this->requestParams['paymentMethod'];
        
        if(isset($idCar) && isset($name) && isset($surName) && isset($paymentMethod))
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