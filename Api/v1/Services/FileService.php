<?php
namespace Api\v1\Services;

use Api\v1\Exceptions\HtmlToPdfException;
use Api\v1\Exceptions\MergingHtmlException;
use Api\v1\Exceptions\MergingPdfException;
use Api\v1\Exceptions\MergingWordDocumentException;
use Api\v1\Exceptions\UnprocessableEntityException;
use Api\v1\Exceptions\WordDocumentToPdfException;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
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
     * @var \SplFileInfo
     */
    private $temporaryFilesFolder;

    /**
     * FileService constructor.
     * @param \Twig_Environment $twigEnvironment
     */
    public function __construct(\Twig_Environment $twigEnvironment)
    {
        $this->twigEnvironment = $twigEnvironment;
        $this->temporaryFilesFolder = new \SplFileInfo(ROOT_PATH . TEMPORARY_FILES_FOLDER);
        $this->fileSystem = new Filesystem();
        $this->client = new Client();
    }

    /**
     * Generates a random file name.
     * @param string $ext
     * @return string
     */
    public function generateRandomFileName(string $ext = ""): string
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
        $filePath = $this->temporaryFilesFolder->getRealPath() . "/" . $fileName;
        $file = fopen($filePath, "w");
        $stream = \GuzzleHttp\Psr7\stream_for($file);
        $this->client->request("GET", $fileUrl, [ RequestOptions::SINK => $stream, RequestOptions::SYNCHRONOUS => true ]);
        return new \SplFileInfo($filePath);
    }

    /**
     * Populates a twig file.
     * @param \SplFileInfo $file
     * @param array $data
     * @param string $resultFileName
     * @return \SplFileInfo
     * @throws UnprocessableEntityException
     */
    public function populateTwigFile(\SplFileInfo $file, array $data, string $resultFileName): \SplFileInfo
    {
        try {
            $twigTemplate = new TwigTemplate($this->twigEnvironment, $file->getRealPath(), $data);
            $folderPath = $this->temporaryFilesFolder->getRealPath() . "/";
            $populatedHtmlFile = new \SplFileObject($folderPath . $resultFileName, "w");
            $populatedHtmlFile->fwrite($twigTemplate->getHtml());
            return $populatedHtmlFile->getFileInfo();
        } catch (\Exception $e) {
            throw new UnprocessableEntityException($e->getMessage());
        }
    }

    /**
     * Populates a Word document.
     * @param \SplFileInfo $file
     * @param array $data
     * @param string $resultFileName
     * @return \SplFileInfo
     * @throws UnprocessableEntityException
     */
    public function populateWordDocument(\SplFileInfo $file, array $data, string $resultFileName): \SplFileInfo
    {
        $folderPath = $this->temporaryFilesFolder->getRealPath() . "/";
        $scriptFile = new \SplFileInfo(ROOT_PATH . "Api/v1/Scripts/populateWordDocument.js");
        $nodeCommand = NODE_PATH . " " . $scriptFile->getRealPath() . " " . $file->getRealPath() . " " . json_encode($data) . " " . $folderPath . $resultFileName;

        $process = new Process($nodeCommand);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new UnprocessableEntityException($process->getErrorOutput());
        }

        return new \SplFileInfo($folderPath . $resultFileName);
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
        $folderPath = $this->temporaryFilesFolder->getRealPath(). "/";
        $wkhtmltopdfCommand = WKHTMLTOPDF_PATH . " ";

        if (!empty($header)) {
            $wkhtmltopdfCommand .= "--header-html " . $header->getRealPath() . " --header-spacing 3 ";
        }

        if (!empty($footer)) {
            $wkhtmltopdfCommand .= "--footer-html " . $footer->getRealPath() . " --margin-bottm 15mm --footer-spacing -3 ";
        }

        $wkhtmltopdfCommand .= $body->getRealPath() . " " . $folderPath . $resultFileName;

        $process = new Process($wkhtmltopdfCommand);
        $process->run();

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
    public function convertWordDocumentToPdf(\SplFileInfo $wordDocument, string $resultFileName): \SplFileInfo
    {
        $folderPath = $this->temporaryFilesFolder->getRealPath() . "/";
        $sofficeCommand = LIBREOFFICE_PATH . ' --headless --convert-to pdf ' . $wordDocument->getRealPath() . ' --writer -outdir "' . $folderPath . $resultFileName . '"';

        $process = new Process($sofficeCommand);
        $process->run();

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
    public function mergePdfFiles(array $pdfFilesToMerge, string $resultFileName): \SplFileInfo
    {
        $folderPath = $this->temporaryFilesFolder->getRealPath() . "/";
        $pdftkCommand = PDFTK_PATH . " ";

        /** @var \SplFileInfo $pdfFile */
        foreach ($pdfFilesToMerge as $pdfFile) {
            $pdftkCommand .= $pdfFile->getRealPath() . " ";
        }

        $pdftkCommand .= "cat output " . $folderPath . $resultFileName;

        $process = new Process($pdftkCommand);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new MergingPdfException();
        }

        return new \SplFileInfo($folderPath . $resultFileName);
    }

    /**
     * Merges a list of HTML files.
     * @param array<array<String, \SplFileInfo>> $htmlFilesToMerge
     * @param string $resultFileName
     * @return \SplFileInfo
     * @throws MergingHtmlException
     */
    public function mergeHtmlFiles(array $htmlFilesToMerge, string $resultFileName): \SplFileInfo
    {
        try {
            $scriptFile = new \SplFileInfo(ROOT_PATH . "Api/v1/Scripts/mergeHtml.twig");
            $twigTemplate = new TwigTemplate($this->twigEnvironment, $scriptFile->getRealPath(), $htmlFilesToMerge);
            $folderPath = $this->temporaryFilesFolder->getRealPath() . "/";
            $resultFile = new \SplFileObject($folderPath . $resultFileName, "w");
            $resultFile->fwrite($twigTemplate->getHtml());
            return $resultFile->getFileInfo();
        } catch (\Exception $e) {
            throw new MergingHtmlException($e->getMessage());
        }
    }

    /**
     * Merges a list of Word documents.
     * @param array $wordDocumentsToMerge
     * @param string $resultFileName
     * @return \SplFileInfo
     * @throws MergingWordDocumentException
     */
    public function mergeWordDocuments(array $wordDocumentsToMerge, string $resultFileName): \SplFileInfo
    {
        if (count($wordDocumentsToMerge) == 1) {
            return $wordDocumentsToMerge[0];
        }

        throw new MergingWordDocumentException();
    }

    /**
     * Serves a file.
     * @param \SplFileInfo $file
     * @param string $contentType
     * @return Response
     */
    public function serveFile(\SplFileInfo $file, string $contentType): Response
    {
        $attachedFile = fopen($file->getRealPath(), "r");
        $stream = \GuzzleHttp\Psr7\stream_for($attachedFile);

        var_dump($file->getRealPath());

        $response = new Response();
        $response->withHeader("Content-Description", "File Transfer");
        $response->withHeader("Content-Type", $contentType);
        $response->withHeader("Content-Disposition", "attachement; filename=" . $file->getBasename());
        $response->withHeader("Content-Transfer-Encoding", "binary");
        $response->withHeader("Expires", "0");
        $response->withHeader("Cache-Control", "must-revalidate, post-check=0, pre-check=0");
        $response->withHeader("Pragma", "public");
        $response->withHeader("Content-Length", $file->getSize());
        $response->withBody($stream);

        var_dump($response);

        return $response;
    }

    /**
     * Removes a file from disk.
     * @param \SplFileInfo|null $file
     */
    public function removeFileFromDisk(\SplFileInfo $file = null)
    {
        if (!empty($file)) {
            if ($this->fileSystem->exists($file->getRealPath())) {
                try {
                    $this->fileSystem->remove($file->getRealPath());
                } catch (\Exception $e) {
                    error_log($e->getMessage());
                }
            }
        }
    }

}