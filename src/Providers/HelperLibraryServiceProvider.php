<?php

namespace Keysoft\HelperLibrary\Providers;

use Keysoft\HelperLibrary\Support\RequestLogContext;
use Keysoft\HelperLibrary\Support\StructuredLogger;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Console\Output\ConsoleOutput;

class HelperLibraryServiceProvider extends ServiceProvider
{
    protected static array $jobStartedAt = [];

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/keysoft-lib-config.php',
            'keysoft-lib-config'
        );
    }

    public function boot()
    {
        $this->registerQueueLogging();

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

    private function registerQueueLogging(): void
    {
        Queue::before(function (JobProcessing $event): void {
            $jobId = $this->resolveJobId($event->job);
            self::$jobStartedAt[$jobId] = microtime(true);

            StructuredLogger::job('job_started', $this->buildJobContext(
                job: $event->job,
                connectionName: $event->connectionName
            ));
        });

        Queue::after(function (JobProcessed $event): void {
            $jobId = $this->resolveJobId($event->job);

            StructuredLogger::job('job_finished', $this->buildJobContext(
                job: $event->job,
                connectionName: $event->connectionName,
                extra: [
                    'runtime_ms' => $this->resolveJobRuntime($jobId),
                ]
            ));

            unset(self::$jobStartedAt[$jobId]);
        });

        Queue::failing(function (JobFailed $event): void {
            $jobId = $this->resolveJobId($event->job);

            StructuredLogger::exception(
                $event->exception,
                $this->buildJobContext(
                    job: $event->job,
                    connectionName: $event->connectionName,
                    extra: [
                        'runtime_ms' => $this->resolveJobRuntime($jobId),
                    ]
                ),
                'error',
                'job',
                'job_failed'
            );

            unset(self::$jobStartedAt[$jobId]);
        });
    }

    private function buildJobContext(object $job, string $connectionName, array $extra = []): array
    {
        $payload = method_exists($job, 'payload') ? $job->payload() : [];
        $payload = is_array($payload) ? $payload : [];

        return RequestLogContext::filter(array_merge([
            'job_id'     => $this->resolveJobId($job),
            'job_name'   => method_exists($job, 'resolveName') ? $job->resolveName() : ($payload['displayName'] ?? null),
            'queue'      => method_exists($job, 'getQueue') ? $job->getQueue() : null,
            'attempt'    => method_exists($job, 'attempts') ? $job->attempts() : null,
            'connection' => $connectionName,
        ], $extra));
    }

    private function resolveJobId(object $job): string
    {
        if (method_exists($job, 'uuid')) {
            $uuid = $job->uuid();

            if (is_string($uuid) && $uuid !== '') {
                return $uuid;
            }
        }

        if (method_exists($job, 'getJobId')) {
            $jobId = $job->getJobId();

            if ($jobId !== null && $jobId !== '') {
                return (string) $jobId;
            }
        }

        return spl_object_hash($job);
    }

    private function resolveJobRuntime(string $jobId): ?float
    {
        if (! array_key_exists($jobId, self::$jobStartedAt)) {
            return null;
        }

        return round((microtime(true) - self::$jobStartedAt[$jobId]) * 1000, 2);
    }
}
