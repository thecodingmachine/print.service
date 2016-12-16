<?php
namespace Api\v1\Models;

use Api\v1\Content\ContentInterface;
use Api\v1\Exceptions\ContentTypeException;
use Api\v1\Exceptions\HtmlToPdfException;
use Api\v1\Exceptions\UnprocessableEntityException;
use Api\v1\Exceptions\WordDocumentToPdfException;
use Api\v1\Exceptions\ExcelDocumentToPdfException;
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
     * @param ContentInterface|null $template
     * @throws ContentTypeException
     */
    public function __construct(FileService $fileService, string $contentType, int $order, $template)
    {
        parent::__construct($fileService, $contentType, $order, $template);

        switch ($this->contentType) {
            case AbstractTemplate::HTML_CONTENT_TYPE:
                $this->populatedTemplateFileExtension = ".html";
                break;
            case AbstractTemplate::WORD_CONTENT_TYPE:
                $this->populatedTemplateFileExtension = ".docx";
                break;
            case AbstractTemplate::EXCEL_CONTENT_TYPE:
                $this->populatedTemplateFileExtension = ".xlsx";
                break;
            case AbstractTemplate::MAIL_CONTENT_TYPE:
                $this->populatedTemplateFileExtension = ".txt";
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
        parent::__destruct();
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
