<?php
namespace Api\v1\Models;

use Api\v1\Exceptions\ContentTypeException;
use Api\v1\Services\FileService;

/**
 * Class PdfTemplate
 * @package Api\v1\Models
 */
class PdfTemplate extends AbstractTemplate
{

    /**
     * PdfTemplate constructor.
     * @param FileService $fileService
     * @param int $order
     * @param string $templateUrl
     * @throws ContentTypeException
     */
    public function __construct(FileService $fileService, int $order, string $templateUrl)
    {
        parent::__construct($fileService, AbstractTemplate::PDF_CONTENT_TYPE, $order, $templateUrl);
    }

}