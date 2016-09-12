<?php
namespace Api\v1\Models\Impl;

use Api\v1\Enumerations\ContentTypeEnumeration;
use Api\v1\Models\AbstractDocumentTemplate;

/**
 * Class HtmlDocumentTemplate
 * @package Api\v1\Models\Impl
 */
class HtmlDocumentTemplate extends AbstractDocumentTemplate
{

    /**
     * @var string
     */
    private $headerUrl;

    /**
     * @var string
     */
    private $footerUrl;

    /**
     * @var string
     */
    private $headerLocalPath;

    /**
     * @var string
     */
    private $footerLocalPath;

    /**
     * HtmlDocumentTemplate constructor.
     * @param string $url
     * @param array|null $data
     * @param string|null $headerUrl
     * @param string|null $footerUrl
     */
    public function __construct(string $url, array $data = null, string $headerUrl = null, string $footerUrl = null)
    {
        $this->headerUrl = $headerUrl;
        $this->footerUrl = $footerUrl;
        parent::__construct($url, ContentTypeEnumeration::HTML, $data);
    }

    /**
     * @return string
     */
    public function getHeaderUrl(): string
    {
        return $this->headerUrl;
    }

    /**
     * @return string
     */
    public function getFooterUrl(): string
    {
        return $this->footerUrl;
    }

    /**
     * @return string
     */
    public function getHeaderLocalPath(): string
    {
        return $this->headerLocalPath;
    }

    /**
     * @param string $headerLocalPath
     */
    public function setHeaderLocalPath(string $headerLocalPath)
    {
        $this->headerLocalPath = $headerLocalPath;
    }

    /**
     * @return string
     */
    public function getFooterLocalPath(): string
    {
        return $this->footerLocalPath;
    }

    /**
     * @param string $footerLocalPath
     */
    public function setFooterLocalPath(string $footerLocalPath)
    {
        $this->footerLocalPath = $footerLocalPath;
    }
    
}