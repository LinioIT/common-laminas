<?php

declare(strict_types=1);

namespace Linio\Common\Laminas\Middleware;

use Linio\Common\Laminas\Exception\Base\NonCriticalDomainException;
use Linio\Component\Microlog\Log;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class LogExceptions implements MiddlewareInterface
{
    public const EXCEPTIONS_CHANNEL = 'exceptions';

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $error = $handler->handle($request);
        $body = json_decode($error->getBody()->getContents(), false);

        if ($error instanceof NonCriticalDomainException) {
            Log::error($body->errors, [], self::EXCEPTIONS_CHANNEL);
        } else {
            Log::critical($body->errors, [], self::EXCEPTIONS_CHANNEL);
        }

        return $error;
    }
}