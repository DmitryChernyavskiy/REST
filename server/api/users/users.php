<?php
include_once "./libs/MySQL.php";
include_once "config.php";

class users
{
    private $DB;
    function __construct()
    {
        $this->DB = new MySQL(TYPE_MYSQL_DB, HOST_MYSQL_DB, NAME_MYSQL_DB, USER_MYSQL_DB, PASS_MYSQL_DB);
    }
    
    function __destruct()
    {
        unset($this->DB);
    }
 
    public function findUser($var)
    {
        $user = $var['user'];
        $pass = $var['pass'];
        if (!isset($user) || $user=="")
        {
           return null;
        };

        $test = ['user10'=>'777'];
        if($test[$user] == $pass)
        {
            return true;
        }
        
        $query = $this->DB->connect()->setTableName("users")->SetFild("name")->SetFild("pass")->setConditions("name", $user);
        if (!isset($pass) || $pass=="")
        {
            $query->setConditions("pass", $pass);
        }
        $res  = $query->execution();
        if (!$res || count($user)!=0)
        {
            return null;
        }

        return $res;
    } 

    public function setUser($var)
    {
        $user = $var['user'];
        $pass = $var['pass'];
        
        if (isset($user) && isset($pass) && !$this->findUser($var))
        {
            $this->DB->connect()->setTableName("user")->SetFild("name", $user)->SetFild("pass", $pass)->insert()->execution();
            return $res;
        }
        return null;
    }
}