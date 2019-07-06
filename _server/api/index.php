<?php

require_once 'carMarket/carMarketApi.php';

try 
{
    $api = new carMarketApi();
    echo $api->run();
}
catch (Exception $e)
{
    echo json_encode(Array('error' => $e->getMessage()));
}