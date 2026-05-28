<?php

namespace Boostly\Laravel\Http\Middleware;

use Boostly\Laravel\Boostly;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyBoostlySignature
{
    public function __construct(protected Boostly $boostly)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $signature = $request->header('X-Boostly-Signature');

        // A NYERS body-n ellenőrzünk — nem az újraszerializált adaton,
        // különben az HMAC nem egyezne.
        if (! $this->boostly->verifySignature($request->getContent(), $signature)) {
            abort(403, 'Invalid Boostly webhook signature.');
        }

        return $next($request);
    }
}
