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
        try {
            $response = $handler->handle($request);
            $body = json_decode($response->getBody()->getContents());

            $error = $this->getErrors($body);

            if ($response instanceof NonCriticalDomainException) {
                Log::error($error, [], self::EXCEPTIONS_CHANNEL);
            } else {
                Log::critical($error, [], self::EXCEPTIONS_CHANNEL);
            }

            return $response;
        } catch (\Exception $exception) {
            Log::critical($exception->getMessage(), [], self::EXCEPTIONS_CHANNEL);
            return new Response(500, [], 'Internal Server Error');
        }
    }

    private function getErrors($body)
    {
        if ($body === null && json_last_error() !== JSON_ERROR_NONE){
            $error = 'Error: Invalid response';
        }else{
            $error = (empty($body->errors) || !isset($body->errors)) ? $body : $body->errors;
        }

        return $error;
    }
}