<?php

namespace Api\v1\Exceptions;

/**
 * Class UnprocessableEntityException
 * @package Api\v1\Exceptions
 */
class UnprocessableEntityException extends \Exception
{

    /**
     * UnprocessableEntityException constructor.
     * @param string $details
     */
    public function __construct(string $details)
    {
        $this->code = 422;
        $this->message = "Object 'data' of the document seems not to match with tags of the template. Details: $details";
    }

}