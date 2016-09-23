<?php
namespace Api\v1\Exceptions;

/**
 * Class MergingWordDocumentException
 * @package Api\v1\Exceptions
 */
class MergingWordDocumentException extends \Exception
{

    /**
     * MergingWordDocumentException constructor.
     */
    public function __construct()
    {
        $this->code = 500;
        //$this->message = "Merging Word document exception: failed to merge Word documents.";
        $this->message = "Merging Word document exception: this method is not implemented yet.";
    }

}