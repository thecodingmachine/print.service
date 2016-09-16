<?php
namespace Api\v1\Exceptions;

/**
 * Class BadRequestException
 * @package Api\v1\Exceptions
 */
class BadRequestException extends \Exception
{

    /**
     * BadRequestException constructor.
     */
    public function __construct()
    {
        $this->code = 400;
        $this->message = "Bad request exception: malformed request.";
    }

}