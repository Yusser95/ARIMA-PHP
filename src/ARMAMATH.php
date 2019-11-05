<?php

class ARMAMath
{


	public function avgData(array $dataArray)
	{
		return $this->sumData($dataArray)/count($dataArray);
	}
	
	public function sumData(array $dataArray)
	{
		$sumData=0;
		for($i=0;$i<count($dataArray);$i++)
		{
			$sumData+=$dataArray[$i];
		}
		return $sumData;
	}
	
	public function stderrData(array $dataArray)
	{
		return sqrt($this->varerrData($dataArray));
	}
	
	public function varerrData(&$dataArray)
	{
		$variance=0;
		$avgsumData=$this->avgData($dataArray);
		
		for($i=0;$i<count($dataArray);$i++)
		{
			$dataArray[$i]-=$avgsumData;
			$variance+=$dataArray[$i]*$dataArray[$i];
		}
		return $variance/count($dataArray);//variance error;
	}
	
	/**
	 * Calculate autocorrelation function Tho(k)=Grma(k)/Grma(0)
	 * @param $dataArray Sequence
	 * @param $order order
	 * @return
	 */
	public function autocorData(array &$dataArray,$order)
	{


		$autoCor=array_fill(0,$order + 1,0.0);

		$varData=$this->varerrData($dataArray);//Standardized variance

		for($i=0;$i<=$order;$i++)
		{
			$autoCor[$i]=0;
			for($j=0;$j<count($dataArray)-$i;$j++)
			{
				$autoCor[$i]+= ($dataArray[$j+$i]*$dataArray[$j]);

			}
			$autoCor[$i] /= count($dataArray);

            if ($varData != 0) {
                $autoCor[$i] /= $varData;
            }

		}
		return $autoCor;
	}
	
/**
 * Grma
 * @param $dataArray
 * @param order
 * @return Sequence autocorrelation coefficient
 */
	public function autocorGrma(array $dataArray,$order)
	{
		$autoCor=array_fill(0,$order+1,0);
		for($i=0;$i<=$order;$i++)
		{
			$autoCor[$i]=0;
			for($j=0;$j<count($dataArray)-$i;$j++)
			{
				$autoCor[$i] += $dataArray[$j+$i] * $dataArray[$j];
			}
			if((count($dataArray)-$i) != 0) {
                $autoCor[$i] /= (count($dataArray) - $i);
            }
			
		}


		return $autoCor;
	}
	
/**
 * Partial autocorrelation coefficient
 * @param $dataArray
 * @param order
 * @return
 */
	public function parautocorData(array $dataArray,$order)
	{
		$parautocor=array_fill(0,$order,0);
		
		for($i=1;$i<=$order;$i++)
	    {
			$parautocor[$i-1]=$this->parcorrCompute($dataArray, $i,0)[$i-1];
	    }
		return $parautocor;
	}
/**
 * Generate a Toplize matrix
 * @param $dataArray
 * @param order
 * @return
 */
	public function toplize(array $dataArray,$order)
	{//Return totopl two-dimensional array
		$toplizeMatrix=array_fill(0,$order,0);
		for($i=0;$i<count($toplizeMatrix);$i++){
			$toplizeMatrix[$i]=array_fill(0,$order,0);
		}
		$atuocorr=$this->autocorData($dataArray,$order);

		for($i=1;$i<=$order;$i++)
		{
			$k=1;
			for($j=$i-1;$j>0;$j--)
			{
				$toplizeMatrix[$i-1][$j-1]=$atuocorr[$k++];
			}
			$toplizeMatrix[$i-1][$i-1]=1;
			$kk=1;
			for($j=$i;$j<$order;$j++)
			{
				$toplizeMatrix[$i-1][$j]=$atuocorr[$kk++];
			}
		}
		return $toplizeMatrix;
	}

	/**
	 * Solve the parameters of the MA model
	 * @param autocorData
	 * @param q
	 * @return
	 */


	public function getMApara(array $autocorData,$q)
	{



		$maPara=array_fill(0,$q+1,0);//The first one stores the noise parameters, and the next q stores the ma parameters.sigma2,ma1,ma2...
		$tempmaPara=$maPara;
		$temp=0.0;
		$iterationFlag=true;
		//Solution equation
		//Iterative method for solving equations
		$maPara[0]=1;//initialization


		$counter = 0;
		while($iterationFlag)
		{

			for($i=1;$i<count($maPara);$i++)
			{
				$temp+=($maPara[$i]*$maPara[$i]);

			}
            $maPara[0]=($autocorData[0]/(1+$temp));
		
			for($i=1;$i<count($maPara);$i++)
			{
				$temp=0;
				for($j=1;$j<count($maPara)-$i;$j++)
				{
					$temp+=($maPara[$j]*$maPara[$j+$i]);

				}
                if ($maPara[0]-$temp != 0)
                    $maPara[$i]= -($autocorData[$i]/$maPara[0]-$temp);

			}

            break;
		}
		
		return $maPara;
	}
	/**
	 * Calculated autoregressive coefficient
	 * @param $dataArray
	 * @param p
	 * @param q
	 * @return
	 */
	public function parcorrCompute(array $dataArray,$p,$q)
	{

		$toplizeArray=array_fill(0,$p,0);
		for($i=0;$i<count($toplizeArray);$i++){
			$toplizeArray[$i]=array_fill(0,$p,0);
		}//P-th order toplize matrix;

		$autocorrF=$this->autocorGrma($dataArray, $p+$q);//Returns the number of autocorrelation coefficients for the $p+$q order

		$atuocorr=$this->autocorData($dataArray,$p+$q);//Returns the autocorrelation function of $p+$q order


		for($i=1;$i<=$p;$i++)
		{
			$k=1;
			for($j=$i-1;$j>0;$j--)
			{
				$toplizeArray[$i-1][$j-1]=$atuocorr[$q+$k++];
			}
			$toplizeArray[$i-1][$i-1]=$atuocorr[$q];
			$kk=1;
			for($j=$i;$j<$p;$j++)
			{
				$toplizeArray[$i-1][$j]=$atuocorr[$q+$kk++];
			}
		}

		
	    $toplizeMatrix = $toplizeArray;//Convert from a two-digit array to a two-dimensional matrix
	    $matrixLibrary = new MatrixLibrary();
    	$toplizeMatrixinverse = $matrixLibrary->inverseMatrix($toplizeMatrix);

	    $temp=array_fill(0,$p,0);
	    for($i=1;$i<=$p;$i++)
	    {
	    	$temp[$i-1]=$atuocorr[$q+$i];
	    }

		$autocorrMatrix=array($temp);

		$parautocorDataMatrix = $matrixLibrary->multibly_matrices($autocorrMatrix,$toplizeMatrixinverse );

        $parautocorDataMatrix = $parautocorDataMatrix[0];

		
		$result=array_fill(0,count($parautocorDataMatrix)+1,0.0);

		for($i=0;$i<count($parautocorDataMatrix);$i++)
		{
			$result[$i]=$parautocorDataMatrix[$i];
		}


		//Estimate sigmat2
		$sum2=0;
		for($i=0;$i<$p;$i++)
			for($j=0;$j<$p;$j++)
			{
				$sum2+=$result[$i]*$result[$j]*$autocorrF[abs($i-$j)];
			}
		$result[count($result)-1]=$autocorrF[0]-$sum2; //The last result of the result array stores the interference estimate

			return $result;   //The last one that returns 0 is the partial autocorrelation coefficient of k-order pcorr[k]=return value
	}

	
	}