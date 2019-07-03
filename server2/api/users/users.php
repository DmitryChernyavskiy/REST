<?php
include_once "../libs/MySQL.php";
include_once "config.php";

class ysers
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
 
    public function findUser($user)
    {
        if (!isset($user) || $user="")
        {
           return null;
        };
  
        $res = $query = $this->DB->connect()->setTableName("users")->SetFild("name")->SetFild("pass")->setConditions("name", $user)->execution();

        return $res;
    }

    public function setUser($user, $pass)
    {
        $res = $this->findUser($user);
        if (!$res || count($user)!=0)
        {
            return null;
        }
        if (isset($user) && isset($pass))
        {
            $this->DB->connect()->setTableName("user")->SetFild("name", $user)->SetFild("pass", $pass)->insert()->execution();
            return $res;
        }
        return null;
    }
}