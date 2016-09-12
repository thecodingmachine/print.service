<?php
namespace Api\v1\Exceptions;

/**
 * Class ForbiddenException
 * @package Api\v1\Exceptions
 */
class ForbiddenException extends \Exception
{

    /**
     * ForbiddenException constructor.
     */
    public function __construct()
    {
        $this->code = 403;
        $this->message = "Forbidden: authentification réussie mais méthode/opération non autorisée";
    }

}