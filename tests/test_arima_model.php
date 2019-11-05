<?php


require_once __DIR__ . '/../src/arimaModel.php';

$data = array(
    266.0,
    145.9,
    183.1,
    119.3,
    180.3,
    168.5,
    231.8,
    224.5,
    192.8,
    122.9,
    336.5,
    185.9,
    194.3,
    149.5,
    210.1,
    273.3,
    191.4,
    287.0,
    226.0,
    303.6,
    289.9
);

$order = array(1,1,1);


$res = arimaModel::arima($data , $order);

var_dump($res);

$res= arimaModel::auto_arima($data);

$pred_order = arimaModel::get_auto_arima_order();

var_dump($pred_order);
var_dump($res);