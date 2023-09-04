<?php

declare(strict_types=1);

namespace Linio\Common\Laminas\Exception;

class ExceptionTokens
{
    public const CONTENT_TYPE_NOT_SUPPORTED = 'CONTENT_TYPE_NOT_SUPPORTED';
    public const INVALID_REQUEST = 'INVALID_REQUEST';
    public const ENTITY_NOT_FOUND = 'ENTITY_NOT_FOUND';
    public const RUNTIME_EXCEPTION = 'RUNTIME_EXCEPTION';
    public const AN_ERROR_HAS_OCCURRED = 'AN_ERROR_HAS_OCCURRED';
    public const MIDDLEWARE_RUN_OUT_OF_ORDER = 'MIDDLEWARE_RUN_OUT_OF_ORDER';
    public const VALIDATION_RULES_NOT_FOUND = 'VALIDATION_RULES_NOT_FOUND';
    public const REQUEST_BODY_EMPTY_OR_NOT_CONVERTED_TO_ARRAY = 'REQUEST_BODY_EMPTY_OR_NOT_CONVERTED_TO_ARRAY';
    public const ROUTE_NOT_FOUND = 'ROUTE_NOT_FOUND';
    public const SHIPPING_ESTIMATE_INVALID_REQUEST = 'SHIPPING_ESTIMATE_INVALID_REQUEST';
}
