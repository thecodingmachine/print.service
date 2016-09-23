<?php
namespace Api\v1\Exceptions;

/**
 * Class MediaTypeException
 * @package Api\v1\Exceptions
 */
class MediaTypeException extends \Exception
{

    /**
     * MediaTypeException constructor.
     */
    public function __construct()
    {
        $this->code = 415;
        $this->message = "Media type exception: the 'Accept' header value is not supported or does not match with the content types of the templates.";
    }

}