<?php
namespace Api\v1\Exceptions;

/**
 * Class MediaTypeException
 * @package Api\v1\Exceptions
 */
class MediaTypeException extends \Exception
{
    /**
     * MediaTypeException constructor.
     */
    public function __construct()
    {
        $this->code = 415;
        $this->message = "Media Type non supporté: format de sortie et d'entrée incompatibles. Par exemple: template d'entrée au format html (Twig) et sortie demandée en Docx.";
    }
}