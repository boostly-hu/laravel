<?php

namespace Boostly\Laravel\Tests;

use Boostly\Laravel\Boostly;
use Boostly\Laravel\Events\BoostlyLeadReceived;
use Illuminate\Support\Facades\Event;

class WebhookTest extends TestCase
{
    /**
     * @return array<string, mixed>
     */
    private function samplePayload(): array
    {
        return [
            'event' => 'lead_submit',
            'campaign_id' => 'camp-1',
            'variant_id' => 'var-1',
            'site_id' => 'site-1',
            'metadata' => [
                'lead_id' => 'lead-1',
                'email' => 'lead@example.com',
                'name' => 'Teszt Elek',
                'consent' => ['accepted' => true, 'text' => 'Elfogadom.'],
                'fields' => ['phone' => '+36301234567'],
                'coupon' => 'SAVE10',
            ],
            'timestamp' => '2026-05-28T12:00:00.000Z',
        ];
    }

    private function postWebhook(string $body, ?string $signature)
    {
        $server = ['CONTENT_TYPE' => 'application/json'];
        if ($signature !== null) {
            $server['HTTP_X_BOOSTLY_SIGNATURE'] = $signature;
        }

        return $this->call('POST', 'boostly/webhook', [], [], [], $server, $body);
    }

    public function test_valid_signature_dispatches_event(): void
    {
        Event::fake([BoostlyLeadReceived::class]);

        $body = json_encode($this->samplePayload());
        $signature = $this->app->make(Boostly::class)->signature($body);

        $this->postWebhook($body, $signature)->assertOk()->assertJson(['ok' => true]);

        Event::assertDispatched(BoostlyLeadReceived::class, function (BoostlyLeadReceived $e) {
            return $e->lead->email === 'lead@example.com'
                && $e->lead->coupon === 'SAVE10'
                && $e->lead->consentAccepted === true
                && $e->lead->fields['phone'] === '+36301234567';
        });
    }

    public function test_invalid_signature_is_rejected(): void
    {
        Event::fake([BoostlyLeadReceived::class]);

        $body = json_encode($this->samplePayload());

        $this->postWebhook($body, 'sha256=deadbeef')->assertForbidden();

        Event::assertNotDispatched(BoostlyLeadReceived::class);
    }

    public function test_missing_signature_is_rejected(): void
    {
        Event::fake([BoostlyLeadReceived::class]);

        $body = json_encode($this->samplePayload());

        $this->postWebhook($body, null)->assertForbidden();

        Event::assertNotDispatched(BoostlyLeadReceived::class);
    }
}
