<?php

namespace ConstMacro;


class Lazy
{

	/** @var callable */
	private $method;

	/** @var array */
	private $args;


	/**
	 * @param callable
	 * @param mixed..
	 * @return void
	 **/
	public function __construct(/*callable*/ $method)
	{
		$this->method = $method;
		$this->args = array_slice(func_get_args(), 1);
	}


	/**
	 * @return mixed
	 **/
	public function invoke()
	{
		$this->args = Runtime::value($this->args);
		return call_user_func_array($this->method, $this->args);
	}

}
