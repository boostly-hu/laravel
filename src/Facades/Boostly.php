<?php

namespace Boostly\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Illuminate\Support\HtmlString snippetTag(?string $token = null)
 * @method static string snippetUrl(?string $token = null)
 * @method static string signature(string $payload)
 * @method static string signatureFor(string $payload, string $secret)
 * @method static bool verifySignature(string $payload, ?string $signature)
 * @method static bool verifySignatureWith(string $payload, ?string $signature, ?string $secret)
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
