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
        $response = $handler->handle($request);
        $body = json_decode($response->getBody()->getContents());

        $errors = $this->getErrors($body);

        foreach ($errors as $error) {
            if(isset($error->message)){
                $this->saveLog($error->message, (array) $error, $response);
            }
        }

        return $response;
    }

    private function getErrors($body): array
    {
        if ($body === null && json_last_error() !== JSON_ERROR_NONE){
            $errors[] = ['message' => 'Error: Invalid response'];
        }else{
            $errors = (empty($body->errors) || !isset($body->errors)) ? [0 => (array) $body] : $body->errors;
        }

        return (array) $errors;
    }

    private function saveLog($message, $error, $handle)
    {
        if ($handle instanceof NonCriticalDomainException) {
            Log::error($message, $error, self::EXCEPTIONS_CHANNEL);
        } else {
            Log::critical($message, $error, self::EXCEPTIONS_CHANNEL);
        }
    }
}