<?php


require_once __DIR__ . '/arimaContrller.php';
require_once __DIR__ . '/autoArimaContrller.php';


class arimaModel
{

    private static $order;

    public static function arima(array $data ,array $order , $pred_num=1){

        try {
            $arc = new arimaContrller($order);

            $res = $arc->setDataArray($data)->forecast($pred_num);
        }catch (Exception  $e){
            echo "svd did not converge !! hint ( change order )";
        }

        return $res;

    }

    public static function auto_arima(array $data , $pred_num = 1 , $algo = "AIC"){
        try {
            $arc = new autoArimaContrller($algo);

            $res = $arc->setDataArray($data)->forecast($pred_num);
        }catch (Exception  $e){
            echo "svd did not converge !! hint ( change order ) \n";
        }

        self::$order = $arc->getParms();

        return $res;

    }

    public static function get_auto_arima_order(){
        if (is_array(self::$order) && count(self::$order) > 0){
            $p = self::$order[0];
            $d = self::$order[1];
            $q = self::$order[2];
            return array('p'=> $p ,'d'=>$d , 'q'=>$q);
        }else{
            echo "order not avalible try call auto_arima first !! \n";
            return null;
        }
    }

}