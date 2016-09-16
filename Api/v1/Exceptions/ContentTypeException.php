<?php
namespace Api\v1\Exceptions;

/**
 * Class ContentTypeException
 * @package Api\v1\Exceptions
 */
class ContentTypeException extends \Exception
{

    /**
     * ContentTypeException constructor.
     * @param string $contentType
     */
    public function __construct(string $contentType)
    {
        $this->code = 400;
        $this->message = "Content type exception: '$contentType' is not supported.";
    }

}