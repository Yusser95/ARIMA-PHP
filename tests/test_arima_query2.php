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


$sql = "
SELECT bm.f AS col ,dates.date AS da FROM 

(
SELECT concat(a.ay ,'-' , b.bm ,'-' , c.cd ,' ' , d.dh) as date
FROM (SELECT DATE_FORMAT(TIMESTAMP(ten),'%Y') AS ay FROM bsm_monitor_value_hist GROUP BY DATE_FORMAT(TIMESTAMP(ten),'%Y')) AS a
CROSS JOIN (SELECT DATE_FORMAT(TIMESTAMP(ten),'%m') AS bm FROM bsm_monitor_value_hist GROUP BY DATE_FORMAT(TIMESTAMP(ten),'%m') ) AS b
CROSS JOIN (SELECT DATE_FORMAT(TIMESTAMP(ten),'%d') AS cd FROM bsm_monitor_value_hist GROUP BY DATE_FORMAT(TIMESTAMP(ten),'%d') ) AS c
CROSS JOIN (SELECT DATE_FORMAT(TIMESTAMP(ten),'%h') AS dh FROM bsm_monitor_value_hist GROUP BY DATE_FORMAT(TIMESTAMP(ten),'%h') ) AS d
GROUP BY a.ay , b.bm , c.cd , d.dh
) AS dates 


LEFT JOIN (SELECT count(four) AS f, DATE_FORMAT(TIMESTAMP(ten),'%Y-%m-%d %h') AS ten FROM bsm_monitor_value_hist WHERE four LIKE '127.0.0.1+%' GROUP BY DATE_FORMAT(TIMESTAMP(ten),'%Y-%m-%d %h')) AS bm
    ON dates.date = bm.ten
    
WHERE TIMESTAMP(dates.date) >= (SELECT TIMESTAMP(ten) FROM bsm_monitor_value_hist WHERE four LIKE '127.0.0.1+%' ORDER BY TIMESTAMP(ten) LIMIT 1) 
AND TIMESTAMP(dates.date) <= (SELECT TIMESTAMP(ten) FROM bsm_monitor_value_hist WHERE four LIKE '127.0.0.1+%' ORDER BY TIMESTAMP(ten) DESC LIMIT 1)


;
";







// arima
$order = array(0,1,1);
$arc = new arimaContrller($order);
$res = $arc->setDataBaseConnection($link)->query($sql,"da" ,"col" )->forecast(1);
var_dump($res);



// auto arima
$arc = new autoArimaContrller("BIC");
$res = $arc->setDataBaseConnection($link)->query($sql , "da" ,"col" )->forecast(1);
var_dump($res);



var_dump($arc->getParms());