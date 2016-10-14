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
     * @var string $details
     */
    public function __construct(string $details)
    {
        $this->code = 500;
        $this->message = "HTML to PDF exception: failed to convert the HTML file. Details: $details";
    }

}