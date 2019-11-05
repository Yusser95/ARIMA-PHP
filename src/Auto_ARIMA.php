<?php


require_once __DIR__ . '/ARMAMATH.php';
require_once __DIR__ . '/AR.php';
require_once __DIR__ . '/MA.php';
require_once __DIR__ . '/ARMA.php';
require_once __DIR__ . '/Random.php';
require_once __DIR__ . '/MatrixLibrary.php';


class Auto_ARIMA {

	public $originalData = array();
	public $armamath;
	public $stderrDara=0;
	public $avgsumData=0;
	public $armaARMAcoe=array();
	public $bestarmaARMAcoe=array();
	public $p;
	public $q;
	public $d;
	
/**
 * Constructor
 * @param originalData Raw time series data
 */
	public function __construct($originalData , $eval = "AIC")
	{
		$this->armamath= new ARMAMath();
		$this->originalData=$originalData;
		$this->getARIMAmodel($eval);
	}
/**
 * Raw data normalization processing: first-order seasonal difference
 * @return Differential data
 */ 
	public function preDealDif()
	{

        $lag = $this->d;

        $tempData = array();
        if (count($this->originalData) - $lag >= 0) {

            for ($i = 0; $i < count($this->originalData) - $lag; $i++) {
                $tempData[$i] = $this->originalData[$i + $lag] - $this->originalData[$i];
            }
        }else{
            die( "error data to small for lag={$lag} choose another value !");
        }

		return $tempData;
	}


    public function preDealDif2()
    {

        $order = $this->d;
        $tempData = $this->originalData;

        for($i= 0 ; $i < $order ;$i++){
            $tempData = $this->diffrence($tempData,1);

        }

        return $tempData;
    }


    public function diffrence($data , $lag)
    {


        $tempData = array();
        if (count($data) - $lag > 0) {

            for ($i = 0; $i < count($data) - $lag; $i++) {
                $tempData[$i] = $data[$i + $lag] - $data[$i];
            }
        }else{
            die( "error data to small for lag={$lag} choose another value !");
        }

        return $tempData;
    }


/**
 * Perform inverse differential processing on predicted values
 * @param predictValue Predicted value
 * @return Anti-differential prediction value
 */
    public function aftDeal($predictValue)
    {
        return (int)($predictValue+$this->originalData[count($this->originalData)-$this->d]);
    }

