<?php namespace Pauly4it\LaraTurk;

use Exception;

class LaraturkException extends Exception {

	protected $errors;

	public function __construct($message, $errors = null, $code = 500, Exception $previous = null)
	{
		$this->errors = $errors;

		parent::__construct($message, $code, $previous);
	}

	public function getErrors()
    {
        return $this->errors;
    }

}