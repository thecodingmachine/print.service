<?php
namespace Api\v1\Models;

use Api\v1\Content\ContentInterface;
use Api\v1\Exceptions\ContentTypeException;
use Api\v1\Exceptions\HtmlToPdfException;
use Api\v1\Exceptions\UnprocessableEntityException;
use Api\v1\Services\FileService;

/**
 * Class HtmlTemplate
 * @package Api\v1\Models
 */
class HtmlTemplate extends AbstractTemplateToPopulate
{

    /**
     * @var ContentInterface
     */
    private $headerTemplateUrl;

    /**
     * @var \SplFileInfo
     */
    private $headerTemplate;

    /**
     * @var \SplFileInfo
     */
    private $populatedHeaderTemplate;

    /**
     * @var ContentInterface
     */
    private $footerTemplateUrl;

    /**
     * @var \SplFileInfo
     */
    private $footerTemplate;

    /**
     * @var \SplFileInfo
     */
    private $populatedFooterTemplate;

    /**
     * HtmlTemplate constructor.
     * @param FileService $fileService
     * @param int $order
     * @param ContentInterface $templateUrl
     * @param ContentInterface|null $headerTemplateUrl
     * @param ContentInterface|null $footerTemplateUrl
     * @throws ContentTypeException
     */
    public function __construct(FileService $fileService, int $order, $templateUrl, $headerTemplateUrl = null, $footerTemplateUrl = null)
    {
        parent::__construct($fileService, AbstractTemplate::HTML_CONTENT_TYPE, $order, $templateUrl);
        $this->headerTemplateUrl = $headerTemplateUrl;
        $this->footerTemplateUrl = $footerTemplateUrl;
    }

    /**
     * Removes the populated templates.
     */
    function __destruct()
    {
        $this->fileService->removeFileFromDisk($this->populatedHeaderTemplate);
        $this->fileService->removeFileFromDisk($this->headerTemplate);
        $this->fileService->removeFileFromDisk($this->populatedFooterTemplate);
        $this->fileService->removeFileFromDisk($this->footerTemplate);
        parent::__destruct();
    }

    /**
     * Downloads the templates.
     * @throws \Exception
     */
    public function download()
    {
        if (!empty($this->headerTemplateUrl)) {
            $this->headerTemplate = $this->fileService->loadContent($this->fileService->generateRandomFileName($this->templateFileExtension), $this->headerTemplateUrl);
        }

        if (!empty($this->footerTemplateUrl)) {
            $this->footerTemplate = $this->fileService->loadContent($this->fileService->generateRandomFileName($this->templateFileExtension), $this->footerTemplateUrl);
        }
        
        parent::download();
    }

    /**
     * Populates the template.
     * @param array|null $data
     * @throws UnprocessableEntityException
     */
    public function populate(array $data = null)
    {
        $this->populatedTemplate = $this->fileService->populateTwigFile($this->template, $data, $this->fileService->generateRandomFileName($this->populatedTemplateFileExtension));

        if (!empty($this->headerTemplate)) {
            $this->populatedHeaderTemplate = $this->fileService->populateTwigFile($this->headerTemplate, $data, $this->fileService->generateRandomFileName($this->populatedTemplateFileExtension));
        }

        if (!empty($this->footerTemplate)) {
            $this->populatedFooterTemplate = $this->fileService->populateTwigFile($this->footerTemplate, $data, $this->fileService->generateRandomFileName($this->populatedTemplateFileExtension));
        }
    }

    /**
     * Converts the template to PDF.
     * @throws HtmlToPdfException
     */
    public function convertToPdf()
    {
        $this->pdfTemplate = $this->fileService->convertHtmlFileToPdf($this->populatedTemplate, $this->fileService->generateRandomFileName(".pdf"), $this->populatedHeaderTemplate, $this->populatedFooterTemplate);
    }

    /**
     * @return \SplFileInfo|null
     */
    public function getHeaderTemplate()
    {
        return $this->headerTemplate;
    }

    /**
     * @return \SplFileInfo|null
     */
    public function getPopulatedHeaderTemplate()
    {
        return $this->populatedHeaderTemplate;
    }

    /**
     * @return \SplFileInfo|null
     */
    public function getFooterTemplate()
    {
        return $this->footerTemplate;
    }

    /**
     * @return \SplFileInfo|null
     */
    public function getPopulatedFooterTemplate()
    {
        return $this->populatedFooterTemplate;
    }

}