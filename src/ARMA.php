<?php

require_once __DIR__ . '/ARMAMATH.php';
require_once __DIR__ . '/Random.php';
require_once __DIR__ . '/MatrixLibrary.php';

class ARMA {
	
	public $stdoriginalData = array();
	public $p;
	public $q;
	public $armamath;
	
	/**
	 * ARMA model
	 * @param stdoriginalData
	 * @param p,q //p,q is the order of the MA model
	 */
	public function __construct($stdoriginalData,$p,$q)
	{
		$this->armamath= new ARMAMath();
		$this->stdoriginalData=$stdoriginalData;
		$this->p=$p;
		$this->q=$q;	
	}
	public function ARMAmodel()
	{

	
		$arcoe=$this->armamath->parcorrCompute($this->stdoriginalData, $this->p, $this->q);

		$autocorData=$this->getautocorofMA($this->p, $this->q, $this->stdoriginalData, $arcoe);

		$macoe=$this->armamath->getMApara($autocorData, $this->q);//Get the parameter values in the MA model



		$v=array();

		array_push($v, $arcoe);
		array_push($v, $macoe);

		return $v;
	}
	
	/**
	 * Obtain the autocorrelation coefficient of MA
	 * @param p
	 * @param q
	 * @param stdoriginalData
	 * @param autoCordata
	 * @return
	 */
	public function getautocorofMA($p,$q,array $stdoriginalData,array $autoRegress)
	{
		$temp=0;

		$errArray=array_fill(0,count($stdoriginalData)-$p,0);

		$count=0;

		for($i=$p;$i<count($stdoriginalData);$i++)
		{
			$temp=0;
			for($j=1;$j<=$p;$j++)
				$temp+=$stdoriginalData[$i-$j]*$autoRegress[$j-1];
			$errArray[$count++]=$stdoriginalData[$i]-$temp;//Save estimated residual sequence
		}
		return $this->armamath->autocorGrma($errArray, $q);
	}
}