    public function aftDeal2($predictValue)
    {

        $tempData = $this->preDealDif();

        return (int)($predictValue+$tempData[count($tempData)-1]);

    }


/**
 * Raw data standardization processing: Z-Score normalization
 * @param Pending data
 * @return Normalized data
 */
	public function preDealNor(array $tempData)
	{
		//Z-Score
		$this->avgsumData=$this->armamath.avgData($tempData);
		$this->stderrDara=$this->armamath.stderrData($tempData);
		
		for($i=0;$i<count($tempData);$i++)
		{
			$tempData[$i]=($tempData[$i]-$this->avgsumData)/$this->stderrDara;
		}
		
		return $tempData;
	}
/**
* Get the ARMA model=[p,q]
 * @return Order information of the ARMA model
 */
	public function getARIMAmodel($eval = "AIC")
	{
        $paraType=0;
        $minAIC=9999999;
        $bestModelindex=0;

        $model = array(array(0, 1), array(1, 0), array(1, 1), array(0, 2), array(2, 0), array(2, 2), array(1, 2), array(2, 1), array(3, 0), array(0, 3), array(3, 1), array(1, 3), array(3, 2), array(2, 3), array(3, 3));//,{4,0},{0,4},{4,1},{1,4},{4,2},{2,4},{4,3},{3,4},{4,4}};



        for($d=1;$d<2;$d++) {
            $this->d = $d;

            $stdoriginalData = $this->preDealDif();//Raw data differential processing


            //Iterate over 8 models and select the model with the smallest AIC value as our model.
            for ($i = 0; $i < count($model); $i++) {
                try {
                    if ($model[$i][0] == 0) {
                        $ma = new MA($stdoriginalData, $model[$i][1]);
                        $this->armaARMAcoe = $ma->MAmodel();
                        $paraType = 1;
                    } else if ($model[$i][1] == 0) {
                        $ar = new AR($stdoriginalData, $model[$i][0]);
                        $this->armaARMAcoe = $ar->ARmodel();
                        $paraType = 2;
                    } else {
                        $arma = new ARMA($stdoriginalData, $model[$i][0], $model[$i][1]);
                        $this->armaARMAcoe = $arma->ARMAmodel();
                        $paraType = 3;
                    }

                    if ($eval == "AIC") {
                        $temp = $this->getmodelAIC($this->armaARMAcoe, $stdoriginalData, $paraType);
//                        print("AIC of these model=");
                    }
                    elseif ($eval == "BIC") {
                        $temp = $this->getmodelBIC($this->armaARMAcoe, $stdoriginalData, $paraType);
//                        print("BIC of these model=");
                    }
                    elseif ($eval == "AIC+BIC") {
                        $t1 = $this->getmodelAIC($this->armaARMAcoe, $stdoriginalData, $paraType);
                        $t2 = $this->getmodelBIC($this->armaARMAcoe, $stdoriginalData, $paraType);

                        $temp = ($t1+$t2)/2;
//                        print("BIC of these model=");
                    }
                    else{
                        $temp = $this->getmodelMSE($model[$i], $stdoriginalData);
//                        print("MSE of these model=");
                    }

                    if ($temp < $minAIC) {
                        $bestModelindex = $i;
                        $minAIC = $temp;
                        $this->bestarmaARMAcoe = $this->armaARMAcoe;

                        $this->p = $model[$i][0];
                        $this->q = $model[$i][1];
                    }
                }catch (Exception  $e){

                }
            }
        }
		
		return $model[$bestModelindex];
 	}
/**
 * Calculate the AIC of the ARMA model
 * @param para Load model parameter information
 * @param stdoriginalData   Preprocessed raw data
 * @param type 1：MA；2：AR；3：ARMA
 * @return Model AIC value
 */
	public function getmodelAIC($para,$stdoriginalData,$type)
	{
		$temp=0;
		$temp2=0;
		$sumerr=0;
		$p=0;//ar1,ar2,...,sig2
		$q=0;//sig2,ma1,ma2...
		$n=count($stdoriginalData);
		$random=new Random();
		
		if($type==1)
		{
			$maPara=$para[0];
			$q=count($maPara);
			$err=array_fill(0,$q,0);  //error(t),error(t-1),error(t-2)...
			for($k=$q-1;$k<$n;$k++)
			{
				$temp=0;
				
				for($i=1;$i<$q;$i++)
				{
					    $temp+=$maPara[$i]*$err[$i];
				}
			
				//Generate noise at various moments
				for($j=$q-1;$j>0;$j--)
				{
					$err[$j]=$err[$j-1];
				}
				$err[0]=$random->gauss()*sqrt($maPara[0]);
				
				//The sum of the estimated variances
				$sumerr+=($stdoriginalData[$k]-($temp))*($stdoriginalData[$k]-($temp));
				
			}
			return ($n-($q-1))*log($sumerr/($n-($q-1)))+($q+1)*2;
		}
		else if($type==2)
		{
			$arPara=$para[0];
			$p=count($arPara);
			for($k=$p-1;$k<$n;$k++)
			{
				$temp=0;
				for($i=0;$i<$p-1;$i++)
				{
					$temp+=$arPara[$i]*$stdoriginalData[$k-$i-1];
				}
				//The sum of the estimated variances
				$sumerr+=($stdoriginalData[$k]-$temp)*($stdoriginalData[$k]-$temp);
			}
			return ($n-($q-1))*log($sumerr/($n-($q-1)))+($p+1)*2;
		}
		else
		{
			$arPara=$para[0];
			$maPara=$para[1];
			$p=count($arPara);
			$q=count($maPara);
			$err=array_fill(0,$q,0);  //error(t),error(t-1),error(t-2)...
			
			for($k=$p-1;$k<$n;$k++)
			{
				$temp=0;
				$temp2=0;
				for($i=0;$i<$p-1;$i++)
				{
					$temp+=$arPara[$i]*$stdoriginalData[$k-$i-1];
				}
			
				for($i=1;$i<$q;$i++)
				{
					$temp2+=$maPara[$i]*$err[$i];
				}
			
				//Generate noise at various moments
				for($j=$q-1;$j>0;$j--)
				{
					$err[$j]=$err[$j-1];
				}
				$err[0]=$random->gauss()*sqrt($maPara[0]);
				$sumerr+=($stdoriginalData[$k]-($temp2+$temp))*($stdoriginalData[$k]-($temp2+$temp));
			}
			return ($n-($q-1))*log($sumerr/($n-($q-1)))+($p+$q)*2;
		}
	}


