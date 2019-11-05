<?php

require_once __DIR__ . '/ARMAMATH.php';
require_once __DIR__ . '/Random.php';
require_once __DIR__ . '/MatrixLibrary.php';

class MA {

	public $stdoriginalData=array();
	public $q;
	public $armamath;
	
	/** MA model
	 * @param stdoriginalData //Preprocessed data
	 * @param q //q is the order of the MA model
	 */
	public function __construct($stdoriginalData,$q)
	{
		$this->armamath= new ARMAMath();
		$this->stdoriginalData=$stdoriginalData;
		$this->q=$q;
	}
/**
 * Return MA model parameters
 * @return
 */
	public function MAmodel()
	{
		$v=array();
		array_push($v , $this->armamath->getMApara($this->armamath->autocorGrma($this->stdoriginalData,$this->q), $this->q));
		return $v;//Get the parameter value in the MA model
	}
		
	
}