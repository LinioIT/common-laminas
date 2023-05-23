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
        $error_handle = $handler->handle($request);
        $body = json_decode($$error_handle->getBody()->getContents(), false);

        $error = ($body === null && json_last_error() !== JSON_ERROR_NONE)
                 ? 'Error: Invalid response'
                 : (empty($body->errors) || !isset($body->errors)) ? $body : $body->errors;

        if ($error instanceof NonCriticalDomainException) {
            Log::error($error, [], self::EXCEPTIONS_CHANNEL);
        } else {
            Log::critical($error, [], self::EXCEPTIONS_CHANNEL);
        }

        return $error_handle;
    }
}