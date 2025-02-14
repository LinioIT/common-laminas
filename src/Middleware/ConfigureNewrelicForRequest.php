<?php

declare(strict_types=1);

namespace Linio\Common\Laminas\Middleware;

use Linio\Common\Laminas\Exception\Http\MiddlewareOutOfOrderException;
use Mezzio\Router\RouteResult;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ConfigureNewrelicForRequest implements MiddlewareInterface
{
    private string $appName;

    public function __construct(string $appName)
    {
        $this->appName = $appName;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!extension_loaded('newrelic')) {
            return $handler->handle($request);
        }

        newrelic_set_appname($this->appName);
        $this->addRequestIdToNewrelic($request);
        $this->nameRouteIfRouteFound($request);

        return $handler->handle($request);
    }

    private function nameRouteIfRouteFound(ServerRequestInterface $request): void
    {
        /** @var RouteResult $routeResult */
        $routeResult = $request->getAttribute(RouteResult::class);

        if (!$routeResult instanceof RouteResult || $routeResult->isFailure()) {
            return;
        }

        $routeName = $routeResult->getMatchedRouteName();

        newrelic_name_transaction($routeName);
    }

    /**
     * @throws MiddlewareOutOfOrderException
     */
    private function addRequestIdToNewrelic(ServerRequestInterface $request): void
    {
        $requestId = $request->getAttribute('requestId', false);

        if (!$requestId) {
            return;
        }

        newrelic_add_custom_parameter('requestId', $requestId);
    }
}
