<?php
namespace Api\v1\Models;

use Api\v1\Exceptions\ContentTypeException;
use Api\v1\Exceptions\HtmlToPdfException;
use Api\v1\Exceptions\UnprocessableEntityException;
use Api\v1\Exceptions\WordDocumentToPdfException;
use Api\v1\Services\FileService;

/**
 * Class AbstractTemplateToPopulate
 * @package Api\v1\Models
 */
abstract class AbstractTemplateToPopulate extends AbstractTemplate
{

    /**
     * @var string
     */
    protected $populatedTemplateFileExtension;

    /**
     * @var \SplFileInfo
     */
    protected $populatedTemplate;

    /**
     * @var \SplFileInfo
     */
    protected $pdfTemplate;

    /**
     * AbstractTemplateToPopulate constructor.
     * @param FileService $fileService
     * @param string $contentType
     * @param int $order
     * @param string $templateUrl
     * @throws ContentTypeException
     */
    public function __construct(FileService $fileService, string $contentType, int $order, string $templateUrl)
    {
        parent::__construct($fileService, $contentType, $order, $templateUrl);

        switch ($this->contentType) {
            case AbstractTemplate::HTML_CONTENT_TYPE:
                $this->populatedTemplateFileExtension = ".html";
                break;
            case AbstractTemplate::WORD_CONTENT_TYPE:
                $this->populatedTemplateFileExtension = ".docx";
                break;
            default:
                throw new ContentTypeException($contentType);
        }
    }

    /**
     * Removes populated template from disk.
     */
    function __destruct()
    {
        $this->fileService->removeFileFromDisk($this->populatedTemplate);
        $this->fileService->removeFileFromDisk($this->template);
    }

    /**
     * Populates the template.
     * @param array|null $data
     * @throws UnprocessableEntityException
     */
    public abstract function populate(array $data = null);

    /**
     * Converts the template to PDF.
     * @throws HtmlToPdfException
     * @throws WordDocumentToPdfException
     */
    public abstract function convertToPdf();

    /**
     * @return \SplFileInfo
     */
    public function getPopulatedTemplate(): \SplFileInfo
    {
        return $this->populatedTemplate;
    }

    /**
     * @return \SplFileInfo
     */
    public function getPdfTemplate(): \SplFileInfo
    {
        return $this->pdfTemplate;
    }

}