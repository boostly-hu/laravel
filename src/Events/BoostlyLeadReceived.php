<?php

namespace Boostly\Laravel\Events;

use Boostly\Laravel\Data\BoostlyLead;

/**
 * Akkor sül el, amikor a Boostly egy lead-webhookot kézbesít, és az
 * aláírás-ellenőrzés sikeres volt. Iratkozz fel rá a host-appodban a
 * lead továbbküldéséhez (CRM, hírlevél, stb.).
 */
class BoostlyLeadReceived
{
    /**
     * @param  array<string, mixed>  $payload  A nyers, dekódolt payload.
     */
    public function __construct(
        public readonly BoostlyLead $lead,
        public readonly array $payload,
    ) {
    }
}
