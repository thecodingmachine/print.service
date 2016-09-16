<?php
namespace Api\v1\Services;

use Api\v1\Exceptions\HtmlToPdfException;
use Api\v1\Exceptions\MergingPdfException;
use Api\v1\Exceptions\UnprocessableEntityException;
use Api\v1\Exceptions\WordDocumentToPdfException;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Mouf\Html\Renderer\Twig\TwigTemplate;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

/**
 * Class FileService
 * @package Api\v1\Services
 */
class FileService
{

    /**
     * @var \Twig_Environment
     */
    private $twigEnvironment;

    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * @var Client
     */
    private $client;

    /**
     * FileService constructor.
     * @param \Twig_Environment $twigEnvironment
     */
    public function __construct(\Twig_Environment $twigEnvironment)
    {
        $this->twigEnvironment = $twigEnvironment;
        $this->fileSystem = new Filesystem();
        $this->client = new Client();
    }

    /**
     * Generates a random file name.
     * @param string $ext
     * @return string
     */
    public function generateRandomFileName(string $ext): string
    {
        return substr(str_shuffle(str_repeat($x="0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ", ceil(5/strlen($x)))), 1, 5) . "_dt_" . time() . $ext;
    }

    /**
     * Downloads a file.
     * @param $fileName
     * @param string $fileUrl
     * @return \SplFileInfo
     * @throws \Exception
     */
    public function downloadFile(string $fileName, string $fileUrl): \SplFileInfo
    {
        $filePath = ROOT_PATH . TEMPORARY_FILES_FOLDER . $fileName;
        $file = new \SplFileObject($filePath, "w");
        $stream = \GuzzleHttp\Psr7\stream_for($file);
        $this->client->request("GET", $fileUrl, [ RequestOptions::SINK => $stream, RequestOptions::SYNCHRONOUS => true ]);
        return $file->getFileInfo();
    }

    /**
     * Populates a twig file.
     * @param \SplFileInfo $file
     * @param array $data
     * @param string $resultFileName
     * @return \SplFileInfo
     * @throws UnprocessableEntityException
     */
    public function populateHtml(\SplFileInfo $file, array $data, string $resultFileName): \SplFileInfo
    {
        try {
            $twigTemplate = new TwigTemplate($this->twigEnvironment, $file->getRealPath(), $data);
            $folderPath = $file->getPathInfo()->getRealPath();
            $populatedHtmlFile = new \SplFileObject($folderPath . $resultFileName, "w");
            $populatedHtmlFile->fwrite($twigTemplate->getHtml());
            return $populatedHtmlFile->getFileInfo();
        } catch (\Exception $e) {
            throw new UnprocessableEntityException($e->getMessage());
        }
    }

    /**
     * Converts a HTML file to PDF.
     * @param \SplFileInfo $body
     * @param string $resultFileName
     * @param \SplFileInfo|null $header
     * @param \SplFileInfo|null $footer
     * @return \SplFileInfo
     * @throws HtmlToPdfException
     */
    public function convertHtmlFileToPdf(\SplFileInfo $body, string $resultFileName, \SplFileInfo $header = null, \SplFileInfo $footer = null): \SplFileInfo
    {
        $folderPath = $body->getPathInfo()->getRealPath();
        $wkhtmltopdfCommand = WKHTMLTOPDF_PATH . " ";

        if (!empty($header)) {
            $wkhtmltopdfCommand .= "--header-html " . $header->getRealPath() . " --header-spacing 3 ";
        }

        if (!empty($footer)) {
            $wkhtmltopdfCommand .= "--footer-html " . $footer->getRealPath() . " --margin-bottm 15mm --footer-spacing -3 ";
        }

        $wkhtmltopdfCommand .= $body->getRealPath() . " " . $folderPath . $resultFileName;

        $process = new Process();
        $process->run($wkhtmltopdfCommand);

        if (!$process->isSuccessful()) {
            throw new HtmlToPdfException();
        }

       return new \SplFileInfo($folderPath . $resultFileName);
    }

    /**
     * Converts a Word document to PDF.
     * @param \SplFileInfo $wordDocument
     * @param string $resultFileName
     * @return \SplFileInfo
     * @throws WordDocumentToPdfException
     */
    public function convertWordDocumentToPDf(\SplFileInfo $wordDocument, string $resultFileName): \SplFileInfo
    {
        $folderPath = $wordDocument->getPathInfo()->getRealPath();
        $sofficeCommand = LIBREOFFICE_PATH . ' --headless --convert-to pdf ' . $wordDocument->getRealPath() . ' --writer -outdir "' . $folderPath . $resultFileName . '"';

        $process = new Process();
        $process->run($sofficeCommand);

        if (!$process->isSuccessful()) {
            throw new WordDocumentToPdfException();
        }

        return new \SplFileInfo($folderPath . $resultFileName);
    }

    /**
     * Merges a list of PDF files.
     * @param array<\SplFileInfo> $pdfFilesToMerge
     * @param string $resultFileName
     * @return \SplFileInfo
     * @throws MergingPdfException
     */
    public function mergePdf(array $pdfFilesToMerge, string $resultFileName): \SplFileInfo
    {
        $folderPath = null;
        $pdftkCommand = PDFTK_PATH . " ";

        /** @var \SplFileInfo $pdfFile */
        foreach ($pdfFilesToMerge as $pdfFile) {
            $pdftkCommand .= $pdfFile->getRealPath() . " ";

            if (empty($folderPath)) {
                $folderPath = $pdfFile->getPathInfo()->getRealPath();
            }
        }

        $pdftkCommand .= "cat output " . $folderPath . $resultFileName;

        $process = new Process();
        $process->run($pdftkCommand);

        if (!$process->isSuccessful()) {
            throw new MergingPdfException();
        }

        return new \SplFileInfo($folderPath . $resultFileName);
    }

}