<?php
namespace Api\v1\Exceptions;

/**
 * Class UnauthorizedException
 * @package Api\v1\Exceptions
 */
class UnauthorizedException extends \Exception
{
    /**
     * UnauthorizedException constructor.
     */
    public function __construct()
    {
        $this->code = 401;
        $this->message = "Unauthorized: appel non authentifié ou détails d'authentification non valides";
    }
}