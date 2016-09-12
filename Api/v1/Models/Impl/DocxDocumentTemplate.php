<?php
namespace Api\v1\Models\Impl;

use Api\v1\Enumerations\ContentTypeEnumeration;
use Api\v1\Models\AbstractDocumentTemplate;

/**
 * Class DocxDocumentTemplate
 * @package Api\v1\Models\Impl
 */
class DocxDocumentTemplate extends AbstractDocumentTemplate
{

    /**
     * DocxDocumentTemplate constructor.
     * @param string $url
     * @param array|null $data
     */
    public function __construct(string $url, array $data = null)
    {
        parent::__construct($url, ContentTypeEnumeration::DOCX, $data);
    }

}