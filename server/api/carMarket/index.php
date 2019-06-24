<?php

require_once 'CarMarketApi.php';

try {
    $api = new CarMarketApi();
    echo $api->run();
} catch (Exception $e) {
    echo json_encode(Array('error' => $e->getMessage()));
}