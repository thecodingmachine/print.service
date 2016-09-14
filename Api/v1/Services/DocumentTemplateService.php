<?php
namespace Api\v1\Services;

use Api\v1\Enumerations\ContentTypeEnumeration;
use Api\v1\Exceptions\NotFoundException;
use Api\v1\Exceptions\UnprocessableEntityException;
use Api\v1\Models\AbstractDocumentTemplate;
use Api\v1\Models\DocxDocumentTemplate;
use Api\v1\Models\HtmlDocumentTemplate;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use Mouf\Html\Renderer\Twig\TwigTemplate;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

/**
 * Class DocumentTemplateService
 * @package Api\v1\Services
 */
class DocumentTemplateService
{

    /**
     * @var \Twig_Environment
     */
    private $twigEnvironment;

    /**
     * DocumentTemplateService constructor.
     * @param \Twig_Environment $twigEnvironment
     */
    public function __construct(\Twig_Environment $twigEnvironment)
    {
        $this->twigEnvironment = $twigEnvironment;
    }

    /**
     * Downloads document's templates.
     *
     * @param array $documentTemplates
     * @throws NotFoundException
     * @throws RequestException
     */
    public function downloadTemplates(array $documentTemplates)
    {
        /** @var AbstractDocumentTemplate $documentTemplate */
        foreach ($documentTemplates as $documentTemplate) {
            $contentType = $documentTemplate->getContentType();

            if ($contentType == ContentTypeEnumeration::HTML) {
                /** @var HtmlDocumentTemplate $documentTemplate */
                if (!empty($documentTemplate->getHeaderUrl())) {
                    $headerLocalPath = $this->downloadTemplate($documentTemplate->getHeaderUrl(), $contentType);
                    $documentTemplate->setHeaderTemplateLocalPath($headerLocalPath);
                }

                if (!empty($documentTemplate->getFooterUrl())) {
                    $footerLocalPath = $this->downloadTemplate($documentTemplate->getFooterUrl(), $contentType);
                    $documentTemplate->setFooterTemplateLocalPath($footerLocalPath);
                }
            }

            $localPath = $this->downloadTemplate($documentTemplate->getUrl(), $contentType);
            $documentTemplate->setTemplateLocalPath($localPath);
        }
    }

    /**
     * Populates document's templates.
     *
     * @param array $documentTemplates
     * @throws UnprocessableEntityException
     * @throws \Exception
     */
    public function populate(array $documentTemplates)
    {
        try {
            /** @var AbstractDocumentTemplate $documentTemplate */
            foreach ($documentTemplates as $documentTemplate) {
                $contentType = $documentTemplate->getContentType();

                switch ($contentType) {
                    case ContentTypeEnumeration::HTML:
                        /** @var HtmlDocumentTemplate $documentTemplate */
                        $this->populateHtmlDocumentTemplate($documentTemplate);
                        break;
                    case ContentTypeEnumeration::DOCX:
                        /** @var DocxDocumentTemplate $documentTemplate */
                        $this->populateDocxDocumentTemplate($documentTemplate);
                        break;
                }

            }
        } catch (UnprocessableEntityException $e) {
            $this->removeTemporaryGeneratedFiles($documentTemplates);
            throw $e;
        }
    }

    /**
     * Merges document's templates into one file according to the specified content type.
     *
     * @param array $documentTemplates
     * @param string $accept
     * @return string
     * @throws \Exception
     */
    public function merge(array $documentTemplates, string $accept): string
    {
        switch ($accept) {
            case ContentTypeEnumeration::HTML:
                $finalDocumentPath = $this->mergeAsHtml($documentTemplates);
                break;
            case ContentTypeEnumeration::DOCX:
                $finalDocumentPath = $this->mergeAsDocx($documentTemplates);
                break;
            default:
                $finalDocumentPath = $this->mergeAsPdf($documentTemplates);
        }

        return $finalDocumentPath;
    }

    /**
     * Downloads a document's template.
     *
     * @param string $url
     * @param string $contentType
     * @return string
     * @throws NotFoundException
     * @throws RequestException
     */
    private function downloadTemplate(string $url, string $contentType): string
    {
        switch ($contentType) {
            case ContentTypeEnumeration::HTML:
                $fileExtension = ".twig";
                break;
            case ContentTypeEnumeration::DOCX:
                $fileExtension = ".docx";
                break;
            default:
                $fileExtension = ".pdf";
        }

        try {
            // TODO: checks if template not updated.
            $templatePath = ROOT_PATH . "tmp/template_" . $this->generateRandomName() . $fileExtension;
            $template = fopen($templatePath, "w");
            $stream = \GuzzleHttp\Psr7\stream_for($template);
            $client = new Client();
            $client->request("GET", $url, [ RequestOptions::SINK => $stream, RequestOptions::SYNCHRONOUS => true ]);

            return $templatePath;

        } catch (RequestException $e) {

            if ($e->getCode() == 404) {
                throw new NotFoundException();
            }

            throw $e;
        }
    }

