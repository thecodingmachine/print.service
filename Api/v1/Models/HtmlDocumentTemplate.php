<?php
namespace Api\v1\Models;

use Api\v1\Enumerations\ContentTypeEnumeration;

/**
 * Class HtmlDocumentTemplate
 * @package Api\v1\Models
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
    private $headerTemplateLocalPath;

    /**
     * @var string
     */
    private $footerTemplateLocalPath;

    /**
     * @var string
     */
    private $populatedHeaderTemplateLocalPath;

    /**
     * @var string
     */
    private $populatedFooterTemplateLocalPath;

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
    public function getHeaderTemplateLocalPath(): string
    {
        return $this->headerTemplateLocalPath;
    }

    /**
     * @param string $headerTemplateLocalPath
     */
    public function setHeaderTemplateLocalPath(string $headerTemplateLocalPath)
    {
        $this->headerTemplateLocalPath = $headerTemplateLocalPath;
    }

    /**
     * @return string
     */
    public function getFooterTemplateLocalPath(): string
    {
        return $this->footerTemplateLocalPath;
    }

    /**
     * @param string $footerTemplateLocalPath
     */
    public function setFooterTemplateLocalPath(string $footerTemplateLocalPath)
    {
        $this->footerTemplateLocalPath = $footerTemplateLocalPath;
    }

    /**
     * @return string
     */
    public function getPopulatedHeaderTemplateLocalPath(): string
    {
        return $this->populatedHeaderTemplateLocalPath;
    }

    /**
     * @param string $populatedHeaderTemplateLocalPath
     */
    public function setPopulatedHeaderTemplateLocalPath(string $populatedHeaderTemplateLocalPath)
    {
        $this->populatedHeaderTemplateLocalPath = $populatedHeaderTemplateLocalPath;
    }

    /**
     * @return string
     */
    public function getPopulatedFooterTemplateLocalPath(): string
    {
        return $this->populatedFooterTemplateLocalPath;
    }

    /**
     * @param string $populatedFooterTemplateLocalPath
     */
    public function setPopulatedFooterTemplateLocalPath(string $populatedFooterTemplateLocalPath)
    {
        $this->populatedFooterTemplateLocalPath = $populatedFooterTemplateLocalPath;
    }
    
}