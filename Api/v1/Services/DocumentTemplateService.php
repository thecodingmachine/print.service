<?php
namespace Api\v1\Services;

use Api\v1\Enumerations\ContentTypeEnumeration;
use Api\v1\Exceptions\NotFoundException;
use Api\v1\Exceptions\UnprocessableEntityException;
use Api\v1\Models\AbstractDocumentTemplate;
use Api\v1\Models\Impl\DocxDocumentTemplate;
use Api\v1\Models\Impl\HtmlDocumentTemplate;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;

/**
 * Class DocumentTemplateService
 * @package Api\v1\Services
 */
class DocumentTemplateService
{

    /**
     * DocumentTemplateService constructor.
     */
    public function __construct()
    {
    }

    /**
     * Downloads document's templates.
     *
     * @param array $documentTemplates
     * @throws NotFoundException
     * @throws RequestException
     */
    public function downloadTemplates(array $documentTemplates)
    {
        /** @var AbstractDocumentTemplate $documentTemplate */
        foreach ($documentTemplates as $documentTemplate) {
            $contentType = $documentTemplate->getContentType();

            if ($contentType == ContentTypeEnumeration::HTML) {
                /** @var HtmlDocumentTemplate $documentTemplate */
                if (!empty($documentTemplate->getHeaderUrl())) {
                    $headerLocalPath = $this->downloadTemplate($documentTemplate->getHeaderUrl(), $contentType);
                    $documentTemplate->setHeaderLocalPath($headerLocalPath);
                }

                if (!empty($documentTemplate->getFooterUrl())) {
                    $footerLocalPath = $this->downloadTemplate($documentTemplate->getFooterUrl(), $contentType);
                    $documentTemplate->setFooterLocalPath($footerLocalPath);
                }
            }

            $localPath = $this->downloadTemplate($documentTemplate->getUrl(), $contentType);
            $documentTemplate->setLocalPath($localPath);
        }
    }

    /**
     * Populates document's templates.
     *
     * @param array $documentTemplates
     * @throws UnprocessableEntityException
     */
    public function populate(array $documentTemplates)
    {
        /** @var AbstractDocumentTemplate $documentTemplate */
        foreach ($documentTemplates as $documentTemplate) {
            $contentType = $documentTemplate->getContentType();

            switch ($contentType) {
                case ContentTypeEnumeration::HTML:
                    /** @var HtmlDocumentTemplate $documentTemplate */
                    $this->populateHtmlDocumentTemplate($documentTemplate);
                    break;
                case ContentTypeEnumeration::DOCX:
                    /** @var DocxDocumentTemplate $documentTemplate */
                    $this->populateDocxDocumentTemplate($documentTemplate);
                    break;
            }

        }
    }

    /**
     * Merges document's templates into one file according to the specified content type.
     *
     * @param array $documentTemplates
     * @param string $accept
     * @return string
     * @throws \Exception
     */
    public function merge(array $documentTemplates, string $accept): string
    {
        switch ($accept) {
            case ContentTypeEnumeration::HTML:
                $finalDocumentPath = $this->mergeAsHtml($documentTemplates);
                break;
            case ContentTypeEnumeration::DOCX:
                $finalDocumentPath = $this->mergeAsDocx($documentTemplates);
                break;
            default:
                $finalDocumentPath = $this->mergeAsPdf($documentTemplates);
        }

        return $finalDocumentPath;
    }

    /**
     * Downloads a document's template.
     *
     * @param string $url
     * @param string $contentType
     * @return string
     * @throws NotFoundException
     * @throws RequestException
     */
    private function downloadTemplate(string $url, string $contentType): string
    {
        switch ($contentType) {
            case ContentTypeEnumeration::HTML:
                $fileExtension = ".twig";
                break;
            case ContentTypeEnumeration::DOCX:
                $fileExtension = ".docx";
                break;
            default:
                $fileExtension = ".pdf";
        }

        try {
            // TODO: checks if template not updated.
            $templatePath = ROOT_PATH . "tmp/" . $this->generateRandomName() . $fileExtension;
            $template = fopen($templatePath, "w");
            $stream = \GuzzleHttp\Psr7\stream_for($template);
            $client = new Client();
            $client->request("GET", $url, [ RequestOptions::SINK => $stream, RequestOptions::SYNCHRONOUS => true ]);

            return $templatePath;
        } catch (RequestException $e) {

            if ($e->getCode() == 404) {
                throw new NotFoundException();
            }

            throw $e;
        }
    }

    /**
     * Generates a random template name.
     *
     * @return string
     */
    private function generateRandomName(): string
    {
        return substr(str_shuffle(str_repeat($x="0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ", ceil(5/strlen($x)))), 1, 5) . time();
    }

    /**
     * Populates a Html document template.
     *
     * @param HtmlDocumentTemplate $template
     * @throws UnprocessableEntityException
     */
    private function populateHtmlDocumentTemplate(HtmlDocumentTemplate $template)
    {
        // TODO: populates html document template using twig.
    }

    /**
     * Populates a Docx document template.
     *
     * @param DocxDocumentTemplate $template
     * @throws UnprocessableEntityException
     */
    private function populateDocxDocumentTemplate(DocxDocumentTemplate $template)
    {
        // TODO: populates docx document template using node and docxtemplater.
    }

    /**
     * Merges document's templates as PdF.
     *
     * @param array $documentTemplates
     * @return string
     * @throws \Exception
     */
    private function mergeAsPdf(array $documentTemplates): string
    {
        // TODO: merge all document templates as PDF. For Hmtl, use pdftkhtml2pdf, for Docx use libreoffice to convert to PDF. Then use PDFTK for the merge itself.
        return "";
    }

    /**
     * Merges document's templates as Html.
     *
     * @param array $documentTemplates
     * @return string
     * @throws \Exception
     */
    private function mergeAsHtml(array $documentTemplates): string
    {
        // TODO: merge all document templates as html. Write in a html file might be the best solution.
        return "";
    }

    /**
     * Merges document's templates as Docx.
     *
     * @param array $documentTemplates
     * @return string
     * @throws \Exception
     */
    private function mergeAsDocx(array $documentTemplates): string
    {
        // TODO: merge all document templates as docx. Uses a script to add pages to a new file or first template.
        return "";
    }

}