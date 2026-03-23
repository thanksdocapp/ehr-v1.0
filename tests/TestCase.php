<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Bootstrap the application. Force MySQL as the default connection during testing so
     * RefreshDatabase can run migrations (many are MySQL-specific; .env.testing often sets sqlite).
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();

        if ($app->environment('testing')) {
            $app['config']->set('database.default', 'mysql');
            // .env.testing often points DB_DATABASE at SQLite; MySQL needs a real schema name for migrations.
            $app['config']->set('database.connections.mysql.host', env('DB_TEST_HOST', env('DB_HOST', '127.0.0.1')));
            $app['config']->set('database.connections.mysql.port', env('DB_TEST_PORT', env('DB_PORT', '3306')));
            $app['config']->set('database.connections.mysql.database', env('DB_TEST_DATABASE', 'ehr_testing'));
            $app['config']->set('database.connections.mysql.username', env('DB_TEST_USERNAME', env('DB_USERNAME', 'root')));
            $app['config']->set('database.connections.mysql.password', env('DB_TEST_PASSWORD', env('DB_PASSWORD', '')));
        }

        return $app;
    }
}
