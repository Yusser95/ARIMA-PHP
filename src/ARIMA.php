<?php


require_once __DIR__ . '/ARMAMATH.php';
require_once __DIR__ . '/AR.php';
require_once __DIR__ . '/MA.php';
require_once __DIR__ . '/ARMA.php';
require_once __DIR__ . '/Random.php';
require_once __DIR__ . '/MatrixLibrary.php';


class ARIMA {

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
	public function __construct($originalData , $p , $d , $q)
	{
		$this->armamath= new ARMAMath();
		$this->originalData=$originalData;
		$this->p = $p;
		$this->q = $q;
		$this->d = $d;
		$this->getARIMAmodel($p , $q);
	}
/**
 * Raw data normalization processing: first-order seasonal difference
 * @return Differential data
 */ 
	public function preDealDif()
	{


        $tempData = array();
        if (count($this->originalData) - $this->d >= 0) {

            for ($i = 0; $i < count($this->originalData) - $this->d; $i++) {
                $tempData[$i] = $this->originalData[$i + $this->d] - $this->originalData[$i];
            }
        }else{
            die( "error data to small for d={$this->d} choose another value !");
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
	public function getARIMAmodel($p , $q)
	{
		$stdoriginalData=$this->preDealDif();//Raw data differential processing
		
		$paraType=0;
		$minAIC=9999999;
		$bestModelindex=0;
		$model=array(array($p,$q));//array(0,1),array(1,0),array(1,1),array(0,2),array(2,0),array(2,2),array(1,2),array(2,1));//,array(3,0),array(0,3),array(3,1),array(1,3),array(3,2),array(2,3),array(3,3));//,{4,0},{0,4},{4,1},{1,4},{4,2},{2,4},{4,3},{3,4},{4,4}};
		//Iterate over 8 models and select the model with the smallest AIC value as our model.
		for($i=0;$i<count($model);$i++)
		{
			if($p==0)
			{
				$ma=new MA($stdoriginalData, $q);
				$this->armaARMAcoe=$ma->MAmodel();
				$paraType=1;
			}
			else if($q==0)
			{
				$ar=new AR($stdoriginalData, $p);
				$this->armaARMAcoe=$ar->ARmodel();
				$paraType=2;
			}
			else
			{
				$arma=new ARMA($stdoriginalData, $p, $q);
				$this->armaARMAcoe=$arma->ARMAmodel();
				$paraType=3;
			}
			

			$temp=$this->getmodelAIC($this->armaARMAcoe,$stdoriginalData,$paraType);


			if ($temp<$minAIC)
			{
				$bestModelindex=$i;
				$minAIC=$temp;
				$this->bestarmaARMAcoe=$this->armaARMAcoe;
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
				$err[0]=$random->gauss_ms()*sqrt($maPara[0]);
				
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
				$err[0]=$random->gauss_ms()*sqrt($maPara[0]);
				$sumerr+=($stdoriginalData[$k]-($temp2+$temp))*($stdoriginalData[$k]-($temp2+$temp));
			}
			return ($n-($q-1))*log($sumerr/($n-($q-1)))+($p+$q)*2;
		}
	}

/**
 * Make one step prediction
 * @param p The order of the AR of the ARMA model
 * @param q The order of the MA of the ARMA model
 * @return Predictive value
 */
	public function forecast()
	{
        $p = $this->p;
        $q = $this->q;
		$predict=0;
		$stdoriginalData=$this->preDealDif();
		$n=count($stdoriginalData);
		$temp=0;
		$temp2=0;
		$err=array_fill(0,$q+1,0);//array();
	
		$random=new Random();
		if($p==0)
		{
			$maPara=$this->bestarmaARMAcoe[0];
			for($k=$q;$k<$n;$k++)
			{
				$temp=0;
				for($i=1;$i<=$q;$i++)
				{
					$temp+=$maPara[$i]*$err[$i];
				}
				//Generate noise at various moments
				for($j=$q;$j>0;$j--)
				{
					$err[$j]=$err[$j-1];
				}
				$err[0]=$random->gauss_ms()*sqrt($maPara[0]);
			}
			$predict=(int)($temp); //Generate prediction
		}
		else if($q==0)
		{
			$arPara=$this->bestarmaARMAcoe[0];
			for($k=$p;$k<$n;$k++)
			{
				$temp=0;
				for($i=0;$i<$p;$i++)
				{
					$temp+=$arPara[$i]*$stdoriginalData[$k-$i-1];
				}
			}
			$predict=(int)($temp);
		}
		else
		{

			$arPara=$this->bestarmaARMAcoe[0];
			$maPara=$this->bestarmaARMAcoe[1];
			$err= array_fill(0,$q+1,0); #array();  //$error(t),$error(t-1),$error(t-2)...
			for($k=$p;$k<$n;$k++)
			{
				$temp=0;
				$temp2=0;
				for($i=0;$i<$p;$i++)
				{
					$temp+=$arPara[$i]*$stdoriginalData[$k-$i-1];
				}
			
				for($i=1;$i<$q;$i++)
				{
					$temp2+=$maPara[$i]*$err[$i];
				}
			
				//Generate noise at various moments
				for($j=$q;$j>0;$j--)
				{
					$err[$j]=$err[$j-1];
				}
				
				$err[0]=$random->gauss_ms()*sqrt($maPara[0]);
			}
			
			$predict=(int)($temp2+$temp);
			
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