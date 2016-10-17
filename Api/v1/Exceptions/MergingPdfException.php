<?php
namespace Api\v1\Exceptions;

/**
 * Class MergingPdfException
 * @package Api\v1\Exceptions
 */
class MergingPdfException extends \Exception
{

    /**
     * MergingPdfException constructor.
     * @var string $details
     */
    public function __construct(string $details)
    {
        $this->code = 500;
        $this->message = "Merging PDF exception: failed to merge PDF files. Details: $details";
    }

}