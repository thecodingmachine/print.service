<?php
namespace Api\v1\Models;

use Api\v1\Enumerations\ContentTypeEnumeration;

/**
 * Class DocxDocumentTemplate
 * @package Api\v1\Models
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