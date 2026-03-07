<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class FailedJobCentral extends Model
{
    protected $table = 'failed_jobs';

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'failed_at' => 'datetime',
        ];
    }

    public function getConnectionName(): ?string
    {
        return (string) Config::get('queue.failed.database', Config::get('database.default'));
    }

    public function getDisplayNameAttribute(): string
    {
        $payload = json_decode((string) $this->payload, true);

        if (! is_array($payload)) {
            return '-';
        }

        $displayName = (string) (
            $payload['displayName']
            ?? data_get($payload, 'data.commandName')
            ?? $payload['job']
            ?? '-'
        );

        if ($displayName === '') {
            return '-';
        }

        return Str::replace('\\', '/', $displayName);
    }

    public function getExceptionSummaryAttribute(): string
    {
        $firstLine = trim(strtok((string) $this->exception, "\n") ?: '');

        if ($firstLine === '') {
            return '-';
        }

        return Str::limit($firstLine, 180);
    }
}

