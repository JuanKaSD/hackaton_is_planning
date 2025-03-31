<?php

namespace App\Exceptions;

use Exception;

class RateLimiterNotFoundException extends Exception
{
    /**
     * Create a new exception for a rate limiter that doesn't exist.
     *
     * @param  string  $limiter
     * @return static
     */
    public static function forLimiter(string $limiter)
    {
        return new static("Rate limiter [{$limiter}] is not defined.");
    }
}
