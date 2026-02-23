<?php

namespace Keysoft\HelperLibrary\Providers;

use Illuminate\Console\Events\CommandStarting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Console\Output\ConsoleOutput;

class HelperLibraryServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/keysoft-lib-config.php',
            'keysoft-lib-config'
        );
    }

    public function boot()
    {
        if (! app()->runningInConsole()) {
            return;
        }

        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__ . '/../../config/keysoft-lib-config.php'
            => $this->app->configPath('keysoft-lib-config.php'),
        ], 'keysoft-config');

        $guard = Config::get('keysoft-lib-config.command_guard');

        if (! ($guard['enabled'] ?? false)) {
            return;
        }

        Event::listen(CommandStarting::class, function ($event) use ($guard) {

            $command = $event->command ?? '';

            foreach ($guard['blocked_commands'] ?? [] as $blocked) {

                if (str_starts_with($command, $blocked)) {

                    if (in_array($command, $guard['except'] ?? [])) {
                        return;
                    }

                    $output = new ConsoleOutput();
                    $output->writeln(
                        "<comment>{$guard['message']}</comment>"
                    );

                    exit(0);
                }
            }

        });
    }
}