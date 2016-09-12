<?php
namespace Api\v1\Exceptions;

/**
 * Class NotFoundException
 * @package Api\v1\Exceptions
 */
class NotFoundException extends \Exception
{
    /**
     * NotFoundException constructor.
     */
    public function __construct()
    {
        $this->code = 404;
        $this->message = "Not Found: impossible de charger les templates distants fournis dans la requête";
    }
}