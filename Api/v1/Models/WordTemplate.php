<?php
namespace Api\v1\Models;

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
     * @param string $templateUrl
     * @throws ContentTypeException
     */
    public function __construct(FileService $fileService, int $order, string $templateUrl)
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
        // TODO: Implement populate() method.
    }

    /**
     * Converts the template to PDF.
     * @throws WordDocumentToPdfException
     */
    public function convertToPdf()
    {
        $this->pdfTemplate = $this->fileService->convertWordDocumentToPDf($this->populatedTemplate, $this->fileService->generateRandomFileName(".pdf"));
    }

}