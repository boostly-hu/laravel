<?php

namespace Boostly\Laravel\Tests;

use Boostly\Laravel\Boostly;
use Boostly\Laravel\Contracts\BoostlySecretResolver;
use Boostly\Laravel\Events\BoostlyLeadReceived;
use Boostly\Laravel\Support\ConfigSecretResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;

class MultiTenantWebhookTest extends TestCase
{
    private const TENANT_SECRET = 'tenant-secret-xyz';

    private function bindResolver(?string $secret): void
    {
        $this->app->bind(BoostlySecretResolver::class, function () use ($secret) {
            return new class($secret) implements BoostlySecretResolver {
                public function __construct(private ?string $secret)
                {
                }

                public function resolve(Request $request): ?string
                {
                    return $this->secret;
                }
            };
        });
    }

    private function postWebhook(string $body, ?string $signature)
    {
        $server = ['CONTENT_TYPE' => 'application/json'];
        if ($signature !== null) {
            $server['HTTP_X_BOOSTLY_SIGNATURE'] = $signature;
        }

        return $this->call('POST', 'boostly/webhook', [], [], [], $server, $body);
    }

    public function test_middleware_validates_with_resolver_secret_not_config(): void
    {
        Event::fake([BoostlyLeadReceived::class]);
        $this->bindResolver(self::TENANT_SECRET);

        $body = json_encode(['event' => 'lead_submit', 'metadata' => ['email' => 'a@b.c']]);

        // A resolver tenant-secretjével aláírva → 200 + event.
        $validSignature = $this->app->make(Boostly::class)->signatureFor($body, self::TENANT_SECRET);
        $this->postWebhook($body, $validSignature)->assertOk();
        Event::assertDispatched(BoostlyLeadReceived::class);

        // A config secretjével aláírva → 403 (a resolver mást ad vissza).
        $configSignature = 'sha256='.hash_hmac('sha256', $body, 'test-secret');
        $this->postWebhook($body, $configSignature)->assertForbidden();
    }

    public function test_null_resolver_secret_is_forbidden(): void
    {
        $this->bindResolver(null);

        $body = json_encode(['event' => 'lead_submit']);

        $this->postWebhook($body, 'sha256=whatever')->assertForbidden();
    }

    public function test_default_resolver_reads_config_for_backward_compat(): void
    {
        $resolver = $this->app->make(BoostlySecretResolver::class);

        $this->assertInstanceOf(ConfigSecretResolver::class, $resolver);
        $this->assertSame('test-secret', $resolver->resolve(Request::create('/')));
    }
}
