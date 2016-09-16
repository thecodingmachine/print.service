<?php
namespace Api\v1\Models;

use Api\v1\Exceptions\BadRequestException;
use Api\v1\Exceptions\ContentTypeException;
use Api\v1\Exceptions\MediaTypeException;
use Api\v1\Exceptions\MergingHtmlException;
use Api\v1\Exceptions\MergingPdfException;
use Api\v1\Exceptions\MergingWordDocumentException;
use Api\v1\Exceptions\UnprocessableEntityException;
use Api\v1\Services\FileService;

/**
 * Class DocumentsHandler
 * @package Api\v1\Models
 */
class DocumentsHandler
{

    /**
     * @var FileService
     */
    private $fileService;

    /**
     * @var string
     */
    private $mediaType;

    /**
     * @var array<Document>
     */
    private $documents;

    /**
     * @var \SplFileInfo
     */
    private $finalDocument;

    /**
     * DocumentsHandler constructor.
     * @param FileService $fileService
     * @param string $mediaType
     * @param array $postData
     * @throws MediaTypeException
     * @throws BadRequestException
     * @throws ContentTypeException
     */
    public function __construct(FileService $fileService, string $mediaType, array $postData)
    {
        if (empty($mediaType) || $mediaType == "*/*") {
            throw new MediaTypeException();
        }

        if (empty($postData) || !is_array($postData)) {
            throw new BadRequestException();
        }

        $this->fileService = $fileService;
        $this->mediaType = $mediaType;
        $this->documents = [];
        
        // case: merge many documents
        if (count(array_filter(array_keys($postData), "is_string")) > 0) {

            /** @var array $currentDocumentData */
            foreach ($postData as $currentDocumentData) {
                $this->documents[] = $this->parseSingleDocumentData($currentDocumentData);
            }
            
        } else {
            // case: generate single document
            $this->documents[] = $this->parseSingleDocumentData($postData);
        }
    }

    function __destruct()
    {
        // TODO: Implement __destruct() method.
    }

    /**
     * Parses a single document data.
     * @param array $documentData
     * @return Document
     * @throws BadRequestException
     * @throws MediaTypeException
     * @throws ContentTypeException
     */
    private function parseSingleDocumentData(array $documentData): Document
    {
        if (empty($documentData) || !isset($documentData["templates"]) || empty($documentData["templates"]) || !is_array($documentData["templates"])) {
            throw new BadRequestException();
        }

        $templatesData = $documentData["templates"];
        $data = isset($documentData["data"]) && !empty($documentData["data"]) ? $documentData["data"] : null;

        $document = new Document($data);

        /** @var array $currentTemplateData */
        foreach ($templatesData as $currentTemplateData) {

            if (!isset($currentTemplateData["order"]) || empty($currentTemplateData["order"]) || !isset($currentTemplateData["contentType"]) || empty($currentTemplateData["contentType"]) || !isset($currentTemplateData["url"]) || empty($currentTemplateData["url"])) {
                throw new BadRequestException();
            }

            $order = $currentTemplateData["order"];
            $contentType = $currentTemplateData["contentType"];
            $templateUrl = $currentTemplateData["url"];

            switch ($contentType) {
                case AbstractTemplate::HTML_CONTENT_TYPE:
                    if ($this->mediaType == AbstractTemplate::WORD_CONTENT_TYPE) {
                        throw new MediaTypeException();
                    }
                    $headerTemplateUrl = isset($template["headerUrl"]) && !empty($template["headerUrl"]) ? $template["headerUrl"] : null;
                    $footerTemplateUrl = isset($template["footerUrl"]) && !empty($template["footerUrl"]) ? $template["footerUrl"] : null;
                    $document->addTemplate(new HtmlTemplate($this->fileService, $order, $templateUrl, $headerTemplateUrl, $footerTemplateUrl));
                    break;
                case AbstractTemplate::WORD_CONTENT_TYPE:
                    if ($this->mediaType == AbstractTemplate::HTML_CONTENT_TYPE) {
                        throw new MediaTypeException();
                    }
                    $document->addTemplate(new WordTemplate($this->fileService, $order, $templateUrl));
                    break;
                case AbstractTemplate::PDF_CONTENT_TYPE:
                    if ($this->mediaType != $contentType) {
                        throw new MediaTypeException();
                    }
                    $document->addTemplate(new PdfTemplate($this->fileService, $order, $templateUrl));
                    break;
                default:
                    throw new ContentTypeException($contentType);
            }

        }

        return $document;
    }

