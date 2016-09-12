<?php
namespace Api\v1\Services;

use Api\v1\Enumerations\ContentTypeEnumeration;
use Api\v1\Exceptions\BadRequestException;
use Api\v1\Exceptions\ForbiddenException;
use Api\v1\Exceptions\MediaTypeException;
use Api\v1\Models\Impl\DocxDocumentTemplate;
use Api\v1\Models\Impl\HtmlDocumentTemplate;
use Api\v1\Models\Impl\PdfDocumentTemplate;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class RequestParserService
 * @package Api\v1\Services
 */
class RequestParserService
{

    /**
     * RequestParserService constructor.
     */
    public function __construct()
    {
    }

    /**
     * Primary check of a request.
     *
     * @param ServerRequestInterface $request
     * @param bool $isMerge
     * @return array
     * @throws BadRequestException
     * @throws MediaTypeException
     * @throws ForbiddenException
     */
    public function prepare(ServerRequestInterface $request, bool $isMerge = false): array
    {
        $accept = $request->getHeaderLine("Accept");

        if (empty($accept) || $accept == "*/*") {
            throw new MediaTypeException();
        }

        $body = $request->getParsedBody();

        if (empty($body)) {
            throw new BadRequestException();
        }

        if ((!$isMerge && is_array($body)) || ($isMerge && !is_array($body))) {
            throw new ForbiddenException();
        }

        return [
            "accept" => $accept,
            "body" => $body
        ];
    }

    /**
     * Parses request body.
     *
     * @param array $prepared
     * @param bool $isMerge
     * @return array
     * @throws BadRequestException
     * @throws MediaTypeException
     */
    public function parse(array $prepared, bool $isMerge = false): array
    {
        $body = $prepared["body"];
        $accept = $prepared["accept"];

        if (!$isMerge) {

            $parsed = $this->parseSingle($body, $accept);

        } else {

            $parsed = [];

            /** @var array $documentData */
            foreach ($body as $documentData) {
                $parsed = array_merge($parsed, $this->parseSingle($documentData, $accept));
            }

        }

        return $parsed;
    }

    /**
     * Parses a single document data.
     *
     * @param array $single
     * @param string $accept
     * @return array
     * @throws BadRequestException
     * @throws MediaTypeException
     */
    private function parseSingle(array $single, string $accept): array
    {
        $documentTemplates = [];

        if (empty($single) || !isset($single["templates"]) || empty($single["templates"]) || !is_array($single["templates"])) {
            throw new BadRequestException();
        }

        $templates = $single["templates"];
        $data = isset($single["data"]) && !empty($single["data"]) ? $single["data"] : null;

        /** @var array $template */
        foreach ($templates as $template) {

            if (!isset($template["contentType"]) || empty($template["contentType"]) || !isset($template["url"]) || empty($template["url"])) {
                throw new BadRequestException();
            }

            $contentType = $template["contentType"];
            $url = $template["url"];

            switch ($contentType) {
                case ContentTypeEnumeration::PDF:
                    if ($accept != $contentType) {
                        throw new MediaTypeException();
                    }

                    $documentTemplates[] = new PdfDocumentTemplate($url);
                    break;
                case ContentTypeEnumeration::HTML:
                    if ($accept == ContentTypeEnumeration::DOCX) {
                        throw new MediaTypeException();
                    }

                    $headerUrl = isset($template["headerUrl"]) && !empty($template["headerUrl"]) ? $template["headerUrl"] : null;
                    $footerUrl = isset($template["footerUrl"]) && !empty($template["footerUrl"]) ? $template["footerUrl"] : null;

                    $documentTemplates[] = new HtmlDocumentTemplate($url, $data, $headerUrl, $footerUrl);
                    break;
                case ContentTypeEnumeration::DOCX:
                    if ($accept == ContentTypeEnumeration::HTML) {
                        throw new MediaTypeException();
                    }

                    $documentTemplates[] = new DocxDocumentTemplate($url, $data);
                    break;
                default:
                    throw new MediaTypeException();
            }

        }

        return $documentTemplates;
    }
}