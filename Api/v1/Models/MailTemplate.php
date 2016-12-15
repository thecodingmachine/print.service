<?php
namespace Api\v1\Models;

use Api\v1\Content\ContentInterface;
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
     * @var ContentInterface
     */
    private $contentTextUrl;

    /**
     * @var \SplFileInfo
     */
    private $contentTextTemplate;

    /**
     * @var ContentInterface
     */
    private $contentHTMLUrl;

    /**
     * @var \SplFileInfo
     */
    private $contentHTMLTemplate;

    /**
     * MailTemplate constructor.
     * @param FileService $fileService
     * @param int $order
     * @param ContentInterface|null $subject
     * @param ContentInterface|null $contentText
     * @param ContentInterface|null $contentHTML
     */
    public function __construct(FileService $fileService, int $order, $subject, $contentText, $contentHTML)
    {
        parent::__construct($fileService, AbstractTemplate::MAIL_CONTENT_TYPE, $order, $subject);
        $this->contentTextUrl = $contentText;
        $this->contentHTMLUrl = $contentHTML;
    }


    /**
     * Downloads the templates.
     * @throws \Exception
     */
    public function download()
    {
        $this->template = $this->fileService->loadContent($this->fileService->generateRandomFileName('.txt'), $this->templateUrl);

        if (!empty($this->contentTextUrl)) {
            $this->contentTextTemplate = $this->fileService->loadContent($this->fileService->generateRandomFileName('.txt'), $this->contentTextUrl);
        }

        if (!empty($this->contentHTMLUrl)) {
            $this->contentHTMLTemplate = $this->fileService->loadContent($this->fileService->generateRandomFileName('.html'), $this->contentHTMLUrl);
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
     * @throws HtmlToPdfException
     */
    public function convertToPdf()
    {
        throw new HtmlToPdfException("Mail conversion to PDF not supported");
    }
}