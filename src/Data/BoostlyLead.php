<?php

namespace Boostly\Laravel\Data;

/**
 * Egy beérkező Boostly lead-webhook (lead_submit) tipizált reprezentációja.
 */
class BoostlyLead
{
    /**
     * @param  array<string, string>  $fields  A beküldött form-mezők.
     * @param  array<string, mixed>  $raw      A nyers, dekódolt payload.
     */
    public function __construct(
        public readonly ?string $event,
        public readonly ?string $email,
        public readonly ?string $name,
        public readonly ?string $leadId,
        public readonly ?string $campaignId,
        public readonly ?string $campaignName,
        public readonly ?string $variantId,
        public readonly ?string $siteId,
        public readonly ?string $coupon,
        public readonly bool $consentAccepted,
        public readonly ?string $consentText,
        public readonly ?string $consentAt,
        public readonly array $fields,
        public readonly ?string $ip,
        public readonly ?string $userAgent,
        public readonly ?string $timestamp,
        public readonly array $raw,
    ) {
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromArray(array $payload): self
    {
        $meta = (array) ($payload['metadata'] ?? []);
        $consent = (array) ($meta['consent'] ?? []);

        return new self(
            event: isset($payload['event']) ? (string) $payload['event'] : null,
            email: isset($meta['email']) ? (string) $meta['email'] : null,
            name: isset($meta['name']) ? (string) $meta['name'] : null,
            leadId: isset($meta['lead_id']) ? (string) $meta['lead_id'] : null,
            campaignId: isset($payload['campaign_id']) ? (string) $payload['campaign_id'] : null,
            campaignName: isset($payload['campaign_name']) ? (string) $payload['campaign_name'] : null,
            variantId: isset($payload['variant_id']) ? (string) $payload['variant_id'] : null,
            siteId: isset($payload['site_id']) ? (string) $payload['site_id'] : null,
            coupon: isset($meta['coupon']) ? (string) $meta['coupon'] : null,
            consentAccepted: (bool) ($consent['accepted'] ?? false),
            consentText: isset($consent['text']) ? (string) $consent['text'] : null,
            consentAt: isset($consent['at']) ? (string) $consent['at'] : null,
            fields: (array) ($meta['fields'] ?? []),
            ip: isset($meta['ip']) ? (string) $meta['ip'] : null,
            userAgent: isset($meta['user_agent']) ? (string) $meta['user_agent'] : null,
            timestamp: isset($payload['timestamp']) ? (string) $payload['timestamp'] : null,
            raw: $payload,
        );
    }
}
