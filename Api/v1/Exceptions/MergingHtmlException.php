<?php
namespace Api\v1\Exceptions;

/**
 * Class MergingHtmlException
 * @package Api\v1\Exceptions
 */
class MergingHtmlException extends \Exception
{

    /**
     * MergingHtmlException constructor.
     * @param string $details
     */
    public function __construct(string $details)
    {
        $this->code = 500;
        $this->message = "Merging HTML exception: failed to merge HTML files. Details: $details";
    }

}