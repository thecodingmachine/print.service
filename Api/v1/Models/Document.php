<?php
namespace Api\v1\Models;

use Api\v1\Exceptions\BadRequestException;
use Api\v1\Exceptions\HtmlToPdfException;
use Api\v1\Exceptions\UnprocessableEntityException;
use Api\v1\Exceptions\WordDocumentToPdfException;
use Api\v1\Exceptions\ExcelDocumentToPdfException;
use Api\v1\Services\FileService;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

/**
 * Class Document
 * @package Api\v1\Models
 */
class Document
{

    const LINK_DATA_TYPE = "link";
    const IMAGE_DATA_TYPE = "image";

    /**
     * @var FileService
     */
    private $fileService;

    /**
     * @var array<AbstractTemplate>
     */
    private $templates;

    /**
     * @var array
     */
    private $data;

    /**
     * @var array
     */
    private $formattedDataForHtml;

    /**
     * @var array
     */
    private $formattedDataForWordDocument;
    /**
     * @var array
     */
    private $formattedDataForExcelDocument;
    /**
     * @var array<\SplFileInfo>
     */
    private $images;

    /**
     * Document constructor.
     * @param FileService $fileService
     * @param array $data
     */
    public function __construct(FileService $fileService, array $data = [])
    {
        $this->fileService = $fileService;
        $this->templates = [];
        $this->data = $data;
        $this->formattedDataForHtml = [];
        $this->formattedDataForWordDocument = [];
        $this->formattedDataForExcelDocument = [];
        $this->images = [];
    }

    /**
     * Adds a template in the array of templates with the correct order.
     * @param AbstractTemplate $template
     * @throws BadRequestException
     * @throws \Exception
     */
    public function addTemplate(AbstractTemplate $template)
    {
        $this->templates[] = $template;
        usort($this->templates, function(AbstractTemplate $templateA,  AbstractTemplate $templateB) {
            return $templateA->getOrder() > $templateB->getOrder();
        });

        if ($template->getContentType() == AbstractTemplate::HTML_CONTENT_TYPE && empty($this->formattedDataForHtml)) {
            $this->formattedDataForHtml = $this->formatDataForHtml($this->data);
        } else if ($template->getContentType() == AbstractTemplate::WORD_CONTENT_TYPE && empty($this->formattedDataForWordDocument)) {
            $this->formattedDataForWordDocument = $this->formatDataForWordDocument($this->data);
        } else if ($template->getContentType() == AbstractTemplate::EXCEL_CONTENT_TYPE && empty($this->formattedDataForExcelDocument)) {
            $this->formattedDataForExcelDocument = $this->formatDataForExcelDocument($this->data);
        }
    }

    /**
     * Parses the data and format them for populating HTML templates.
     * @param array $data
     * @return array
     * @throws BadRequestException
     */
    private function formatDataForHtml(array $data): array
    {
        $formattedData = [];

        foreach ($data as $key => $currentData) {
            if (!is_array($currentData)) {
                $formattedData[$key] = $currentData;
                continue;
            }

            if (isset($currentData["type"])) {

                if (empty($currentData["type"])) {
                    throw new BadRequestException();
                }

                switch ($currentData["type"]) {
                    case Document::LINK_DATA_TYPE:
                        if (!isset($currentData["url"]) || empty($currentData["url"])) {
                            throw new BadRequestException();
                        }

                        if (isset($currentData["text"]) && empty($currentData["text"])) {
                            throw new BadRequestException();
                        }
                        $formattedData[$key] = isset($currentData["text"]) ? ["text" => $currentData["text"], "url" => $currentData["url"]] : $currentData["url"];
                        break;
                    case Document::IMAGE_DATA_TYPE:
                        if (!isset($currentData["url"]) || empty($currentData["url"])) {
                            throw new BadRequestException();
                        }
                        $formattedData[$key] = $currentData["url"];
                        break;
                    default:
                        throw new BadRequestException();
                }
                continue;
            }

            $formattedData[$key] = $this->formatDataForHtml($currentData);
        }

        return $formattedData;
    }

