<?php
namespace Api\v1\Exceptions;
/**
 * Class MergingExcelDocumentException
 * @package Api\v1\Exceptions
 */
class MergingExcelDocumentException extends \Exception
{
    /**
     * MergingExcelDocumentException constructor.
     */
    public function __construct()
    {
        $this->code = 500;
        $this->message = "Merging Excel document exception: this method is not implemented yet.";
    }
}