    /**
     * Populates a Html document template.
     *
     * @param HtmlDocumentTemplate $template
     * @throws UnprocessableEntityException
     */
    private function populateHtmlDocumentTemplate(HtmlDocumentTemplate $template)
    {
        try {
            // creates the body
            $templateLocalPath = $template->getTemplateLocalPath();
            $twigBodyTemplate = new TwigTemplate($this->twigEnvironment, $templateLocalPath, $template->getData());
            $populatedTemplateLocalPath = ROOT_PATH . "tmp/" . $this->generateRandomName() . ".html";
            $populatedBodyHtmlFile = new \SplFileObject($populatedTemplateLocalPath, "w");
            $populatedBodyHtmlFile->fwrite($twigBodyTemplate->getHtml());
            $template->setPopulatedTemplateLocalPath($populatedTemplateLocalPath);

            // creates header if exists
            $headerTemplateLocalPath = $template->getHeaderTemplateLocalPath();

            if (!empty($headerTemplateLocalPath)) {
                $twigHeaderTemplate = new TwigTemplate($this->twigEnvironment, $headerTemplateLocalPath, $template->getData());
                $populatedHeaderTemplateLocalPath = ROOT_PATH . "tmp/" . $this->generateRandomName() . ".html";
                $populatedHeaderHtmlFile = new \SplFileObject($populatedHeaderTemplateLocalPath, "w");
                $populatedHeaderHtmlFile->fwrite($twigHeaderTemplate->getHtml());
                $template->setPopulatedHeaderTemplateLocalPath($populatedHeaderTemplateLocalPath);
            }

            // creates footer if exists
            $footerTemplateLocalPath = $template->getFooterTemplateLocalPath();

            if (!empty($footerTemplateLocalPath)) {
                $twigFooterTemplate = new TwigTemplate($this->twigEnvironment, $footerTemplateLocalPath, $template->getData());
                $populatedFooterTemplateLocalPath = ROOT_PATH . "tmp/" . $this->generateRandomName() . ".html";
                $populatedFooterHtmlFile = new \SplFileObject($populatedFooterTemplateLocalPath, "w");
                $populatedFooterHtmlFile->fwrite($twigFooterTemplate->getHtml());
                $template->setPopulatedFooterTemplateLocalPath($populatedFooterTemplateLocalPath);

            }

        } catch (\Exception $e) {
            throw new UnprocessableEntityException();
        }
    }

    /**
     * Populates a Docx document template.
     *
     * @param DocxDocumentTemplate $template
     * @throws UnprocessableEntityException
     */
    private function populateDocxDocumentTemplate(DocxDocumentTemplate $template)
    {
        // TODO: populates docx document template using node and docxtemplater.
    }

    /**
     * Merges document's templates as PdF.
     *
     * @param array $documentTemplates
     * @return string
     * @throws \Exception
     */
    private function mergeAsPdf(array $documentTemplates): string
    {
        $pdfFilesPaths = [];
        $finalDocumentPath = ROOT_PATH . "tmp/" . $this->generateRandomName() . ".pdf";

        // first, converts document's templates according to their content types
        /** @var AbstractDocumentTemplate $documentTemplate */
        foreach ($documentTemplates as $documentTemplate) {
            $contentType = $documentTemplate->getContentType();

            switch ($contentType) {
                case ContentTypeEnumeration::HTML:
                    $convertedDocumentTemplatePath =  ROOT_PATH . "tmp/" . $this->generateRandomName() . ".pdf";
                    $wkhtmltopdfCommand = WKHTMLTOPDF_PATH . " ";

                    /** @var HtmlDocumentTemplate $documentTemplate */
                    if (!empty($documentTemplate->getPopulatedHeaderTemplateLocalPath())) {
                        $wkhtmltopdfCommand .= "--header-html " . $documentTemplate->getPopulatedHeaderTemplateLocalPath() . " --header-spacing 3 ";
                    }

                    if (!empty($documentTemplate->getPopulatedFooterTemplateLocalPath())) {
                        $wkhtmltopdfCommand .= "--footer-html " . $documentTemplate->getPopulatedFooterTemplateLocalPath() . " --margin-bottm 15mm --footer-spacing -3 ";
                    }

                    $wkhtmltopdfCommand .= $documentTemplate->getPopulatedTemplateLocalPath() . " " . $convertedDocumentTemplatePath;

                    $process = new Process();
                    $process->run($wkhtmltopdfCommand);

                    if (!$process->isSuccessful()) {
                        $this->removeTemporaryGeneratedFiles($documentTemplates, $pdfFilesPaths);
                        throw new \Exception("Une erreur est survenue lors de la conversion d'un fichier HTML au format PDF", 500);
                    }

                    $pdfFilesPaths[] = $convertedDocumentTemplatePath;

                    break;
                case ContentTypeEnumeration::DOCX:
                    $convertedDocumentTemplatePath =  ROOT_PATH . "tmp/" . $this->generateRandomName() . ".pdf";
                    $wkhtmltopdfCommand = LIBREOFFICE_PATH . ' --headless --convert-to pdf ' . $documentTemplate->getPopulatedTemplateLocalPath() . ' --writer -outdir "' . ROOT_PATH . 'tmp"';

                    $process = new Process();
                    $process->run($wkhtmltopdfCommand);

                    if (!$process->isSuccessful()) {
                        $this->removeTemporaryGeneratedFiles($documentTemplates, $pdfFilesPaths);
                        throw new \Exception("Une erreur est survenue lors de la conversion d'un fichier Word au format PDF", 500);
                    }

                    $pdfFilesPaths[] = $convertedDocumentTemplatePath;
                    break;
                default:
                    // case PDF, no need to convert (captain obvious)
                    $pdfFilesPaths[] = $documentTemplate->getTemplateLocalPath();
            }
        }

        // then merges all PDF width PDFtk
        $pdftkCommand = PDFTK_PATH . " ";

        /** @var string $pdfFilePath */
        foreach ($pdfFilesPaths as $pdfFilePath) {
            $pdftkCommand .= $pdfFilePath . " ";
        }

        $pdftkCommand .= "cat output " . $finalDocumentPath;

        $process = new Process();
        $process->run($pdftkCommand);

        if (!$process->isSuccessful()) {
            $this->removeTemporaryGeneratedFiles($documentTemplates, $pdfFilesPaths);
            throw new \Exception("Une erreur est survenue lors de la fusion des documents PDF", 500);
        }

        return $finalDocumentPath;
    }

