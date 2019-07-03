<?php

require_once 'serverApi.php';

try 
{
    $api = new serverApi();
    echo $api->run();
}
catch (Exception $e)
{
    echo json_encode(Array('error' => $e->getMessage()));
}