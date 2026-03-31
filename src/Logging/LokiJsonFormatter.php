<?php

namespace Keysoft\HelperLibrary\Logging;

use Monolog\Formatter\JsonFormatter;
use Monolog\LogRecord;

class LokiJsonFormatter extends JsonFormatter
{
    public function format(LogRecord $record): string
    {
        $context = $this->normalize($record->context);
        $extra   = $this->normalize($record->extra);

        // Merge context and extra into a flat payload for Loki ingestion.
        // System fields (timestamp, level, channel, message) are written last
        // to guarantee they are always present. Any context/extra key that
        // collides with a system field is prefixed with "ctx_" to preserve the
        // original value instead of silently discarding it.
        $systemKeys = ['timestamp', 'level', 'level_value', 'channel', 'message'];

        $merged = array_merge(
            is_array($context) ? $context : ['context' => $context],
            is_array($extra)   ? $extra   : ['extra'   => $extra],
        );

        foreach ($systemKeys as $key) {
            if (array_key_exists($key, $merged)) {
                $merged['ctx_' . $key] = $merged[$key];
                unset($merged[$key]);
            }
        }

        $payload = array_merge($merged, [
            'timestamp'   => $record->datetime->format(DATE_ATOM),
            'level'       => $record->level->getName(),
            'level_value' => $record->level->value,
            'channel'     => $record->channel,
            'message'     => $record->message,
        ]);

        return $this->toJson($payload, true).($this->appendNewline ? "\n" : '');
    }
}
