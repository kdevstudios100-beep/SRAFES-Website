<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    /**
     * Refuse to run unless the app points at a throwaway test database: in-memory
     * SQLite (phpunit.xml) or a MySQL database whose name ends in "_testing"
     * (phpunit.mysql.xml). This runs before RefreshDatabase migrates anything, so
     * a misconfigured environment can never touch real school data.
     */
    public function createApplication()
    {
        $app = parent::createApplication();

        $connection = $app['config']->get('database.default');
        $database = (string) $app['config']->get("database.connections.{$connection}.database");

        $isThrowaway = match ($connection) {
            'sqlite' => $database === ':memory:',
            'mysql', 'mariadb' => str_ends_with($database, '_testing'),
            default => false,
        };

        if ($app->configurationIsCached() || ! $isThrowaway) {
            throw new RuntimeException(
                "Tests aborted: expected in-memory SQLite or a MySQL database ending in _testing, got [{$connection}: {$database}]. "
                .'Run "php artisan config:clear" and check phpunit.xml.'
            );
        }

        return $app;
    }
}
