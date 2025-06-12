<?php

namespace Asciisd\CyberSource\Exceptions;

use Exception;

class CyberSourceException extends Exception
{
    /**
     * The error data from CyberSource.
     *
     * @var array|null
     */
    protected $errorData;

    /**
     * Create a new CyberSourceException instance.
     *
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     * @param array|null $errorData
     */
    public function __construct($message = "", $code = 0, \Throwable $previous = null, array $errorData = null)
    {
        parent::__construct($message, $code, $previous);
        $this->errorData = $errorData;
    }

    /**
     * Get the error data from CyberSource.
     *
     * @return array|null
     */
    public function getErrorData()
    {
        return $this->errorData;
    }
}
