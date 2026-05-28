<?php

namespace Boostly\Laravel;

use Illuminate\Support\HtmlString;

class Boostly
{
    /**
     * A snippet.js teljes URL-je a megadott (vagy konfigurált) tokennel.
     * Üres stringet ad, ha hiányzik a token vagy az URL.
     */
    public function snippetUrl(?string $token = null): string
    {
        $token = $token ?: (string) config('boostly.site_token');
        $base = rtrim((string) config('boostly.url'), '/');

        if ($token === '' || $base === '') {
            return '';
        }

        return $base.'/snippet.js?token='.urlencode($token);
    }

    /**
     * A beágyazandó <script> tag. Bladeben a @boostlySnippet direktíván
     * keresztül a legkényelmesebb. HtmlString → Blade nem escape-eli újra.
     */
    public function snippetTag(?string $token = null): HtmlString
    {
        $url = $this->snippetUrl($token);

        if ($url === '') {
            return new HtmlString('');
        }

        return new HtmlString(
            '<script async src="'.htmlspecialchars($url, ENT_QUOTES).'"></script>'
        );
    }

    /**
     * Aláírás kiszámítása egy EXPLICIT secrettel, a Boostly szerver
     * formátumában: "sha256=<hex>".
     */
    public function signatureFor(string $payload, string $secret): string
    {
        return 'sha256='.hash_hmac('sha256', $payload, $secret);
    }

    /**
     * Aláírás ellenőrzése egy EXPLICIT secrettel (timing-safe).
     * A $payload a NYERS kérés-body legyen, nem újraszerializált adat.
     */
    public function verifySignatureWith(string $payload, ?string $signature, ?string $secret): bool
    {
        if ($secret === null || $secret === '' || $signature === null || $signature === '') {
            return false;
        }

        return hash_equals($this->signatureFor($payload, $secret), $signature);
    }

    /**
     * A payloadhoz tartozó aláírás a KONFIGURÁLT secrettel (kényelmi metódus).
     */
    public function signature(string $payload): string
    {
        return $this->signatureFor($payload, (string) config('boostly.webhook_secret'));
    }

    /**
     * Beérkező webhook aláírásának ellenőrzése a KONFIGURÁLT secrettel.
     * A $payload a NYERS kérés-body legyen, nem újraszerializált adat.
     */
    public function verifySignature(string $payload, ?string $signature): bool
    {
        return $this->verifySignatureWith($payload, $signature, config('boostly.webhook_secret') ?: null);
    }
}
