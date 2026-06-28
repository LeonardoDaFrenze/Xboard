<?php

namespace App\Providers;

use App\Support\ProtocolManager;
use Illuminate\Support\ServiceProvider;

class ProtocolServiceProvider extends ServiceProvider
{
  /**
   * Translation
   *
   * @return void
   */
  public function register()
  {
    $this->app->scoped('protocols.manager', function ($app) {
      return new ProtocolManager($app);
    });

    $this->app->scoped('protocols.flags', function ($app) {
      return $app->make('protocols.manager')->getAllFlags();
    });
  }

  /**
   * Start Service
   *
   * @return void
   */
  public function boot()
  {
// Preload protocol classes and cache when starting
    $this->app->make('protocols.manager')->registerAllProtocols();

  }

  /**
   * Services Provided
   *
   * @return array
   */
  public function provides()
  {
    return [
      'protocols.manager',
      'protocols.flags',
    ];
  }
}