    /**
     * Generates the final document.
     * @throws MediaTypeException
     * @throws UnprocessableEntityException
     * @throws MergingHtmlException
     * @throws MergingWordDocumentException
     * @throws MergingPdfException
     * @throws \Exception
     */
    public function generate()
    {
        /** @var Document $currentDocument */
        foreach ($this->documents as $currentDocument) {
            $currentDocument->downloadTemplates();
            $currentDocument->populateTemplates();
        }

        switch ($this->mediaType) {
            case AbstractTemplate::HTML_CONTENT_TYPE:
                $this->mergeAsHtml();
                break;
            case AbstractTemplate::WORD_CONTENT_TYPE:
                $this->mergeAsWordDocument();
                break;
            case AbstractTemplate::PDF_CONTENT_TYPE:
                $this->mergeAsPdf();
                break;
            default:
                throw new MediaTypeException();
        }
    }

    /**
     * Merges HTML files.
     * @throws MergingHtmlException
     */
    private function mergeAsHtml()
    {
        $htmlFilesToMerge = [];

        /** @var Document $currentDocument */
        foreach ($this->documents as $currentDocument) {
            $currentTemplates = $currentDocument->getTemplates();

            /** @var HtmlTemplate $currentTemplate */
            foreach ($currentTemplates as $currentTemplate) {
                $htmlFilesToMerge[] = [
                    "header" => $currentTemplate->getPopulatedHeaderTemplate(),
                    "body" => $currentTemplate->getPopulatedTemplate(),
                    "footer" => $currentTemplate->getPopulatedFooterTemplate()
                ];
            }
        }

        $this->finalDocument = $this->fileService->mergeHtmlFiles($htmlFilesToMerge, $this->fileService->generateRandomFileName(".html"));
    }

    /**
     * Merges Word document files.
     * @throws MergingWordDocumentException
     */
    private function mergeAsWordDocument()
    {
        $wordDocumentsToMerge = [];

        /** @var Document $currentDocument */
        foreach ($this->documents as $currentDocument) {
            $currentTemplates = $currentDocument->getTemplates();

            /** @var WordTemplate $currentTemplate */
            foreach ($currentTemplates as $currentTemplate) {
                $wordDocumentsToMerge[] = $currentTemplate->getPopulatedTemplate();
            }
        }

        $this->finalDocument = $this->fileService->mergeWordDocuments($wordDocumentsToMerge, $this->fileService->generateRandomFileName(".docx"));
    }

    /**
     * Merges PDF files.
     * @throws MergingPdfException
     */
    private function mergeAsPdf()
    {
        $pdfFilesToMerge = [];

        /** @var Document $currentDocument */
        foreach ($this->documents as $currentDocument) {
            $currentDocument->convertTemplatesToPdf();
            $currentTemplates = $currentDocument->getTemplates();

            /** @var AbstractTemplate $currentTemplate */
            foreach ($currentTemplates as $currentTemplate) {
                if ($currentTemplate->getContentType() == AbstractTemplate::PDF_CONTENT_TYPE) {
                    $pdfFilesToMerge[] = $currentTemplate->getTemplate();
                } else {
                    /** @var AbstractTemplateToPopulate $currentTemplate */
                    $pdfFilesToMerge[] = $currentTemplate->getPdfTemplate();
                }
            }
        }

        $this->finalDocument = $this->fileService->mergePdfFiles($pdfFilesToMerge, $this->fileService->generateRandomFileName(".pdf"));
    }

    /**
     * @return \SplFileInfo
     */
    public function getFinalDocument(): \SplFileInfo
    {
        return $this->finalDocument;
    }

}