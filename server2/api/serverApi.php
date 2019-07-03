<?php
include_once "carMarket/carMarket.php";
include_once "users/users.php";
include_once "ViewApi.php";

class serverApi
{
    public $requestUri = [];
    public $requestParams = [];
    protected $action = ''; 
    protected $method = ''; //GET|POST|PUT|DELETE
    protected $className;
    protected $test = ['user10'=>'123']; //test auturisation
    protected $ViewApi;

    public function __construct()
    {
        $this->ViewApi = new ViewApi;

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
            $this->ViewAp->response('', 401);
            exit;
        }
    }
    public function run()
    {
        if(array_shift($this->requestUri) !== 'api')
        {
            throw new RuntimeException('API Not Found', 404);
        }
        $className = array_shift($this->requestUri);
        if($className != get_class($this))
        {
            throw new RuntimeException('class API Not Found', 405);
        }
        $this->action = ($this->requestUri ? array_shift($this->requestUri) : null);
        $class = new $className;
        if((!$this->action) || !method_exists($class, $this->action))
        {
            throw new RuntimeException('Invalid Method '.$this->action, 405);
        }
        if($this->method = 'PUT' || $this->method = 'POST'){
            $res = $class->{$this->action}(json_decode(file_get_contents('php://input'), true));
        }else{
            $res = $class->{$this->action}($this->requestParams);
        } 
        return $this->returnResult($res);      
       
    }
    protected function returnResult($res)
    {
        if($res)
        {
            return $this->ViewAp->response($res, 200);
        }
        return $this->ViewAp->response('Data not found', 404);
    }
}