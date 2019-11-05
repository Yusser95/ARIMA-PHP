<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 12/20/18
 * Time: 7:00 PM
 */



require_once __DIR__ . '/../src/arimaContrller.php';
require_once __DIR__ . '/../src/autoArimaContrller.php';


/*
 *     read data from data base and forecast
 * */


//    database connection

define( 'DB_HOST', 'localhost' ); // set database host
define( 'DB_USER', 'root' ); // set database user
define( 'DB_PASS', 'root' ); // set database password
define( 'DB_NAME', 'ArimaForcast' ); // set database name


mb_internal_encoding( 'UTF-8' );
mb_regex_encoding( 'UTF-8' );
mysqli_report( MYSQLI_REPORT_STRICT );

$link="";
try {
    $link = new mysqli( DB_HOST, DB_USER, DB_PASS, DB_NAME );
    $link->set_charset( "utf8" );
} catch ( Exception $e ) {
    die( 'Unable to connect to database' );
}



// arima
$order = array(0,1,1);
$arc = new arimaContrller($order);
$res = $arc->setDataBaseConnection($link)->select('ten' ,'COUNT(four)','bsm_monitor_value_hist')->where("four LIKE 'zabbix-internal-camper.srv.joopbox.com+%'")->groupBy("four")->orderBy("CAST(SUBSTR(four , LOCATE('+', four) ) AS UNSIGNED)",true)->forecast(1);
var_dump($res);


// auto arima
$arc = new autoArimaContrller("BIC");
$res = $arc->setDataBaseConnection($link)->select('ten' , 'COUNT(four)','bsm_monitor_value_hist')->where("four LIKE 'zabbix-internal-camper.srv.joopbox.com+%'")->groupBy("four")->orderBy("CAST(SUBSTR(four , LOCATE('+', four) ) AS UNSIGNED)",true)->forecast(1);
var_dump($res);

var_dump($arc->getParms());