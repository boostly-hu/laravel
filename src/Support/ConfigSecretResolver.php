<?php

namespace Boostly\Laravel\Support;

use Boostly\Laravel\Contracts\BoostlySecretResolver;
use Illuminate\Http\Request;

/**
 * Alapértelmezett resolver: a configból (BOOSTLY_WEBHOOK_SECRET) olvas.
 * Ez adja a v0.2.0 előtti, egyetlen .env-es secrettel működő viselkedést.
 */
class ConfigSecretResolver implements BoostlySecretResolver
{
    public function resolve(Request $request): ?string
    {
        return config('boostly.webhook_secret') ?: null;
    }
}
