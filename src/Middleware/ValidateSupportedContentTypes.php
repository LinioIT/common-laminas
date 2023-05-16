<?php

declare(strict_types=1);

namespace Linio\Common\Laminas\Middleware;

use Linio\Common\Laminas\Exception\Http\ContentTypeNotSupportedException;
use Linio\Common\Laminas\Exception\Http\MiddlewareOutOfOrderException;
use Linio\Common\Laminas\Exception\Http\RouteNotFoundException;
use function Linio\Common\Laminas\Support\getCurrentRouteFromMatchedRoute;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Mezzio\Container\ApplicationFactory;
use Mezzio\Router\RouteResult;
use Mezzio\Router\RouteCollector;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ValidateSupportedContentTypes implements MiddlewareInterface
{
    public const DEFAULT_CONTENT_TYPES = ['application/json'];

    private array $supportedContentTypes = [];
    private RouteCollector $routeCollector;

    public function __construct(array $supportedContentTypes, RouteCollector $routeCollector)
    {
        $this->supportedContentTypes = $supportedContentTypes;
        $this->routeCollector = $routeCollector;
    }

    /**
     * @param ?string $contentType Null allows non-api requests
     */
    public function supportType(?string $contentType = null): self
    {
        $this->supportedContentTypes[] = $contentType;

        return $this;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $contentType = $request->getHeader('Content-Type')[0] ?? null;

        try {
            $this->matchContentTypeFromRoute($contentType, $request);

            return $handler->handle($request);
        } catch (RouteNotFoundException $exception) {
            // Fallback to non-route specific types
        }

        if (in_array($contentType, $this->supportedContentTypes)) {
            return $handler->handle($request);
        }

        throw new ContentTypeNotSupportedException($contentType);
    }

    private function matchContentTypeFromRoute(?string $contentType, ServerRequestInterface $request): void
    {
        $routeResult = $request->getAttribute(RouteResult::class);

        if (!$routeResult instanceof RouteResult || !$routeResult->isSuccess()) {
            throw new MiddlewareOutOfOrderException(ApplicationFactory::ROUTING_MIDDLEWARE, self::class);
        }

        $routeConfig = getCurrentRouteFromMatchedRoute($routeResult, $this->routeCollector);

        if (isset($routeConfig->getOptions()['content_types']) && is_array($routeConfig->getOptions()['content_types'])) {
            if (!in_array($contentType, $routeConfig->getOptions()['content_types'])) {
                throw new ContentTypeNotSupportedException($contentType);
            }
        }
    }
}
