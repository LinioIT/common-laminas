<?php

declare(strict_types=1);

namespace Linio\Common\Laminas\Middleware;

use Linio\Common\Laminas\Exception\Http\MiddlewareOutOfOrderException;
use Linio\Common\Laminas\Exception\Http\RouteNotFoundException;

use function Linio\Common\Laminas\Support\getCurrentRouteFromMatchedRoute;

use Linio\Common\Laminas\Validation\ValidationService;
use Mezzio\Router\RouteCollector;
use Mezzio\Router\RouteResult;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ValidateRequestBody implements MiddlewareInterface
{
    private ValidationService $validationService;

    private RouteCollector $routes;

    public function __construct(ValidationService $validationService, RouteCollector $routes)
    {
        $this->validationService = $validationService;
        $this->routes = $routes;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $routeResult = $request->getAttribute(RouteResult::class);

        if (!$routeResult instanceof RouteResult || $routeResult->isFailure()) {
            throw new MiddlewareOutOfOrderException('Routing Middleware', self::class);
        }

        $validationClasses = $this->getValidationRuleClasses($routeResult);

        if (!empty($validationClasses)) {
            $this->validationService->validate($request->getParsedBody(), $validationClasses);
        }

        return $handler->handle($request);
    }

    /**
     * @throws RouteNotFoundException
     */
    private function getValidationRuleClasses(RouteResult $routeResult): array
    {
        $matchedRoute = getCurrentRouteFromMatchedRoute($routeResult, $this->routes);

        if (empty($matchedRoute->getOptions()['validation_rules'])) {
            return [];
        }

        $rules = $matchedRoute->getOptions()['validation_rules'];

        if (!is_array($rules)) {
            return [$rules];
        }

        return $rules;
    }
}
