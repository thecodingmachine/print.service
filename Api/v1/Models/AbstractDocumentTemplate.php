<?php
namespace Api\v1\Models;

/**
 * Class AbstractDocumentTemplate
 * @package Api\v1\Models
 */
abstract class AbstractDocumentTemplate
{

    /**
     * @var string
     */
    protected $url;

    /**
     * @var string
     */
    protected $contentType;

    /**
     * @var array
     */
    protected $data;

    /**
     * @var string
     */
    protected $templateLocalPath;

    /**
     * @var string
     */
    protected $populatedTemplateLocalPath;
    
    /**
     * AbstractDocumentTemplate constructor.
     * @param string $url
     * @param string $contentType
     * @param array|null $data
     */
    protected function __construct(string $url, string $contentType, array $data = null)
    {
        $this->url = $url;
        $this->contentType = $contentType;
        $this->data = $data;
        $this->templateLocalPath = null;
        $this->populatedTemplateLocalPath = null;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getContentType(): string
    {
        return $this->contentType;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @return string
     */
    public function getTemplateLocalPath(): string
    {
        return $this->templateLocalPath;
    }

    /**
     * @param string $templateLocalPath
     */
    public function setTemplateLocalPath(string $templateLocalPath)
    {
        $this->templateLocalPath = $templateLocalPath;
    }

    /**
     * @return string
     */
    public function getPopulatedTemplateLocalPath(): string
    {
        return $this->populatedTemplateLocalPath;
    }

    /**
     * @param string $populatedTemplateLocalPath
     */
    public function setPopulatedTemplateLocalPath(string $populatedTemplateLocalPath)
    {
        $this->populatedTemplateLocalPath = $populatedTemplateLocalPath;
    }

}