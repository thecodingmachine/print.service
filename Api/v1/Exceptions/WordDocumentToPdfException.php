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
     */
    public function __construct()
    {
        $this->code = 500;
        $this->message =  "Word document to PDF exception: failed to convert the Word document.";
    }

}