    /**
     * Parses the data looking for images to download and format the data for populating Word templates.
     * @param array
     * @return array
     * @throws BadRequestException
     * @throws \Exception
     */
    private function formatDataForWordDocument(array $data): array
    {
        $formattedData = [];

        foreach ($data as $key => $currentData) {
            if (!is_array($currentData)) {
                $formattedData[$key] = $currentData;
                continue;
            }

            if (isset($currentData["type"])) {

                if (empty($currentData["type"])) {
                    throw new BadRequestException();
                }

                switch ($currentData["type"]) {
                    case Document::LINK_DATA_TYPE:
                        if (!isset($currentData["url"]) || empty($currentData["url"])) {
                            throw new BadRequestException();
                        }

                        if (isset($currentData["text"]) && empty($currentData["text"])) {
                            throw new BadRequestException();
                        }
                        $formattedData[$key] = isset($currentData["text"]) ? [ "text" => $currentData["text"], "url" => $currentData["url"] ] : $currentData["url"];
                        break;
                    case Document::IMAGE_DATA_TYPE:
                        if (!isset($currentData["url"]) || empty($currentData["url"])) {
                            throw new BadRequestException();
                        }
                        $file = $this->downloadImage($currentData["url"]);
                        $formattedData[$key] = $file->getRealPath();
                        break;
                    default:
                        throw new BadRequestException();
                }
                continue;
            }

            $formattedData[$key] = $this->formatDataForWordDocument($currentData);
        }

        return $formattedData;
    }

    /**
     * Parses the data looking for images to download and format the data for populating Excel templates.
     * @param array
     * @return array
     * @throws BadRequestException
     * @throws \Exception
     */
    private function formatDataForExcelDocument(array $data): array
    {
        $formattedData = [];
        foreach ($data as $key => $currentData) {
            if (!is_array($currentData)) {
                $formattedData[$key] = $currentData;
                continue;
            }
            if (isset($currentData["type"])) {
                if (empty($currentData["type"])) {
                    throw new BadRequestException();
                }
                switch ($currentData["type"]) {
                    case Document::LINK_DATA_TYPE:
                        if (!isset($currentData["url"]) || empty($currentData["url"])) {
                            throw new BadRequestException();
                        }
                        if (isset($currentData["text"]) && empty($currentData["text"])) {
                            throw new BadRequestException();
                        }
                        $formattedData[$key] = isset($currentData["text"]) ? [ "text" => $currentData["text"], "url" => $currentData["url"] ] : $currentData["url"];
                        break;
                    case Document::IMAGE_DATA_TYPE:
                        if (!isset($currentData["url"]) || empty($currentData["url"])) {
                            throw new BadRequestException();
                        }
                        $file = $this->downloadImage($currentData["url"]);
                        $formattedData[$key] = $file->getRealPath();
                        break;
                    default:
                        throw new BadRequestException();
                }
                continue;
            }
            $formattedData[$key] = $this->formatDataForExcelDocument($currentData);
        }
        return $formattedData;
    }
    /**
     * Downloads an image and adds it to the array of images.
     * @param string $url
     * @return \SplFileInfo
     * @throws \Exception
     */
    private function downloadImage(string $url): \SplFileInfo
    {
        $file = $this->fileService->downloadFile($this->fileService->generateRandomFileName(), $url, true);
        if ($file === null){
            throw new FileNotFoundException("unable to download remote image '$url'", 404);
        }

        return $file;
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

            switch ($contentType) {
                case AbstractTemplate::HTML_CONTENT_TYPE:
                    /** @var HtmlTemplate $currentTemplate */
                    $currentTemplate->populate($this->formattedDataForHtml);
                    break;
                case AbstractTemplate::WORD_CONTENT_TYPE:
                    /** @var WordTemplate $currentTemplate */
                    $currentTemplate->populate($this->formattedDataForWordDocument);
                    break;
                case AbstractTemplate::EXCEL_CONTENT_TYPE:
                    /** @var ExcelTemplate $currentTemplate */
                    $currentTemplate->populate($this->formattedDataForExcelDocument);
                    break;
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
     * @return array<AbstractTemplate>
     */
    public function getTemplates(): array
    {
        return $this->templates;
    }

}
