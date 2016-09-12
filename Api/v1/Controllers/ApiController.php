<?php
namespace Api\v1\Controllers;

use Api\v1\Services\DocumentTemplateService;
use Api\v1\Services\RequestParserService;
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
     * @var RequestParserService
     */
    private $requestParserService;

    /**
     * @var DocumentTemplateService
     */
    private $documentTemplateService;

    /**
     * ApiController constructor.
     * @param RequestParserService $requestParserService
     * @param DocumentTemplateService $documentTemplateService
     */
    public function __construct(RequestParserService $requestParserService, DocumentTemplateService $documentTemplateService)
    {
        $this->requestParserService = $requestParserService;
        $this->documentTemplateService = $documentTemplateService;
    }

    /**
     * @URL("api/v1/documents/generate")
     * @Post
     * @param ServerRequestInterface $request
     * @return JsonResponse
     */
    public function generate(ServerRequestInterface $request)
    {
        // TODO basic auth
        try {
            $prepared = $this->requestParserService->prepare($request);
            $documentTemplates = $this->requestParserService->parse($prepared);
            $this->documentTemplateService->downloadTemplates($documentTemplates);
            $this->documentTemplateService->populate($documentTemplates);
            $finalDocumentPath = $this->documentTemplateService->merge($documentTemplates, $prepared["accept"]);

            // TODO return final document.

            return new JsonResponse([ "message" => $finalDocumentPath ]);

        } catch (\Exception $e) {
            $errorResponse = new JsonResponse([
                "message" => $e->getMessage()
            ]);

            return $errorResponse->withStatus($e->getCode());
        }
    }

    /**
     * @URL("api/v1/documents/merge")
     * @Post
     * @param ServerRequestInterface $request
     * @return JsonResponse
     */
    public function merge(ServerRequestInterface $request)
    {
        // TODO basic auth
        try {
            $prepared = $this->requestParserService->prepare($request, true);
            $documentTemplates = $this->requestParserService->parse($prepared, true);
            $this->documentTemplateService->downloadTemplates($documentTemplates);
            $this->documentTemplateService->populate($documentTemplates);
            $finalDocumentPath = $this->documentTemplateService->merge($documentTemplates, $prepared["accept"]);

            // TODO return final document.

            return new JsonResponse([ "message" => $finalDocumentPath ]);

        } catch (\Exception $e) {
            $errorResponse = new JsonResponse([
                "message" => $e->getMessage()
            ]);

            return $errorResponse->withStatus($e->getCode());
        }
    }

}