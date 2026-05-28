<?php

namespace Boostly\Laravel;

use Boostly\Laravel\Contracts\BoostlySecretResolver;
use Boostly\Laravel\Http\Controllers\WebhookController;
use Boostly\Laravel\Http\Middleware\VerifyBoostlySignature;
use Boostly\Laravel\Support\ConfigSecretResolver;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class BoostlyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/boostly.php', 'boostly');

        $this->app->singleton(Boostly::class, fn () => new Boostly());
        $this->app->alias(Boostly::class, 'boostly');

        // Felülírható a host-app service providerében a per-tenant secrethez.
        $this->app->bind(BoostlySecretResolver::class, ConfigSecretResolver::class);
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/boostly.php' => $this->app->configPath('boostly.php'),
        ], 'boostly-config');

        // @boostlySnippet  vagy  @boostlySnippet($token)
        Blade::directive('boostlySnippet', function (string $expression) {
            return "<?php echo app('boostly')->snippetTag({$expression}); ?>";
        });

        $this->registerWebhookRoute();
    }

    protected function registerWebhookRoute(): void
    {
        if (! config('boostly.webhook.enabled', true)) {
            return;
        }

        // Az aláírás-ellenőrzés MINDIG fut; a config middleware csak extra.
        $middleware = array_merge(
            (array) config('boostly.webhook.middleware', []),
            [VerifyBoostlySignature::class],
        );

        Route::middleware($middleware)
            ->post(config('boostly.webhook.path', 'boostly/webhook'), WebhookController::class)
            ->name('boostly.webhook');
    }
}
