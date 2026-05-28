<?php

namespace Boostly\Laravel\Tests;

class WebhookDisabledTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);
        $app['config']->set('boostly.webhook.enabled', false);
    }

    public function test_route_is_not_registered_when_disabled(): void
    {
        $this->assertFalse(
            $this->app['router']->getRoutes()->hasNamedRoute('boostly.webhook')
        );

        $this->call('POST', 'boostly/webhook')->assertNotFound();
    }
}
