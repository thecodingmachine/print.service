<?php
namespace Api\v1\Services;
use Api\v1\Enumerations\ContentTypeEnumeration;
use Api\v1\Exceptions\NotFoundException;
use Api\v1\Models\AbstractDocumentTemplate;
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
     * @var Client
     */
    private $client;

    /**
     * DocumentTemplateService constructor.
     */
    public function __construct()
    {
        $this->client = new Client();
    }

    /**
     * Downloads documents' templates.
     *
     * @param array $documentTemplates
     * @return array
     * @throws NotFoundException
     * @throws RequestException
     */
    public function downloadTemplates(array $documentTemplates): array
    {
        /** @var AbstractDocumentTemplate $documentTemplate */
        foreach ($documentTemplates as $documentTemplate) {
            $contentType = $documentTemplate->getContentType();

            if ($contentType == ContentTypeEnumeration::HTML) {
                /** @var HtmlDocumentTemplate $documentTemplate */
                if (!empty($documentTemplate->getHeaderUrl())) {
                    $headerLocalPath = $this->downloadTemplate($documentTemplate->getHeaderUrl());
                    $documentTemplate->setHeaderLocalPath($headerLocalPath);
                }

                if (!empty($documentTemplate->getFooterUrl())) {
                    $footerLocalPath = $this->downloadTemplate($documentTemplate->getFooterUrl());
                    $documentTemplate->setFooterLocalPath($footerLocalPath);
                }
            }

            $localPath = $this->downloadTemplate($documentTemplate->getUrl());
            $documentTemplate->setLocalPath($localPath);
        }

        return $documentTemplates;
    }

    public function populate(array $documentTemplates)
    {
        return $documentTemplates;
    }

    /**
     * Downloads a document's template.
     *
     * @param string $url
     * @return string
     * @throws NotFoundException
     * @throws RequestException
     */
    private function downloadTemplate(string $url): string
    {
        try {
            $templatePath = "/tmp/" .  $this->generateRandomName();
            $template = fopen($templatePath, "w");
            $stream = \GuzzleHttp\Psr7\stream_for($template);
            $this->client->request("GET", $url, [ RequestOptions::SINK => $stream, RequestOptions::SYNCHRONOUS => true ]);

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

}