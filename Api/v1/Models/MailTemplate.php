<?php
namespace Api\v1\Models;

use Api\v1\Exceptions\ContentTypeException;
use Api\v1\Exceptions\HtmlToPdfException;
use Api\v1\Exceptions\UnprocessableEntityException;
use Api\v1\Exceptions\WordDocumentToPdfException;
use Api\v1\Services\FileService;
use GuzzleHttp\Psr7\MultipartStream;
use GuzzleHttp\Psr7\Response;

/**
 * Class MailTemplate
 * @package Api\v1\Models
 */
class MailTemplate extends AbstractTemplateToPopulate
{
    const MULTIPART_BOUNDARY = "XZY";

    /**
     * @var string
     */
    private $contentTextUrl;

    /**
     * @var \SplFileInfo
     */
    private $contentTextTemplate;

    /**
     * @var string
     */
    private $contentHTMLUrl;

    /**
     * @var \SplFileInfo
     */
    private $contentHTMLTemplate;

    /**
     * PdfTemplate constructor.
     * @param FileService $fileService
     * @param int $order
     * @param string $templateUrl
     * @throws ContentTypeException
     */
    public function __construct(FileService $fileService, int $order, string $subjectUrl, string $contentTextUrl, string $contentHTMLUrl)
    {
        parent::__construct($fileService, AbstractTemplate::MAIL_CONTENT_TYPE, $order, $subjectUrl);
        $this->contentTextUrl = $contentTextUrl;
        $this->contentHTMLUrl = $contentHTMLUrl;
    }


    /**
     * Downloads the templates.
     * @throws \Exception
     */
    public function download()
    {
        $this->template = $this->fileService->downloadFile($this->fileService->generateRandomFileName('.txt'), $this->templateUrl);

        if (!empty($this->contentTextUrl)) {
            $this->contentTextTemplate = $this->fileService->downloadFile($this->fileService->generateRandomFileName('.txt'), $this->contentTextUrl);
        }

        if (!empty($this->contentHTMLUrl)) {
            $this->contentHTMLTemplate = $this->fileService->downloadFile($this->fileService->generateRandomFileName('.html'), $this->contentHTMLUrl);
        }
    }

    /**
     * Populates the template.
     * @param array|null $data
     * @throws UnprocessableEntityException
     */
    public function populate(array $data = null)
    {
        $subject = $this->fileService->processTwigFile($this->template, $data);

        if (!empty($this->contentTextTemplate)) {
            $contentText = $this->fileService->processTwigFile($this->contentTextTemplate, $data);
        } else {
            $contentText = null;
        }

        if (!empty($this->contentHTMLTemplate)) {
            $contentHTML = $this->fileService->processTwigFile($this->contentHTMLTemplate, $data);
        } else {
            $contentHTML = null;
        }


        $filename = $this->fileService->generateRandomFileName('.txt');
        $filepath = $this->fileService->getAbsolutePath($filename);
        $stream = fopen($filepath, "w");
        $this->writeContents($stream, $subject, $contentText, $contentHTML);
        fclose($stream);

        $this->populatedTemplate = new \SplFileInfo($filepath);
    }

    /**
     * @param resource $handle
     * @param string $subject
     * @param string $contentText
     * @param string $contentHTML
     */
    private function writeContents($handle, $subject, $contentText, $contentHTML)
    {
        $separator = '--'.self::MULTIPART_BOUNDARY;

        fwrite($handle, <<<EOL
$separator
Content-Disposition: name="subject"
Content-Type: text/plain

$subject

EOL
        );

        if ($contentText) {
            fwrite($handle, <<<EOL
$separator
Content-Disposition: name="contentText"
Content-Type: text/plain

$contentText

EOL
            );
        }

        if ($contentHTML) {
            fwrite($handle, <<<EOL
$separator
Content-Disposition: name="contentHTML"
Content-Type: text/html

$contentHTML

EOL
            );
        }

        fwrite($handle, "$separator--");
    }

    /**
     * Converts the template to PDF.
     * @throws HtmlToPdfException
     * @throws WordDocumentToPdfException
     */
    public function convertToPdf()
    {
        // TODO: Implement convertToPdf() method.
    }
}