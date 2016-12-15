<?php
namespace Api\v1\Models;

use Api\v1\Content\ContentInterface;
use Api\v1\Exceptions\ContentTypeException;
use Api\v1\Exceptions\UnprocessableEntityException;
use Api\v1\Exceptions\WordDocumentToPdfException;
use Api\v1\Services\FileService;

/**
 * Class WordTemplate
 * @package Api\v1\Models
 */
class WordTemplate extends AbstractTemplateToPopulate
{

    /**
     * WordTemplate constructor.
     * @param FileService $fileService
     * @param int $order
     * @param ContentInterface|null $templateUrl
     * @throws ContentTypeException
     */
    public function __construct(FileService $fileService, int $order, $templateUrl)
    {
        parent::__construct($fileService, AbstractTemplate::WORD_CONTENT_TYPE, $order, $templateUrl);
    }

    /**
     * Populates the template.
     * @param array|null $data
     * @throws UnprocessableEntityException
     */
    public function populate(array $data = null)
    {
        $this->populatedTemplate = $this->fileService->populateWordDocument($this->template, $data, $this->fileService->generateRandomFileName($this->populatedTemplateFileExtension));
    }

    /**
     * Converts the template to PDF.
     * @throws WordDocumentToPdfException
     */
    public function convertToPdf()
    {
        $this->pdfTemplate = $this->fileService->convertWordDocumentToPdf($this->populatedTemplate, $this->fileService->generateRandomFileName(".pdf"));
    }

}