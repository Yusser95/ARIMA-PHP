<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 12/27/18
 * Time: 4:45 PM
 */

require_once __DIR__ . '/../src/TimeSiries.php';


//    database connection
define( 'DB_HOST', 'localhost' ); // set database host
define( 'DB_USER', 'root' ); // set database user
define( 'DB_PASS', 'root' ); // set database password
define( 'DB_NAME', 'ArimaForcast' ); // set database name


$conn="";
try {
    $conn = new mysqli( DB_HOST, DB_USER, DB_PASS, DB_NAME );
    $conn->set_charset( "utf8" );
} catch ( Exception $e ) {
    die( 'Unable to connect to database' );
}



$query = "SELECT COUNT(four) col ,ten da FROM bsm_monitor_value_hist WHERE four LIKE '127.0.0.1+%' GROUP BY DATE_FORMAT(TIMESTAMP(ten),'%Y-%m-%d %h');";
$results = $conn->query( $query )->fetch_all();
//var_dump($results);

$ts = new TimeSiries($results,1,0);
$ts->fill_missing_data("01h");
var_dump($ts->data);


