<?php
namespace Api\v1\Exceptions;

/**
 * Class WordDocumentToPdfException
 * @package Api\v1\Exceptions
 */
class WordDocumentToPdfException extends \Exception
{

    /**
     * WordDocumentToPdfException constructor.
     * @var string $details
     */
    public function __construct(string $details)
    {
        $this->code = 500;
        $this->message =  "Word document to PDF exception: failed to convert the Word document. Details: $details";
    }

}