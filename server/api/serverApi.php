<?php
include_once "carMarket/carMarket.php";
include_once "carMarket/config.php";
include_once "users/users.php";
include_once "ViewApi.php";

class serverApi
{
    public $requestUri = [];
    public $requestParams = [];
    protected $action = ''; 
    protected $method = ''; //GET|POST|PUT|DELETE
    protected $className;
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
        //error_log ("_1_ ".$this->method, 3, "/home/user10/public_html/errors.log");

        $format = mb_strtolower($this->requestUri[count($this->requestUri)-1]);//DEFINE_FORMAT;
        if($format != '.txt' && $format != '.html' && $format != '.html')
        {
            $format = DEFINE_FORMAT;
        }
        $this->ViewApi->setFormat($format);

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
        if(array_shift($this->requestUri) !== 'api')
        {
            throw new RuntimeException('API Not Found', 404);
        }
        $className = array_shift($this->requestUri);
        if(!class_exists($className))
        {
            throw new RuntimeException('class API Not Found', 405);
        }
        $this->action = ($this->requestUri ? array_shift($this->requestUri) : null);
        $class = new $className;
        if((!$this->action) || !method_exists($class, $this->action))
        {
            throw new RuntimeException('Invalid Method '.$this->action, 405);
        }

        $user = $_SERVER['PHP_AUTH_USER'];
        $pass = $_SERVER['PHP_AUTH_PW'];
        $users = new users;
        $validated = (isset($user) && $users->findUser(['user'=> $user,'pass'=> $pass]));
        if (!$validated && $className != "users") {
            header('WWW-Authenticate: Basic realm="My Realm"');
            return $this->ViewApi->response('', 401);
        }

        if(count($this->requestParams) && ($this->method == 'PUT' || $this->method == 'POST'))
        {
            $res = $class->{$this->action}(json_decode(file_get_contents('php://input'), true));
        }else{
            //error_log ("\n2_listcar".print_r($this->requestParams,true)."\n", 3, "/home/user10/public_html/errors.log");
            $res = $class->{$this->action}($this->requestParams);
        } 
        
        if($res)
        {
            return $this->ViewApi->response($res, 200);
        }
        return $this->ViewApi->response('Data not found', 404);      
       
    }

}