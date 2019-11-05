<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 12/27/18
 * Time: 7:55 PM
 */

class TimeSiries
{

    public $data; // array of data has two columns
    public $col_date;
    public $col_value;
    public $sorted;

    private $formats = array('Y'=>0,'y'=>1,'m'=>2,'d'=>3,'H'=>4,'h'=>5,'i'=>6,'s'=>7);



    public function __construct(array $data , $col_date , $col_value) {
        $this->data = $data;
        $this->col_date = $col_date;
        $this->col_value = $col_value;
        $this->sorted=false;
    }


    // step (2 digit number and letter "h,i,s  ,d,m,y")
    public function fill_missing_data($step){
        echo "time siries class - filling missing data \n";
        if(strlen($step) == 3) {
            if(!$this->sorted){
                $this->sort_data();
            }

            $dur = (int)substr($step, 0, 2);
            $dur_type  = substr($step, 2, 3);

            $temp =array();
            $pdt = null;
            foreach ($this->data as $row) {
                $dt = new DateTime($row[$this->col_date]);
                if($pdt != null) {

                    while (true) {

                        $pdt->add(new DateInterval($this->add_interval($dur_type, $dur)));


                        $flag = true;
                        foreach (array_keys($this->formats) as $t) {
                            if ($this->formats[$t] <= $this->formats[$dur_type]) {
                                if ((int)$dt->diff($pdt)->format("%".$t) != 0) {
                                    $flag = false;
                                }
                            }
                        }

                        if ($flag) {
                            array_push($temp,$row);
                            break;
                        }else{
                            array_push($temp,array($this->col_value=>null, $this->col_date=>$pdt->format("Y-m-d H:i:s")));
                        }


                    }



                }else{
                    array_push($temp,$row);
                }
                $pdt = $dt;
            }

            $this->data = $temp;

            return $this->data;

        }
        else{
            die("step is rong ex:20s !!");
        }

    }



    function date_compare($a, $b)
    {
        $t1 = strtotime($a[$this->col_date]);
        $t2 = strtotime($b[$this->col_date]);
        return $t1 - $t2;
    }


    public function sort_data(){
        usort($this->data, array($this,'date_compare'));
        $this->sorted=true;
        return $this->data;
    }

    function seconds_diff($t1 , $t2)
    {

        $differenceInSeconds = $t1 - $t2;
        return $differenceInSeconds;
    }

    function minuts_diff($t1 , $t2)
    {

        $differenceInSeconds = $t1 - $t2;
        return $differenceInSeconds/60;
    }

    function hours_diff($t1 , $t2)
    {

        $differenceInSeconds = $t1 - $t2;
        return $differenceInSeconds/60/60;
    }


    function add_interval($step ,$interval)
    {
        $sReturn = 'P';

        if($step == 'y'){
            $sReturn .= $interval . 'Y';
        }

        if($step == 'm'){
            $sReturn .= $interval . 'M';
        }

        if($step == 'd'){
            $sReturn .= $interval . 'D';
        }

        if($step == 'h' || $step == 'i' || $step == 's'){
            $sReturn .= 'T';

            if($step == 'h'){
                $sReturn .= $interval . 'H';
            }

            if($step == 'i'){
                $sReturn .= $interval . 'M';
            }

            if($step == 's'){
                $sReturn .= $interval . 'S';
            }
        }

        return $sReturn;
    }

}