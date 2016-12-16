<?php
namespace Api\v1\Models;

use Api\v1\Content\ContentInterface;
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
     * @param ContentInterface|null $template
     * @throws ContentTypeException
     */
    public function __construct(FileService $fileService, int $order, $template)
    {
        parent::__construct($fileService, AbstractTemplate::PDF_CONTENT_TYPE, $order, $template);
    }

}