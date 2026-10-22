<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    /**
     * Refuse to run unless the app points at an in-memory SQLite database.
     * This runs before RefreshDatabase migrates anything, so a misconfigured
     * environment (cached config, wrong .env) can never touch real school data.
     */
    public function createApplication()
    {
        $app = parent::createApplication();

        $connection = $app['config']->get('database.default');
        $database = $app['config']->get("database.connections.{$connection}.database");

        if ($app->configurationIsCached() || $connection !== 'sqlite' || $database !== ':memory:') {
            throw new RuntimeException(
                "Tests aborted: expected an in-memory SQLite database but got [{$connection}: {$database}]. "
                .'Run "php artisan config:clear" and check phpunit.xml.'
            );
        }

        return $app;
    }
}
