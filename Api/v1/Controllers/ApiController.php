<?php
namespace Api\v1\Controllers;

use Mouf\Mvc\Splash\Annotations\Post;
use Mouf\Mvc\Splash\Annotations\URL;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ApiController
 * @package Api\v1\Controllers
 */
class ApiController
{

    /**
     * @URL("api/v1/documents/generate")
     * @Post
     * @param ServerRequestInterface $request
     */
    public function generate(ServerRequestInterface $request)
    {

    }

    /**
     * @URL("api/v1/documents/merge")
     * @Post
     * @param ServerRequestInterface $request
     */
    public function merge(ServerRequestInterface $request)
    {

    }

}