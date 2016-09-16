<?php
namespace Api\v1\Models;
use Api\v1\Exceptions\HtmlToPdfException;
use Api\v1\Exceptions\UnprocessableEntityException;
use Api\v1\Exceptions\WordDocumentToPdfException;

/**
 * Class Document
 * @package Api\v1\Models
 */
class Document
{

    /**
     * @var \SplPriorityQueue<AbstractTemplate>
     */
    private $templates;

    /**
     * @var array
     */
    private $data;

    /**
     * Document constructor.
     * @param array|null $data
     */
    public function __construct(array $data = null)
    {
        $this->templates = new \SplPriorityQueue();
        $this->data = $data;
    }

    /**
     * Adds a template in the array of templates with the correct order.
     * @param AbstractTemplate $template
     */
    public function addTemplate(AbstractTemplate $template)
    {
        $this->templates->insert($template, $template->getOrder());
    }

    /**
     * Downloads the templates.
     * @throws \Exception
     */
    public function downloadTemplates()
    {
        /** @var AbstractTemplate $currentTemplate */
        foreach ($this->templates as $currentTemplate) {
            $currentTemplate->download();
        }
    }

    /**
     * Populates the templates.
     * @throws UnprocessableEntityException
     */
    public function populateTemplates()
    {
        /** @var AbstractTemplate $currentTemplate */
        foreach ($this->templates as $currentTemplate) {
           $contentType = $currentTemplate->getContentType();

            if ($contentType != AbstractTemplate::PDF_CONTENT_TYPE) {
                /** @var AbstractTemplateToPopulate $currentTemplate */
                $currentTemplate->populate($this->data);
            }
        }
    }

    /**
     * Converts the templates to PDF.
     * @throws HtmlToPdfException
     * @throws WordDocumentToPdfException
     */
    public function convertTemplatesToPdf()
    {
        /** @var AbstractTemplate $currentTemplate */
        foreach ($this->templates as $currentTemplate) {
            $contentType = $currentTemplate->getContentType();

            if ($contentType != AbstractTemplate::PDF_CONTENT_TYPE) {
                /** @var AbstractTemplateToPopulate $currentTemplate */
                $currentTemplate->convertToPdf();
            }
        }
    }

    /**
     * @return \SplPriorityQueue<AbstractTemplate>
     */
    public function getTemplates(): \SplPriorityQueue
    {
        return $this->templates;
    }

}