    /**
     * Merges document's templates as Html.
     *
     * @param array $documentTemplates
     * @return string
     * @throws \Exception
     */
    private function mergeAsHtml(array $documentTemplates): string
    {
        // TODO: merge all document templates as html. Write in a html file might be the best solution.
        return "";
    }

    /**
     * Merges document's templates as Docx.
     *
     * @param array $documentTemplates
     * @return string
     * @throws \Exception
     */
    private function mergeAsDocx(array $documentTemplates): string
    {
        // TODO: merge all document templates as docx. Uses a script to add pages to a new file or first template.
        return "";
    }

    /**
     * Removes the temporary generated files (but not the templates).
     *
     * @param array $documentTemplates
     * @param array|null $pdfFilesPaths
     * @throws \Exception
     */
    private function removeTemporaryGeneratedFiles(array $documentTemplates, array $pdfFilesPaths = null)
    {
        $fileSystem = new Filesystem();
        $filesToRemovePaths = [];

        /** @var AbstractDocumentTemplate $documentTemplate */
        foreach ($documentTemplates as $documentTemplate) {
            $contentType = $documentTemplate->getContentType();

            switch ($contentType) {
                case ContentTypeEnumeration::PDF:

                    // removes the pdf template from the files to delete
                    if (!empty($pdfFilesPaths) && in_array($documentTemplate->getTemplateLocalPath(), $pdfFilesPaths)) {
                        array_splice($pdfFilesPaths, array_search($documentTemplate->getTemplateLocalPath(), $pdfFilesPaths), 1);
                    }

                    break;
                case ContentTypeEnumeration::HTML:

                    if (!empty($documentTemplate->getPopulatedTemplateLocalPath())) {
                        $filesToRemovePaths[] = $documentTemplate->getPopulatedTemplateLocalPath();
                    }

                    /** @var HtmlDocumentTemplate $documentTemplate */
                    if (!empty($documentTemplate->getPopulatedHeaderTemplateLocalPath())) {
                        $filesToRemovePaths[] = $documentTemplate->getPopulatedHeaderTemplateLocalPath();
                    }

                    if (!empty($documentTemplate->getPopulatedFooterTemplateLocalPath())) {
                        $filesToRemovePaths[] = $documentTemplate->getPopulatedFooterTemplateLocalPath();
                    }

                    break;
                default:
                    // case Word document (.docx)
                    if (!empty($documentTemplate->getPopulatedTemplateLocalPath())) {
                        $filesToRemovePaths[] = $documentTemplate->getPopulatedTemplateLocalPath();
                    }

            }
        }

        if (!empty($pdfFilesPaths)) {
            $filesToRemovePaths = array_merge($filesToRemovePaths, $pdfFilesPaths);
        }

        $fileSystem->remove($filesToRemovePaths);
    }

    /**
     * Generates a random template name.
     *
     * @return string
     */
    private function generateRandomName(): string
    {
        return substr(str_shuffle(str_repeat($x="0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ", ceil(5/strlen($x)))), 1, 5) . "_dt_" . time();
    }

}