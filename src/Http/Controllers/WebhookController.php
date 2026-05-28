<?php

namespace Boostly\Laravel\Http\Controllers;

use Boostly\Laravel\Data\BoostlyLead;
use Boostly\Laravel\Events\BoostlyLeadReceived;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController
{
    /**
     * A beérkező Boostly webhookot feldolgozza: tipizált lead-objektumot
     * épít, majd elsüti a BoostlyLeadReceived eventet. Az aláírást a
     * VerifyBoostlySignature middleware már ellenőrizte.
     */
    public function __invoke(Request $request): JsonResponse
    {
        /** @var array<string, mixed> $payload */
        $payload = (array) $request->json()->all();

        $lead = BoostlyLead::fromArray($payload);

        event(new BoostlyLeadReceived($lead, $payload));

        return response()->json(['ok' => true]);
    }
}
