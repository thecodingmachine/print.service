<?php
namespace Api\v1\Exceptions;

/**
 * Class HtmlToPdfException
 * @package Api\v1\Exceptions
 */
class HtmlToPdfException extends \Exception
{

    /**
     * MergingPdfException constructor.
     */
    public function __construct()
    {
        $this->code = 500;
        $this->message = "HTML to PDF exception: failed to convert the HTML file.";
    }

}