    /**
     * Calculate the BIC of the ARMA model
     * @param para Load model parameter information
     * @param stdoriginalData   Preprocessed raw data
     * @param type 1：MA；2：AR；3：ARMA
     * @return Model BIC value
     */
    public function getmodelBIC($para,$stdoriginalData,$type)
    {
        $temp=0;
        $temp2=0;
        $sumerr=0;
        $p=0;//ar1,ar2,...,sig2
        $q=0;//sig2,ma1,ma2...
        $n=count($stdoriginalData);
        $random=new Random();

        if($type==1)
        {
            $maPara=$para[0];
            $q=count($maPara);
            $err=array_fill(0,$q,0);  //error(t),error(t-1),error(t-2)...
            for($k=$q-1;$k<$n;$k++)
            {
                $temp=0;

                for($i=1;$i<$q;$i++)
                {
                    $temp+=$maPara[$i]*$err[$i];
                }

                //Generate noise at various moments
                for($j=$q-1;$j>0;$j--)
                {
                    $err[$j]=$err[$j-1];
                }
                $err[0]=$random->gauss_ms()*sqrt($maPara[0]);

                //The sum of the estimated variances
                $sumerr+=($stdoriginalData[$k]-($temp))*($stdoriginalData[$k]-($temp));

            }
            return ($n-($q-1))*log($sumerr/($n-($q-1)))+($q+1)*log($n);
        }
        else if($type==2)
        {
            $arPara=$para[0];
            $p=count($arPara);
            for($k=$p-1;$k<$n;$k++)
            {
                $temp=0;
                for($i=0;$i<$p-1;$i++)
                {
                    $temp+=$arPara[$i]*$stdoriginalData[$k-$i-1];
                }
                //The sum of the estimated variances
                $sumerr+=($stdoriginalData[$k]-$temp)*($stdoriginalData[$k]-$temp);
            }
            return ($n-($q-1))*log($sumerr/($n-($q-1)))+($p+1)*log($n);
        }
        else
        {
            $arPara=$para[0];
            $maPara=$para[1];
            $p=count($arPara);
            $q=count($maPara);
            $err=array_fill(0,$q,0);  //error(t),error(t-1),error(t-2)...

            for($k=$p-1;$k<$n;$k++)
            {
                $temp=0;
                $temp2=0;
                for($i=0;$i<$p-1;$i++)
                {
                    $temp+=$arPara[$i]*$stdoriginalData[$k-$i-1];
                }

                for($i=1;$i<$q;$i++)
                {
                    $temp2+=$maPara[$i]*$err[$i];
                }

                //Generate noise at various moments
                for($j=$q-1;$j>0;$j--)
                {
                    $err[$j]=$err[$j-1];
                }
                $err[0]=$random->gauss_ms()*sqrt($maPara[0]);
                $sumerr+=($stdoriginalData[$k]-($temp2+$temp))*($stdoriginalData[$k]-($temp2+$temp));
            }
            return ($n-($q-1))*log($sumerr/($n-($q-1)))+($p+$q)*log($n);
        }
    }

