# Changelog

## v0.2.0

Multi-tenant támogatás. **Nincs breaking change** — aki egyetlen `.env`-es
secrettel használja, annak változtatás nélkül tovább működik.

### Új

- **`BoostlySecretResolver` interfész** (`Boostly\Laravel\Contracts`): a host-app
  a beérkező request alapján adhatja vissza a megfelelő webhook-secretet
  (pl. a kérés domainjéből feloldott tenant titka). Felülírható a host-app
  service providerében.
- **`ConfigSecretResolver` default** (`Boostly\Laravel\Support`): a configból
  (`BOOSTLY_WEBHOOK_SECRET`) olvas — ez adja a korábbi viselkedést.
- **`Boostly::signatureFor()` / `Boostly::verifySignatureWith()`**: explicit
  secrettel dolgozó aláírás-metódusok.

### Változás

- A `VerifyBoostlySignature` middleware mostantól a `BoostlySecretResolver`-en
  keresztül oldja fel a secretet (nem közvetlenül a configból). A default
  resolver miatt a viselkedés alapból változatlan.
- A beépített `POST /boostly/webhook` route továbbra is kikapcsolható
  (`BOOSTLY_WEBHOOK_ENABLED=false`), ha a host-app saját webhook-endpointot visz.

## v0.1.2

- A `BOOSTLY_URL` default a valós hostolt Boostly (`https://boostly.hu`).

## v0.1.1

- A `BOOSTLY_URL`-ből eltávolítva a félrevezető placeholder default.

## v0.1.0

- Első kiadás: `@boostlySnippet` Blade-direktíva + `Boostly` facade,
  `POST /boostly/webhook` route, `VerifyBoostlySignature` middleware
  (HMAC-SHA256), `BoostlyLeadReceived` event + `BoostlyLead` DTO.
