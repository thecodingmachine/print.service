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
        try {
            $prepared = $this->requestParserService->prepare($request);
            $parsed = $this->requestParserService->parse($prepared);
            $documentTemplates = $this->documentTemplateService->downloadTemplates($parsed);
            $documentTemplates = $this->documentTemplateService->populate($documentTemplates);

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
        try {
            $prepared = $this->requestParserService->prepare($request, true);
            $parsed = $this->requestParserService->parse($prepared, true);
            $documentTemplates = $this->documentTemplateService->downloadTemplates($parsed);
            $documentTemplates = $this->documentTemplateService->populate($documentTemplates);

        } catch (\Exception $e) {
            $errorResponse = new JsonResponse([
                "message" => $e->getMessage()
            ]);

            return $errorResponse->withStatus($e->getCode());
        }
    }

}