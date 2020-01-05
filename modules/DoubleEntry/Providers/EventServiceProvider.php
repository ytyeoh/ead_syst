<?php

namespace Modules\DoubleEntry\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\DoubleEntry\Listeners\AdminMenuCreated;
use Modules\DoubleEntry\Listeners\ModuleInstalled;
use Modules\DoubleEntry\Listeners\Updates\Version1012;
use Modules\DoubleEntry\Listeners\Updates\Version1015;
use Modules\DoubleEntry\Listeners\Updates\Version1024;
use Modules\DoubleEntry\Listeners\Updates\Version1026;
use Modules\DoubleEntry\Listeners\Updates\Version110;
use Modules\DoubleEntry\Listeners\Updates\Version111;
use Modules\DoubleEntry\Listeners\Updates\Version114;
use Modules\DoubleEntry\Listeners\Updates\Version117;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function boot()
    {
        $this->app['events']->listen(\App\Events\AdminMenuCreated::class, AdminMenuCreated::class);
        $this->app['events']->listen(\App\Events\ModuleInstalled::class, ModuleInstalled::class);
        $this->app['events']->listen(\App\Events\UpdateFinished::class, Version1012::class);
        $this->app['events']->listen(\App\Events\UpdateFinished::class, Version1015::class);
        $this->app['events']->listen(\App\Events\UpdateFinished::class, Version1024::class);
        $this->app['events']->listen(\App\Events\UpdateFinished::class, Version1026::class);
        $this->app['events']->listen(\App\Events\UpdateFinished::class, Version110::class);
        $this->app['events']->listen(\App\Events\UpdateFinished::class, Version111::class);
        $this->app['events']->listen(\App\Events\UpdateFinished::class, Version114::class);
        $this->app['events']->listen(\App\Events\UpdateFinished::class, Version117::class);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}