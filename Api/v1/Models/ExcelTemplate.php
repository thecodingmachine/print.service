<?php

namespace Api\v1\Models;

use Api\v1\Content\ContentInterface;
use Api\v1\Exceptions\ContentTypeException;
use Api\v1\Exceptions\UnprocessableEntityException;
use Api\v1\Exceptions\ExcelDocumentToPdfException;
use Api\v1\Services\FileService;

/**
 * ExcelTemplate
 *
 */
class ExcelTemplate extends AbstractTemplateToPopulate
{
    /**
     * ExcelTemplate constructor.
     * @param FileService $fileService
     * @param int $order
     * @param ContentInterface|null $templateUrl
     * @throws ContentTypeException
     */
    public function __construct(FileService $fileService, int $order, $templateUrl)
    {
        parent::__construct($fileService, AbstractTemplate::EXCEL_CONTENT_TYPE, $order, $templateUrl);
    }

    /**
     * Populates the template.
     * @param array|null $data
     * @throws UnprocessableEntityException
     */
    public function populate(array $data = null)
    {
        $this->populatedTemplate = $this->fileService->populateExcelDocument($this->template, $data, $this->fileService->generateRandomFileName($this->populatedTemplateFileExtension));
    }
    /**
     * Converts the template to PDF.
     * @throws ExcelDocumentToPdfException
     */
  public function convertToPdf()
    {
        $this->pdfTemplate = $this->fileService->convertExcelDocumentToPdf($this->populatedTemplate, $this->fileService->generateRandomFileName(".pdf"));
    }
}
