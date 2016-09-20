<?php
namespace Api\v1\Models;

use Api\v1\Exceptions\HtmlToPdfException;
use Api\v1\Exceptions\UnprocessableEntityException;
use Api\v1\Exceptions\WordDocumentToPdfException;
use Api\v1\Services\FileService;

/**
 * Class Document
 * @package Api\v1\Models
 */
class Document
{

    /**
     * @var FileService
     */
    private $fileService;

    /**
     * @var \SplPriorityQueue<AbstractTemplate>
     */
    private $templates;

    /**
     * @var array
     */
    private $data;

    /**
     * @var array<\SplFileInfo>
     */
    private $images;

    /**
     * Document constructor.
     * @param FileService $fileService
     * @param array|null $data
     * @throws \Exception
     */
    public function __construct(FileService $fileService, array $data = null)
    {
        $this->fileService = $fileService;
        $this->templates = new \SplPriorityQueue();
        $this->data = $data;
        $this->images = [];

        $this->parseDataForImages($this->data);
    }

    /**
     * Removes images from disk.
     */
    function __destruct()
    {
        // TODO: Implement __destruct() method.
    }

    /**
     * Parses the data looking for images to download.
     * @param array $data
     * @throw \Exception
     */
    private function parseDataForImages(array $data)
    {
        foreach ($data as $key => $currentData) {
            if (!is_array($currentData)) {
                continue;
            }

            if (isset($currentData["url"]) && !empty($currentData["url"]) && isset($currentData["ext"]) && !empty($currentData["ext"])) {
                $file = $this->downloadImage($currentData["url"], $currentData["ext"]);
                $data[$key] = $file->getRealPath();
                continue;
            }

            $this->parseDataForImages($currentData);
        }
    }

    /**
     * Download an image and adds it to the array of images.
     * @param string $url
     * @param string $ext
     * @return \SplFileInfo
     * @throws \Exception
     */
    private function downloadImage(string $url, string $ext): \SplFileInfo
    {
        // TODO add cache.
        $file = $this->fileService->downloadFile($this->fileService->generateRandomFileName($ext), $url);
        $images[] = $file;

        return $file;
    }

    /**
     * Adds a template in the array of templates with the correct order.
     * @param AbstractTemplate $template
     */
    public function addTemplate(AbstractTemplate $template)
    {
        $this->templates->insert($template, $template->getOrder());
    }

    /**
     * Downloads the templates.
     * @throws \Exception
     */
    public function downloadTemplates()
    {
        /** @var AbstractTemplate $currentTemplate */
        foreach ($this->templates as $currentTemplate) {
            $currentTemplate->download();
        }
    }

    /**
     * Populates the templates.
     * @throws UnprocessableEntityException
     * @throws \Exception
     */
    public function populateTemplates()
    {
        /** @var AbstractTemplate $currentTemplate */
        foreach ($this->templates as $currentTemplate) {
           $contentType = $currentTemplate->getContentType();

            if ($contentType != AbstractTemplate::PDF_CONTENT_TYPE) {
                /** @var AbstractTemplateToPopulate $currentTemplate */
                $currentTemplate->populate($this->data);
            }
        }
    }

    /**
     * Converts the templates to PDF.
     * @throws HtmlToPdfException
     * @throws WordDocumentToPdfException
     */
    public function convertTemplatesToPdf()
    {
        /** @var AbstractTemplate $currentTemplate */
        foreach ($this->templates as $currentTemplate) {
            $contentType = $currentTemplate->getContentType();

            if ($contentType != AbstractTemplate::PDF_CONTENT_TYPE) {
                /** @var AbstractTemplateToPopulate $currentTemplate */
                $currentTemplate->convertToPdf();
            }
        }
    }

    /**
     * @return \SplPriorityQueue<AbstractTemplate>
     */
    public function getTemplates(): \SplPriorityQueue
    {
        return $this->templates;
    }

}