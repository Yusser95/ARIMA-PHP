<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 12/27/18
 * Time: 4:45 PM
 */

require_once __DIR__ . '/../src/TimeSiries.php';


// from array


$data = array(

    array("type"=>"fruit", "datetime"=>"2018-06-14 19:00:23"),
    array("type"=>"pork", "datetime"=>"2018-06-14 22:00:23"),
    array("type"=>"milk", "datetime"=>"2018-06-14 21:00:23"),

);

$ts = new TimeSiries($data,'datetime','type');

var_dump($ts->fill_missing_data("01h"));



