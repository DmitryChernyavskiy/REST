<?php
include_once "./libs/QuerySQL.php";
class mySQL extends QuerySQL
{
    static private $link, $typeDB, $hostDB, $nameDB, $user, $pass;
    
    function __construct($typeDB, $hostDB, $nameDB, $user, $pass)
    {
        parent::__construct();
        if (!mySQL::$link) //if the connection to database is not set
        {
            mySQL::$typeDB = $typeDB; //pgsql,mysql....
            mySQL::$hostDB = $hostDB;
            mySQL::$nameDB = $nameDB;
            mySQL::$user = $user;
            mySQL::$pass = $pass;
        }
    }
    
    function __destruct()
    {
        if (mySQL::$link) //mySQL::link
        {
            mySQL::$link = null;
        }
    }
    
    public function connect()
    {
        if (!mySQL::$link)
        {
            try
            {
                mySQL::$link = new PDO(mySQL::$typeDB.":host=".mySQL::$hostDB.";dbname=".mySQL::$nameDB,  mySQL::$user, mySQL::$pass);
            }
            catch (PDOException $e)
            {
                $this->errortext .= " Error connect: ".$e->getMessage()."<br/>";
            }
        }

        return $this;
    }
    
    public function Execution()
    {
        //echo "**".$this->getQuery();
        //print_r($this->params);
        try
        {
            //echo $this->getQuery();
            $stmt = mySQL::$link->prepare($this->getQuery());
            $stmt->execute($this->params);
            $this->getQuery();
        }
        catch (PDOException $e)
        {
            $this->errortext .= "Error execution: ".$e->getMessage()."<br/>";
            return false;
        }
        $this->clearQuery(true);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
/* 
        $dd = $this->getQuery();
        $this->clearQuery(true);
        return $dd;*/
    }
}