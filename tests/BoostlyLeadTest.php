<?php

namespace Boostly\Laravel\Tests;

use Boostly\Laravel\Data\BoostlyLead;
use PHPUnit\Framework\TestCase;

class BoostlyLeadTest extends TestCase
{
    public function test_maps_all_fields_from_full_payload(): void
    {
        $payload = [
            'event' => 'lead_submit',
            'campaign_id' => 'camp-1',
            'campaign_name' => 'Karácsonyi 10% kupon',
            'variant_id' => 'var-1',
            'site_id' => 'site-1',
            'metadata' => [
                'lead_id' => 'lead-1',
                'email' => 'lead@example.com',
                'name' => 'Teszt Elek',
                'consent' => [
                    'accepted' => true,
                    'text' => 'Elfogadom.',
                    'at' => '2026-05-28T20:19:19.307Z',
                ],
                'fields' => ['phone' => '+36301234567'],
                'coupon' => 'SAVE10',
                'ip' => '203.0.113.42',
                'user_agent' => 'TestAgent/1.0',
            ],
            'timestamp' => '2026-05-28T20:19:19.557Z',
        ];

        $lead = BoostlyLead::fromArray($payload);

        $this->assertSame('Karácsonyi 10% kupon', $lead->campaignName);
        $this->assertSame('2026-05-28T20:19:19.307Z', $lead->consentAt);
        $this->assertSame('203.0.113.42', $lead->ip);
        $this->assertSame('TestAgent/1.0', $lead->userAgent);

        // a meglévő mezők változatlanul
        $this->assertSame('lead@example.com', $lead->email);
        $this->assertSame('SAVE10', $lead->coupon);
        $this->assertTrue($lead->consentAccepted);
        $this->assertSame('+36301234567', $lead->fields['phone']);
    }

    public function test_new_fields_are_null_on_old_payload(): void
    {
        // Régi Boostly-szerver payloadja: nincsenek az új kulcsok.
        $payload = [
            'event' => 'lead_submit',
            'campaign_id' => 'camp-1',
            'variant_id' => 'var-1',
            'site_id' => 'site-1',
            'metadata' => [
                'lead_id' => 'lead-1',
                'email' => 'lead@example.com',
                'consent' => ['accepted' => true, 'text' => 'Elfogadom.'],
            ],
            'timestamp' => '2026-05-28T20:19:19.557Z',
        ];

        $lead = BoostlyLead::fromArray($payload);

        $this->assertNull($lead->campaignName);
        $this->assertNull($lead->consentAt);
        $this->assertNull($lead->ip);
        $this->assertNull($lead->userAgent);

        // a meglévő működés érintetlen
        $this->assertSame('lead@example.com', $lead->email);
        $this->assertTrue($lead->consentAccepted);
        $this->assertSame('Elfogadom.', $lead->consentText);
    }
}
