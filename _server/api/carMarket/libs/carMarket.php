<?php
include_once "MySQL.php";
//include_once "config.php";

class carMarket
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
    public function listCars()
    {
        $res = $this->DB->connect()->setTableName("cars")->SetFild("id")->select()->
            setTableName("brands")->SetFild("brand")->setJoinConditions("cars.id_brand = brands.id")->leftJoin()->
            setTableName("models")->SetFild("model")->setJoinConditions("cars.id_model = models.id")->leftJoin()->
            setTableName("colors_cars")->setJoinConditions("cars.id = colors_cars.id_car")->leftJoin()->
            setTableName("colors")->SetFild("color")->setJoinConditions("colors_cars.id_color = colors.id")->leftJoin()->execution();
        return $res;
        //return array(array("id"=>1, "name"=>"test1"), array("id"=>2, "name"=>"test1"));
    }
    public function getInfo($idCar, $color)
    {
        if (isset($idCar))
        {
            $query = $this->DB->connect()->setTableName("cars")->SetFild("id")->SetFild("volume")->SetFild("speed")->SetFild("year")->SetFild("price")->setConditions("id", $idCar)->select()->
                setTableName("brands")->SetFild("brand")->setJoinConditions("cars.id_brand = brands.id")->leftJoin()->
                setTableName("models")->SetFild("model")->setJoinConditions("cars.id_model = models.id")->leftJoin();
            if (isset($color) && $color!="")
            {
                $query->setTableName("colors_cars")->setJoinConditions("cars.id = colors_cars.id_car")->innerJoin()->
                setTableName("colors")->SetFild("color")->setConditions("color", $color)->setJoinConditions("colors_cars.id_color = colors.id")->innerJoin();
            }
            else
            {
                $query->setTableName("colors_cars")->setJoinConditions("cars.id = colors_cars.id_car")->leftJoin()->
                setTableName("colors")->SetFild("color")->setJoinConditions("colors_cars.id_color = colors.id")->leftJoin();
            }
            $res =$query->execution();
            return $res;
        }
        return array();
    }
    public function findCars($var)
    {
        $volume = $var['volume'];
        $speed = $var['speed'];
        $year = $var['year'];
        $price = $var['price'];
        $brand = $var['brand'];
        $model = $var['model'];
        $color = $var['color'];
        $query = $this->DB->connect()->setTableName("cars")->SetFild("id");
        if (isset($volume) && $volume!=0)
        {
            $query->setConditions("volume", $volume);
        };
        if (isset($speed) && $speed!=0)
        {
            $query->setConditions("speed", $speed);
        };
        if (isset($year) && $year!=0)
        {
            $query->setConditions("year", $year);
        };
        if (isset($price) && $price!=0)
        {
            $query->setConditions("price", $price);
        };    
        $query->select()->setTableName("brands")->SetFild("brand");
        //brand
        if (isset($brand) && $brand!="")
        {
            $query->setConditions("brand", $brand)->setJoinConditions("cars.id_brand = brands.id")->innerJoin();
        }
        else
        {
            $query->setJoinConditions("cars.id_brand = brands.id")->leftJoin();
        };
        //model
        $query->setTableName("models")->SetFild("model");
        if (isset($model) && $model!="")
        {
            $query->setConditions("model", $model)->setJoinConditions("cars.id_model = models.id")->innerJoin();
        }
        else
        {
            $query->setJoinConditions("cars.id_model = models.id")->leftJoin();
        };
        //color
        if (isset($color) && $color!="")
        {
            $query->setTableName("colors_cars")->setJoinConditions("cars.id = colors_cars.id_car")->innerJoin()->
            setTableName("colors")->SetFild("color")->setConditions("color", $color)->setJoinConditions("colors_cars.id_color = colors.id")->innerJoin();
        }
        else
        {
            $query->setTableName("colors_cars")->setJoinConditions("cars.id = colors_cars.id_car")->leftJoin()->
            setTableName("colors")->SetFild("color")->setJoinConditions("colors_cars.id_color = colors.id")->leftJoin();
        };
        $res = $query->execution();
        return $res;
    }
    public function setOrder($idCar, $name, $surName, $paymentMethod)
    {
        if (isset($idCar) && isset($name) && isset($surName) && isset($paymentMethod))
        {
            $this->DB->connect()->setTableName("orders_cars")->SetFild("id_car", $idCar)->SetFild("name", $name)->SetFild("surName", $surName)->SetFild("pay", $paymentMethod)->insert();

            $res =$this->DB->execution();
            return $res;
        }
        return false;
    }
    public function getOrders()
    {
        $res = $this->DB->connect()->setTableName("orders_cars")->SetFild("id_car")->SetFild("name")->SetFild("surName")->SetFild("pay")->select()->execution();
        return $res;
    }

    private function getColumrVal($table, $key)
    {
        $res_arr = array();

        $query = $this->DB->connect();
        $arr = $query->setTableName($table)->SetFild($key)->select()->execution();
        foreach($arr as $val)
        {
            $res_arr[] = $val[$key];
        }
        return $res_arr;
    }

    public function getDataDescription()
    {
        $desc = array();

        
        $desc['volume'] = $this->getColumrVal("cars", "volume");
        $desc['speed'] = $this->getColumrVal("cars", "speed");
        $desc['year'] = $this->getColumrVal("cars", "year");
        $desc['price'] = $this->getColumrVal("cars", "price");
        $desc['brand'] = $this->getColumrVal("brands", "brand");
        $desc['model'] = $this->getColumrVal("models", "model");
        $desc['color'] = $this->getColumrVal("colors", "color");
        
        return $desc;

    }
}