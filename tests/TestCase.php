<?php

namespace Boostly\Laravel\Tests;

use Boostly\Laravel\BoostlyServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            BoostlyServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('boostly.url', 'https://app.boostly.test');
        $app['config']->set('boostly.site_token', 'test-site-token');
        $app['config']->set('boostly.webhook_secret', 'test-secret');
    }
}
