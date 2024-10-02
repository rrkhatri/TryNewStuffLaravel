<?php

namespace App\Providers;

use Facebook\WebDriver\WebDriverKeys;
use Illuminate\Support\ServiceProvider;
use Laravel\Dusk\Keyboard;
use Laravel\Dusk\OperatingSystem;

/**
 * @method type(array $array)
 * @method selectAll()
 * @method copy()
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Keyboard::macro('selectAll', function () {
            $this->type([OperatingSystem::onMac() ? WebDriverKeys::COMMAND : WebDriverKeys::CONTROL, 'a']);
        });

        Keyboard::macro('copy', function () {
            $this->type([OperatingSystem::onMac() ? WebDriverKeys::COMMAND : WebDriverKeys::CONTROL, 'c']);
        });

        Keyboard::macro('paste', function () {
            $this->type([OperatingSystem::onMac() ? WebDriverKeys::COMMAND : WebDriverKeys::CONTROL, 'v']);
        });

        Keyboard::macro('copyAll', function () {
            $this->selectAll();
            $this->copy();
        });
    }
}
