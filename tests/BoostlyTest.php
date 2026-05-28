<?php

namespace Boostly\Laravel\Tests;

use Boostly\Laravel\Boostly;

class BoostlyTest extends TestCase
{
    private function boostly(): Boostly
    {
        return $this->app->make(Boostly::class);
    }

    public function test_snippet_url_uses_config_token(): void
    {
        $this->assertSame(
            'https://app.boostly.test/snippet.js?token=test-site-token',
            $this->boostly()->snippetUrl()
        );
    }

    public function test_snippet_url_accepts_explicit_token(): void
    {
        $this->assertSame(
            'https://app.boostly.test/snippet.js?token=other',
            $this->boostly()->snippetUrl('other')
        );
    }

    public function test_snippet_tag_renders_script(): void
    {
        $this->assertSame(
            '<script async src="https://app.boostly.test/snippet.js?token=test-site-token"></script>',
            (string) $this->boostly()->snippetTag()
        );
    }

    public function test_snippet_url_is_empty_without_token(): void
    {
        config(['boostly.site_token' => null]);

        $this->assertSame('', $this->boostly()->snippetUrl());
        $this->assertSame('', (string) $this->boostly()->snippetTag());
    }

    public function test_signature_matches_expected_format(): void
    {
        $body = '{"hello":"world"}';
        $expected = 'sha256='.hash_hmac('sha256', $body, 'test-secret');

        $this->assertSame($expected, $this->boostly()->signature($body));
    }

    public function test_verify_signature_accepts_valid_and_rejects_invalid(): void
    {
        $body = '{"hello":"world"}';
        $valid = $this->boostly()->signature($body);

        $this->assertTrue($this->boostly()->verifySignature($body, $valid));
        $this->assertFalse($this->boostly()->verifySignature($body, 'sha256=deadbeef'));
        $this->assertFalse($this->boostly()->verifySignature($body, null));
        $this->assertFalse($this->boostly()->verifySignature($body, ''));
    }
}
