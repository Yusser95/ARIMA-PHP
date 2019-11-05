<?php

require_once __DIR__ . '/ARMAMATH.php';
require_once __DIR__ . '/Random.php';
require_once __DIR__ . '/MatrixLibrary.php';

class AR {
	
	public $stdoriginalData=array();
	public $p;
	public $armamath;
	
	/**
	 * AR model
	 * @param stdoriginalData
	 * @param p //p is the order of the MA model
	 */
	public function __construct($stdoriginalData,$p)
	{
		$this->armamath= new ARMAMath();
		$this->stdoriginalData=$stdoriginalData;
		$this->p=$p;
	}
/**
 * Return AR model parameters
 * @return
 */
	public function ARmodel()
	{
		$v=array();;
		array_push($v , $this->armamath->parcorrCompute($this->stdoriginalData, $this->p, 0));
		return $v;//Autoregressive coefficient
		
		//Also estimate the variance item?
	}
	
}