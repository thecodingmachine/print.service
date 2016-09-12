<?php
namespace Api\v1\Exceptions;

/**
 * Class UnprocessableEntityException
 * @package Api\v1\Exceptions
 */
class UnprocessableEntityException extends \Exception
{
    /**
     * UnprocessableEntityException constructor.
     */
    public function __construct()
    {
        $this->code = 422;
        $this->message = "Unprocessable Entity: objet de données `data` passé dans la requête impossible à traiter dans le template.";
    }
}