<?php

declare(strict_types=1);

namespace Linio\Common\Laminas\Middleware;

use Laminas\Diactoros\Response\JsonResponse;
use Linio\Common\Laminas\Exception\Base\DomainException;
use Linio\Common\Laminas\Exception\ExceptionTokens;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ConvertErrorToJsonResponse
{
    public const DEFAULT_STATUS_CODE = 500;

    /**
     * @param mixed $error
     */
    public function __invoke($error, ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return new JsonResponse(self::buildErrorBody($error), self::getStatusCode($error));
    }

    public static function buildErrorBody(mixed $error): array
    {
        switch ($error) {
            case $error instanceof DomainException:
                return self::buildDomainExceptionBody($error);
            case $error instanceof \Throwable:
                return self::buildThrowableBody($error);
            default:
                return self::buildGenericErrorBody($error);
        }
    }

    public static function getStatusCode(mixed $error): int
    {
        switch ($error) {
            case $error instanceof DomainException:
                return (int) $error->getCode();
            default:
                return self::DEFAULT_STATUS_CODE;
        }
    }

    private static function buildGenericErrorBody($error): array
    {
        return self::buildBody();
    }

    private static function buildThrowableBody(\Throwable $throwable): array
    {
        return self::buildGenericErrorBody($throwable->getMessage());
    }

    private static function buildDomainExceptionBody(DomainException $domainException): array
    {
        return self::buildBody(
            $domainException->getToken(),
            $domainException->getMessage(),
            $domainException->getErrors()
        );
    }

    private static function buildBody(
        string $code = ExceptionTokens::AN_ERROR_HAS_OCCURRED,
        string $message = 'A unexpected error has occurred. Please check the logs for more information.',
        array $errors = []
    ): array {
        return [
            'code' => $code,
            'message' => $message,
            'errors' => $errors,
        ];
    }
}
