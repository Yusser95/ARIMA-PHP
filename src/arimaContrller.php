<?php



require_once __DIR__ . '/ARIMA.php';
require_once __DIR__ . '/TimeSiries.php';



class arimaContrller
{

    public $dataArray;
    public $p;
    public $d;
    public $q;
    public $pred_num;

    public $conn;
    public $query;
    public $date_column;
    public $value_column;
    private $step;




    public function __construct($order) {
        $this->p = $order[0];
        $this->d = $order[1];
        $this->q = $order[2];
        $this->step = null;
    }


    public function query($query , $date_column, $value_column ,$step=null){
        $this->dataArray = null;
        $this->date_column = $date_column;
        $this->value_column = $value_column;
        $this->query = $query;
        $this->step = $step;
        return $this;
    }


    public function select($date_column, $value_column , $table){
        $this->dataArray = null;
        $this->date_column = $date_column;
        $this->value_column = $value_column;
        $this->query = "SELECT {$date_column} , {$value_column} FROM {$table}";

        return $this;

    }

    public function where($where){
        $this->query = $this->query." WHERE {$where}";
        return $this;
    }

    public function orderBy($col , $desending = false){
        $this->query = $this->query . " ORDER BY ".$col;
        if ($desending){
            $this->query = $this->query ." DESC";
        }
        return $this;
    }


    public function groupBy($q){
        $this->query = $this->query . " GROUP BY ".$q;

        return $this;
    }


    public function setDataBaseConnection($link)
    {
        $this->dataArray = null;
        $this->conn = $link;
        return $this;
    }



    public function setDataArray($dataArray)
    {
        $this->dataArray = $dataArray;
        return $this;
    }




    public function get_results( $query, $object = false )
    {


        //Overwrite the $row var to null
        $row = null;

        $results = $this->conn->query( $query );
        if( $this->conn->error )
        {
            echo $this->conn->error;
        }
        else
        {
            $row = array();
            while( $r = ( !$object ) ? $results->fetch_assoc() : $results->fetch_object() )
            {
                $row[] = $r;
            }
            return $row;
        }
    }


    public function excute(){


        if(!$this->query){

            die( "query is not defined ! \n");

        }else{
            if($this->conn) {

                $full_query = $this->get_results($this->query);

                if (is_array($full_query)) {

                    if($this->step){
                        $ts = new TimeSiries($full_query,$this->date_column,$this->value_column);

                        $full_query = $ts->fill_missing_data($this->step);
                    }

                    $data = array();
                    foreach ($full_query as $row) {
                        if (is_double((double)$row[$this->value_column])) {
                            array_push($data, (double)$row[$this->value_column]);
                        } else {
                            die( "query return data that is not numeric !!! \n");
                        }
                    }

                    $data = array_reverse($data);

                    $this->dataArray = $data;
                }
            }else{
                die( "connection is not set ! \n");
            }
        }


        return $this->dataArray;


    }

    public function forecast($pred_num)
    {

        if(!$this->dataArray){
            $this->excute();
        }


        $results = array();

        if($this->dataArray) {
            for ($i = 0; $i < $pred_num; $i++) {
                $temp = array_merge($this->dataArray, $results);

                $arima = new ARIMA($temp, $this->p, $this->d, $this->q);
                $res = $arima->forecast();
                array_push($results, $res);
            }
        }

        return $results;

    }

}



