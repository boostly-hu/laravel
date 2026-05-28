# Boostly for Laravel

Boostly-integráció Laravelhez: kampány-snippet beágyazása egy Blade-direktívával, és
lead-webhookok fogadása beépített HMAC-aláírás-ellenőrzéssel.

## Telepítés

```bash
composer require boostly-hu/laravel
```

A csomag auto-discovery-vel regisztrálódik. A config publikálása opcionális:

```bash
php artisan vendor:publish --tag=boostly-config
```

## Konfiguráció

`.env`:

```dotenv
BOOSTLY_URL=https://boostly.hu
BOOSTLY_SITE_TOKEN=az-oldalad-publikus-tokenje
BOOSTLY_WEBHOOK_SECRET=a-webhook-vegpont-titka
```

- **`BOOSTLY_URL`** — a Boostly alap-URL-je (innen töltődik a `snippet.js`). Alapból `https://boostly.hu`, így általában el is hagyható; csak saját példánynál kell felülírni.
- **`BOOSTLY_SITE_TOKEN`** — az oldalhoz tartozó publikus token (Boostly admin → oldal).
- **`BOOSTLY_WEBHOOK_SECRET`** — a webhook-végpont titka (Boostly admin → webhook). Enélkül
  minden beérkező webhook **403**-at kap.

## 1) Snippet beágyazása

A layout `<head>` (vagy a `</body>` elé) Blade-sablonjába:

```blade
@boostlySnippet
```

Ez ezt rendereli:

```html
<script async src="https://app.boostly.io/snippet.js?token=AZ-OLDAL-TOKENJE"></script>
```

Másik tokent is megadhatsz (pl. multi-site):

```blade
@boostlySnippet($oldal->boostly_token)
```

Vagy a facade-dal, programból:

```php
use Boostly\Laravel\Facades\Boostly;

Boostly::snippetTag();          // \Illuminate\Support\HtmlString
Boostly::snippetUrl('token');   // string
```

## 2) Lead-webhook fogadása

A csomag automatikusan regisztrál egy route-ot:

```
POST /boostly/webhook
```

Ezt add meg a Boostly adminban a webhook-végpont URL-jeként
(pl. `https://sajat-oldalad.hu/boostly/webhook`). Az útvonal és a route engedélyezése
a configban állítható (`boostly.webhook.path`, `boostly.webhook.enabled`).

A beérkező (és aláírás-ellenőrzött) leadre a `BoostlyLeadReceived` event sül el. Iratkozz fel rá,
és innen küldd tovább a leadet (CRM, hírlevél, saját tábla, stb.):

```php
use Boostly\Laravel\Events\BoostlyLeadReceived;
use Illuminate\Support\Facades\Event;

Event::listen(function (BoostlyLeadReceived $event) {
    $lead = $event->lead;

    $lead->email;            // string|null
    $lead->name;             // string|null
    $lead->fields;           // array<string,string> — beküldött mezők
    $lead->coupon;           // string|null
    $lead->consentAccepted;  // bool
    $lead->consentText;      // string|null  (GDPR audit)
    $lead->consentAt;        // string|null  (a beleegyezés ideje, ISO 8601 UTC)
    $lead->ip;               // string|null  (a feliratkozó IP-je)
    $lead->userAgent;        // string|null
    $lead->campaignId;
    $lead->campaignName;     // string|null  (ember által olvasható kampánynév)
    $lead->variantId;
    $lead->siteId;
    $lead->raw;              // a teljes nyers payload
});
```

> A `BoostlyLeadReceived` egy egyszerű event-osztály — kösd hozzá a listenered az
> `EventServiceProvider`-ben is, ha úgy kényelmesebb.

> A `consentAt`, `ip`, `userAgent` és `campaignName` mezőket a Boostly szerver a
> payload-bővítő verziótól felfelé tölti; régebbi szerverrel ezek `null`-ok
> (a csomag visszafelé kompatibilis).

### Aláírás-ellenőrzés

Minden beérkező kérés átmegy a `VerifyBoostlySignature` middleware-en, ami az
`X-Boostly-Signature: sha256=<hmac>` fejlécet ellenőrzi a **nyers** kérés-body-n,
HMAC-SHA256-tal, a `BOOSTLY_WEBHOOK_SECRET` titkkal (timing-safe `hash_equals`).
Hibás vagy hiányzó aláírás → **403**.

Extra middleware-t a configban adhatsz (az aláírás-ellenőrzés mindig fut):

```php
// config/boostly.php
'webhook' => [
    'middleware' => ['throttle:60,1'],
],
```

## Multi-tenant / több webáruház

Ha egy kódbázison több webáruház (tenant) fut, és **tenant-onként más a webhook
secret** (jellemzően adatbázisban tárolva, nem `.env`-ben), két opciód van.

### A) Saját webhook-endpoint

Kapcsold ki a beépített route-ot, és vidd a webhookot a host-appban
(így a csomagból csak a `BoostlyLead` DTO-t + a `BoostlyLeadReceived` eventet
használod):

```dotenv
BOOSTLY_WEBHOOK_ENABLED=false
```

### B) Beépített route + per-tenant secret-resolver

Ha a beépített route-ot szeretnéd használni, de a secret futásidőben,
tenant-onként dől el, írd felül a `BoostlySecretResolver`-t a host-app egy
service providerében. A middleware ezen keresztül oldja fel a titkot a beérkező
request alapján; `null` válasz → **403** (nincs integráció ehhez a kéréshez):

```php
use Boostly\Laravel\Contracts\BoostlySecretResolver;
use Illuminate\Http\Request;

$this->app->bind(BoostlySecretResolver::class, function () {
    return new class implements BoostlySecretResolver {
        public function resolve(Request $request): ?string
        {
            // pl. a kérés domainjéből feloldott tenant secretje
            $tenant = app(\App\Services\Tenancy\TenantResolver::class)
                ->fromHost($request->getHost());

            return $tenant?->boostly_webhook_secret;
        }
    };
});
```

> Alapból a `ConfigSecretResolver` fut, ami a `BOOSTLY_WEBHOOK_SECRET`-et
> használja — vagyis az egyszeri `.env`-es beállítás változtatás nélkül működik.

## Tesztek

```bash
composer install
vendor/bin/phpunit
```

## Licenc

MIT
