<?php
namespace Api\v1\Exceptions;
/**
 * Class ExcelDocumentToPdfException
 * @package Api\v1\Exceptions
 */
class ExcelDocumentToPdfException extends \Exception
{
    /**
     * ExcelDocumentToPdfException constructor.
     * @var string $details
     */
    public function __construct(string $details)
    {
        $this->code = 500;
        $this->message =  "Excel document to PDF exception: failed to convert the Excel document. Details: $details";
    }
}