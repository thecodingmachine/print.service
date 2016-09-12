<?php
namespace Api\v1\Models\Impl;

use Api\v1\Enumerations\ContentTypeEnumeration;
use Api\v1\Models\AbstractDocumentTemplate;

/**
 * Class PdfDocumentTemplate
 * @package Api\v1\Models\Impl
 */
class PdfDocumentTemplate extends AbstractDocumentTemplate
{

    /**
     * PdfDocumentTemplate constructor.
     * @param string $url
     */
    public function __construct(string $url)
    {
        parent::__construct($url, ContentTypeEnumeration::PDF);
    }

}