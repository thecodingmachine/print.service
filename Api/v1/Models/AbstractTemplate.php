<?php
namespace Api\v1\Models;

use Api\v1\Exceptions\ContentTypeException;
use Api\v1\Services\FileService;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

/**
 * Class AbstractTemplate
 * @package Api\v1\Models
 */
abstract class AbstractTemplate
{

    const HTML_CONTENT_TYPE = "text/html";
    const WORD_CONTENT_TYPE = "application/vnd.openxmlformats-officedocument.wordprocessingml.document";
    const PDF_CONTENT_TYPE = "application/pdf";
    const EXCEL_CONTENT_TYPE = "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
    const MAIL_CONTENT_TYPE = "multipart/alternative";
    /**
     * @var FileService
     */
    protected $fileService;

    /**
     * @var string
     */
    protected $contentType;

    /**
     * @var int
     */
    protected $order;

    /**
     * @var string
     */
    protected $templateUrl;

    /**
     * @var string
     */
    protected $templateFileExtension;

    /**
     * @var \SplFileInfo
     */
    protected $template;

    /**
     * AbstractTemplate constructor.
     * @param FileService $fileService
     * @param string $contentType
     * @param int $order
     * @param string $templateUrl
     * @throws ContentTypeException
     */
    public function __construct(FileService $fileService, string $contentType, int $order, string $templateUrl)
    {
        $this->fileService = $fileService;
        $this->contentType = $contentType;
        $this->order = $order;
        $this->templateUrl = $templateUrl;

        switch ($this->contentType) {
            case AbstractTemplate::HTML_CONTENT_TYPE:
                $this->templateFileExtension = ".twig";
                break;
            case AbstractTemplate::WORD_CONTENT_TYPE:
                $this->templateFileExtension = ".docx";
                break;
            case AbstractTemplate::PDF_CONTENT_TYPE:
                $this->templateFileExtension = ".pdf";
                break;
            case AbstractTemplate::EXCEL_CONTENT_TYPE:
                $this->templateFileExtension = ".xlsx";
                break;
            case AbstractTemplate::MAIL_CONTENT_TYPE:
                $this->templateFileExtension = ".twig";
                break;
            default:
                throw new ContentTypeException($contentType);
        }
    }

    public function __destruct()
    {
        $this->fileService->removeFileFromDisk($this->template);
    }

    /**
     * Downloads the template.
     * @throws \Exception
     */
    public function download()
    {
        $this->template = $this->fileService->downloadFile($this->fileService->generateRandomFileName($this->templateFileExtension), $this->templateUrl);
        if ($this->template === null)
            throw new FileNotFoundException('unable to download remote templates.', 404);
    }

    /**
     * @return string
     */
    public function getContentType(): string
    {
        return $this->contentType;
    }

    /**
     * @return string
     */
    public function getTemplateFileExtension(): string
    {
        return $this->templateFileExtension;
    }

    /**
     * @return int
     */
    public function getOrder(): int
    {
        return $this->order;
    }

    /**
     * @return \SplFileInfo
     */
    public function getTemplate(): \SplFileInfo
    {
        return $this->template;
    }

}
