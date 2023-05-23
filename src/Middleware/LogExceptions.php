<?php

declare(strict_types=1);

namespace Linio\Common\Laminas\Middleware;

use Linio\Common\Laminas\Exception\Base\NonCriticalDomainException;
use Linio\Component\Microlog\Log;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Stratigility\Middleware\ErrorHandler;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class LogExceptions implements MiddlewareInterface
{
    public const EXCEPTIONS_CHANNEL = 'exceptions';

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $errorMiddleware = new ErrorHandler(
            function ($request) use ($handler) {
                return $handler->handle($request);
            },
            null,
            function ($error, $request, $response) {
                if ($error instanceof NonCriticalDomainException) {
                    Log::error($error, [], self::EXCEPTIONS_CHANNEL);
                } else {
                    Log::critical($error, [], self::EXCEPTIONS_CHANNEL);
                }
                return $response;
            }
        );

        return $errorMiddleware->process($request, $handler);
    }
}