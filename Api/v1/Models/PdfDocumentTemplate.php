<?php
namespace Api\v1\Models;

use Api\v1\Enumerations\ContentTypeEnumeration;

/**
 * Class PdfDocumentTemplate
 * @package Api\v1\Models
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