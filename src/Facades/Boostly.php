<?php

namespace Boostly\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Illuminate\Support\HtmlString snippetTag(?string $token = null)
 * @method static string snippetUrl(?string $token = null)
 * @method static string signature(string $payload)
 * @method static bool verifySignature(string $payload, ?string $signature)
 *
 * @see \Boostly\Laravel\Boostly
 */
class Boostly extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Boostly\Laravel\Boostly::class;
    }
}
