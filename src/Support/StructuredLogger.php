<?php

namespace Keysoft\HelperLibrary\Support;

use Illuminate\Support\Facades\Log;
use Throwable;

class StructuredLogger
{
    public static function access(string $event, array $context = [], string $level = 'info', ?string $message = null): void
    {
        self::write('access', $event, $context, $level, $message);
    }

    public static function application(string $event, array $context = [], string $level = 'info', ?string $message = null): void
    {
        self::write('application', $event, $context, $level, $message);
    }

    public static function audit(string $event, array $context = [], string $level = 'info', ?string $message = null): void
    {
        self::write('audit', $event, $context, $level, $message);
    }

    public static function security(string $event, array $context = [], string $level = 'warning', ?string $message = null): void
    {
        self::write('security', $event, $context, $level, $message);
    }

    public static function job(string $event, array $context = [], string $level = 'info', ?string $message = null): void
    {
        self::write('job', $event, $context, $level, $message);
    }

    public static function exception(Throwable $exception, array $context = [], string $level = 'error', string $category = 'error', ?string $event = null): void
    {
        self::write(
            $category,
            $event ?? 'exception',
            array_merge($context, [
                'exception_class'   => $exception::class,
                'exception_message' => $exception->getMessage(),
                'exception_code'    => $exception->getCode(),
                'exception_file'    => $exception->getFile(),
                'exception_line'    => $exception->getLine(),
            ]),
            $level,
            $exception->getMessage() !== '' ? $exception->getMessage() : ($event ?? 'exception')
        );
    }

    public static function error(string $event, array $context = [], string $level = 'error', ?string $message = null): void
    {
        self::write('error', $event, $context, $level, $message);
    }

    private static function write(string $category, string $event, array $context, string $level, ?string $message = null): void
    {
        Log::log(
            $level,
            $message ?? $event,
            RequestLogContext::filter(array_merge([
                'category' => $category,
                'event'    => $event,
            ], $context))
        );
    }
}