    public function getmodelMSE($para,$stdoriginalData){

//        $count = count($stdoriginalData) * (80 / 100);
        $count = count($stdoriginalData) - 5;
        $train = array_slice($stdoriginalData, 0, round($count));
        $test = array_slice($stdoriginalData, round($count) );

        $results = array();
        for ($i = 0; $i < count($test); $i++) {
            $temp = array_merge($train, $results);
            $arima = new ARIMA($temp, $para[0], $this->d, $para[1]);
            $res = $arima->forecast();
            array_push($results, $res);
        }

        $n = count($test);
        $mse = 0;
        for($i = 0 ; $i<$n ; $i++){
            $mse = ($test[$i]-$results[$i]) * ($test[$i]-$results[$i]) ;
        }

        return $mse / $n;



    }

/**
 * Make one step prediction
 * @param p The order of the AR of the ARMA model
 * @param q The order of the MA of the ARMA model
 * @return Predictive value
 */
	public function forecast()
	{
	    try {
            $p = $this->p;
            $q = $this->q;
            $predict = 0;
            $stdoriginalData = $this->preDealDif();
            $n = count($stdoriginalData);
            $temp = 0;
            $temp2 = 0;
            $err = array_fill(0, $q + 1, 0);//array();

            $random = new Random();
            if ($p == 0) {
                $maPara = $this->bestarmaARMAcoe[0];
                for ($k = $q; $k < $n; $k++) {
                    $temp = 0;
                    for ($i = 1; $i <= $q; $i++) {
                        $temp += $maPara[$i] * $err[$i];
                    }
                    //Generate noise at various moments
                    for ($j = $q; $j > 0; $j--) {
                        $err[$j] = $err[$j - 1];
                    }
                    $err[0] = $random->gauss_ms() * sqrt($maPara[0]);
                }
                $predict = (int)($temp); //Generate prediction
            } else if ($q == 0) {
                $arPara = $this->bestarmaARMAcoe[0];
                for ($k = $p; $k < $n; $k++) {
                    $temp = 0;
                    for ($i = 0; $i < $p; $i++) {
                        $temp += $arPara[$i] * $stdoriginalData[$k - $i - 1];
                    }
                }
                $predict = (int)($temp);
            } else {

                $arPara = $this->bestarmaARMAcoe[0];
                $maPara = $this->bestarmaARMAcoe[1];
                $err = array_fill(0, $q + 1, 0); #array();  //$error(t),$error(t-1),$error(t-2)...

                for ($k = $p; $k < $n; $k++) {
                    $temp = 0;
                    $temp2 = 0;
                    for ($i = 0; $i < $p; $i++) {
                        $temp += $arPara[$i] * $stdoriginalData[$k - $i - 1];
                    }

                    for ($i = 1; $i < $q; $i++) {
                        $temp2 += $maPara[$i] * $err[$i];
                    }

                    //Generate noise at various moments
                    for ($j = $q; $j > 0; $j--) {
                        $err[$j] = $err[$j - 1];
                    }

                    $err[0] = $random->gauss_ms() * sqrt($maPara[0]);
                }

                $predict = (int)($temp2 + $temp);

            }
        }catch (Exception $e){

        }
	
		
		return $this->aftDeal($predict);
	}
/**
 * Calculate the parameters of the MA model
 * @param autocorData Autocorrelation coefficient Grma
 * @param q The order of the MA model
 * @return Return the parameters of the MA model
 */
	public function getMApara($autocorData,$q)
	{
		$maPara=array_fill(0,$q+1,0);//The first one stores the noise parameter, and the next q stores the ma parameter sigma2.,ma1,ma2...
		$tempmaPara=$maPara;
		$temp=0;
		$iterationFlag=true;

		while($iterationFlag)
		{
			for($i=1;$i<count($maPara);$i++)
			{
				$temp+=$maPara[$i]*$maPara[$i];
			}
			$tempmaPara[0]=$autocorData[0]/(1+$temp);
		
			for($i=1;$i<count($maPara);$i++)
			{
				$temp=0;
				for($j=1;$j<count($maPara)-$i;$j++)
				{
					$temp+=$maPara[$j]*$maPara[$j+$i];
				}
				$tempmaPara[$i]=-($autocorData[$i]/$tempmaPara[0]-$temp);
			}
			$iterationFlag=false;
			for($i=0;$i<count($maPara);$i++)
			{
				if($maPara[$i]!=$tempmaPara[$i])
				{
					$iterationFlag=true;
					break;
				}
			}
			
			$maPara=$tempmaPara;
		}
		
		return $maPara;
	}

}