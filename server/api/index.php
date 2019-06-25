<?php

require_once 'carMarket/CarMarketApi.php';

try {
    $api = new CarMarketApi();
    echo $api->run();
} catch (Exception $e) {
    echo json_encode(Array('error' => $e->getMessage()));
}