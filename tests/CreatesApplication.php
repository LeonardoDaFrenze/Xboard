<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;

trait CreatesApplication
{
    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__ . '/../bootstrap/app.php';

        if (!file_exists(base_path('.env'))) {
            @touch(base_path('.env'));
        }

        $app->make(Kernel::class)->bootstrap();

        \Illuminate\Support\Facades\Redis::spy();
        config(['cache.stores.redis.driver' => 'array']);

        return $app;
    }
}

