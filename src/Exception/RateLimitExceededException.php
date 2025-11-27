<?php

declare(strict_types=1);

namespace FreeScout\Api\Exception;

use GuzzleHttp\Exception\RequestException;

class RateLimitExceededException extends RequestException implements Exception
{
}
