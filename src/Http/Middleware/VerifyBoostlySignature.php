<?php

namespace Boostly\Laravel\Http\Middleware;

use Boostly\Laravel\Boostly;
use Boostly\Laravel\Contracts\BoostlySecretResolver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyBoostlySignature
{
    public function __construct(
        protected Boostly $boostly,
        protected BoostlySecretResolver $resolver,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        // A secretet a resolver oldja fel a kérés alapján — így multi-tenant
        // host-appban tenant-onként más titok használható. Az alap resolver
        // a configból olvas (visszafelé kompatibilis).
        $secret = $this->resolver->resolve($request);

        if ($secret === null || $secret === '') {
            abort(403, 'No Boostly integration for this request.');
        }

        $signature = $request->header('X-Boostly-Signature');

        // A NYERS body-n ellenőrzünk — nem az újraszerializált adaton,
        // különben az HMAC nem egyezne.
        if (! $this->boostly->verifySignatureWith($request->getContent(), $signature, $secret)) {
            abort(403, 'Invalid Boostly webhook signature.');
        }

        return $next($request);
    }
}
