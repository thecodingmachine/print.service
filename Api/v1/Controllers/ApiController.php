<?php
namespace Api\v1\Controllers;

use Api\v1\Models\DocumentsHandler;
use Api\v1\Services\FileService;
use Zend\Diactoros\Response;
use Mouf\Mvc\Splash\Annotations\Post;
use Mouf\Mvc\Splash\Annotations\URL;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

/**
 * Class ApiController
 * @package Api\v1\Controllers
 */
class ApiController
{

    /**
     * @var FileService
     */
    private $fileService;

    /**
     * ApiController constructor.
     * @param FileService $fileService
     */
    public function __construct(FileService $fileService)
    {
        // TODO available urls.
        $this->fileService = $fileService;
    }

    /**
     * @URL("api/v1/documents/generate")
     * @Post
     * @param ServerRequestInterface $request
     * @return JsonResponse|Response
     */
    public function generate(ServerRequestInterface $request)
    {
        // TODO basic auth.
        try {
            $accept = $request->getHeaderLine("Accept");
            $postData = $request->getParsedBody();
            $documentsHandler = new DocumentsHandler($this->fileService, $accept, $postData);
            $documentsHandler->generate();
            return $this->fileService->serveFile($documentsHandler->getFinalDocument(), $documentsHandler->getMediaType());
        } catch (\Exception $e) {
            $errorResponse = new JsonResponse([
                "message" => $e->getMessage(),
                "stackTrace" => $e->getTraceAsString()
            ]);

            if ($e->getCode() > 100 && $e->getCode() < 599) {
                return $errorResponse->withStatus($e->getCode());
            }

            return $errorResponse;
        }
    }

    /**
     * @URL("api/v1/documents/merge")
     * @Post
     * @param ServerRequestInterface $request
     * @return JsonResponse|Response
     */
    public function merge(ServerRequestInterface $request)
    {
        // TODO basic auth.
        try {
            $accept = $request->getHeaderLine("Accept");
            $postData = $request->getParsedBody();
            $documentsHandler = new DocumentsHandler($this->fileService, $accept, $postData, true);
            $documentsHandler->generate();
            return $this->fileService->serveFile($documentsHandler->getFinalDocument(), $documentsHandler->getMediaType());
        } catch (\Exception $e) {
            $errorResponse = new JsonResponse([
                "message" => $e->getMessage(),
                "stackTrace" => $e->getTraceAsString()
            ]);

            if ($e->getCode() > 100 && $e->getCode() < 599) {
                return $errorResponse->withStatus($e->getCode());
            }

            return $errorResponse;
        }
    }

}