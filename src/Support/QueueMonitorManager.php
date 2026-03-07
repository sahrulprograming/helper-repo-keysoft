<?php

namespace App\Support;

use Illuminate\Contracts\Queue\Queue as QueueContract;
use Illuminate\Database\QueryException;
use Illuminate\Queue\RedisQueue;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class QueueMonitorManager
{
    public function getDefaultConnectionName(): string
    {
        return (string) Config::get('queue.default', 'database');
    }

    /**
     * @return array<int, array{
     *     queue: string,
     *     pending: int|null,
     *     delayed: int|null,
     *     reserved: int|null,
     *     total: int|null
     * }>
     */
    public function getQueueStats(): array
    {
        $connectionName = $this->getDefaultConnectionName();
        $queueNames = $this->getQueueNames($connectionName);

        $stats = [];

        foreach ($queueNames as $queueName) {
            $stats[] = $this->getQueueStatFor($connectionName, $queueName);
        }

        return $stats;
    }

    public function getSummaryText(): string
    {
        $connectionName = $this->getDefaultConnectionName();
        $queueStats = $this->getQueueStats();
        $failedCount = $this->getFailedJobsCount();
        $runningCount = $this->getRunningJobsCount();

        if ($queueStats === []) {
            return "Queue connection [{$connectionName}] tidak memiliki queue terdeteksi. Running: {$runningCount}, Failed: {$failedCount}.";
        }

        $queueSummary = implode(' | ', array_map(
            fn (array $stat): string => $this->formatQueueSummary($stat),
            $queueStats,
        ));

        return "Queue connection: {$connectionName} | {$queueSummary} | Running jobs: {$runningCount} | Failed jobs: {$failedCount}";
    }

    public function getRunningJobsCount(): int
    {
        $total = 0;
        $hasNumericStats = false;

        foreach ($this->getQueueStats() as $stat) {
            foreach (['pending', 'reserved', 'delayed'] as $key) {
                $value = $stat[$key] ?? null;

                if (! is_int($value)) {
                    continue;
                }

                $hasNumericStats = true;
                $total += $value;
            }
        }

        if ($hasNumericStats) {
            return $total;
        }

        return count($this->getRunningQueueRows());
    }

    public function getFailedJobsCount(): int
    {
        $failedDatabase = $this->getFailedJobsDatabase();
        $failedTable = $this->getFailedJobsTable();

        try {
            if (! Schema::connection($failedDatabase)->hasTable($failedTable)) {
                return 0;
            }

            return (int) DB::connection($failedDatabase)
                ->table($failedTable)
                ->count();
        } catch (Throwable) {
            return 0;
        }
    }

    public function getFailedJobsTableExists(): bool
    {
        $failedDatabase = $this->getFailedJobsDatabase();
        $failedTable = $this->getFailedJobsTable();

        try {
            return Schema::connection($failedDatabase)->hasTable($failedTable);
        } catch (Throwable) {
            return false;
        }
    }

    public function deleteFailedJobByUuid(string $uuid): bool
    {
        $failedDatabase = $this->getFailedJobsDatabase();
        $failedTable = $this->getFailedJobsTable();

        if ($uuid === '') {
            return false;
        }

        try {
            if (! Schema::connection($failedDatabase)->hasTable($failedTable)) {
                return false;
            }

            return DB::connection($failedDatabase)
                    ->table($failedTable)
                    ->where('uuid', $uuid)
                    ->delete() > 0;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * @return array<int, array{
     *     key: string,
     *     record_type: 'running',
     *     state: 'pending'|'reserved'|'delayed',
     *     queue: string,
     *     connection: string,
     *     job: string,
     *     attempts: int|null,
     *     time: string,
     *     info: string,
     *     info_full: string,
     *     uuid: string|null
     * }>
     */
    public function getRunningQueueRows(int $limitPerState = 30): array
    {
        $connectionName = $this->getDefaultConnectionName();
        $queueNames = $this->getQueueNames($connectionName);
        $queue = $this->resolveQueueConnection($connectionName);

        if (! ($queue instanceof RedisQueue)) {
            return [];
        }

        $redis = $queue->getConnection();
        $rows = [];

        foreach ($queueNames as $queueName) {
            $baseKey = $queue->getQueue($queueName);

            $rows = array_merge(
                $rows,
                $this->extractPendingRows(
                    redis: $redis,
                    baseKey: $baseKey,
                    queueName: $queueName,
                    connectionName: $connectionName,
                    limit: $limitPerState,
                ),
                $this->extractSortedSetRows(
                    redis: $redis,
                    key: "{$baseKey}:reserved",
                    queueName: $queueName,
                    connectionName: $connectionName,
                    state: 'reserved',
                    limit: $limitPerState,
                ),
                $this->extractSortedSetRows(
                    redis: $redis,
                    key: "{$baseKey}:delayed",
                    queueName: $queueName,
                    connectionName: $connectionName,
                    state: 'delayed',
                    limit: $limitPerState,
                ),
            );
        }

        return $rows;
    }

    public function getFailedJobsDatabase(): string
    {
        return (string) Config::get('queue.failed.database', Config::get('database.default'));
    }

    public function getFailedJobsTable(): string
    {
        return (string) Config::get('queue.failed.table', 'failed_jobs');
    }

    /**
     * @return array<int, string>
     */
    private function getQueueNames(string $connectionName): array
    {
        $configuredQueue = (string) data_get(Config::get("queue.connections.{$connectionName}"), 'queue', 'default');

        $names = array_values(array_filter(array_map(
            fn (string $name): string => trim($name),
            explode(',', $configuredQueue),
        )));

        if ($names === []) {
            $names = ['default'];
        }

        $failedQueues = $this->getFailedQueues();

        return array_values(array_unique(array_merge($names, $failedQueues)));
    }

    /**
     * @return QueueContract|null
     */
    private function resolveQueueConnection(string $connectionName): ?QueueContract
    {
        try {
            return Queue::connection($connectionName);
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @return array<int, string>
     */
    private function getFailedQueues(): array
    {
        $failedDatabase = $this->getFailedJobsDatabase();
        $failedTable = $this->getFailedJobsTable();

        try {
            if (! Schema::connection($failedDatabase)->hasTable($failedTable)) {
                return [];
            }

            return DB::connection($failedDatabase)
                ->table($failedTable)
                ->whereNotNull('queue')
                ->distinct()
                ->pluck('queue')
                ->filter(fn (?string $queue): bool => filled($queue))
                ->values()
                ->all();
        } catch (QueryException) {
            return [];
        } catch (Throwable) {
            return [];
        }
    }

    /**
     * @return array{
     *     queue: string,
     *     pending: int|null,
     *     delayed: int|null,
     *     reserved: int|null,
     *     total: int|null
     * }
     */
    private function getQueueStatFor(string $connectionName, string $queueName): array
    {
        $queue = $this->resolveQueueConnection($connectionName);

        if (! $queue) {
            return [
                'queue' => $queueName,
                'pending' => null,
                'delayed' => null,
                'reserved' => null,
                'total' => null,
            ];
        }

        if ($queue instanceof RedisQueue) {
            return [
                'queue' => $queueName,
                'pending' => (int) $queue->pendingSize($queueName),
                'delayed' => (int) $queue->delayedSize($queueName),
                'reserved' => (int) $queue->reservedSize($queueName),
                'total' => (int) $queue->size($queueName),
            ];
        }

        return [
            'queue' => $queueName,
            'pending' => $this->safeSize($queue, $queueName),
            'delayed' => null,
            'reserved' => null,
            'total' => $this->safeSize($queue, $queueName),
        ];
    }

    private function safeSize(QueueContract $queue, string $queueName): ?int
    {
        try {
            return (int) $queue->size($queueName);
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @param  array{
     *     queue: string,
     *     pending: int|null,
     *     delayed: int|null,
     *     reserved: int|null,
     *     total: int|null
     * }  $stat
     */
    private function formatQueueSummary(array $stat): string
    {
        if ($stat['total'] === null) {
            return "{$stat['queue']}(N/A)";
        }

        if ($stat['delayed'] === null || $stat['reserved'] === null) {
            return "{$stat['queue']}(Total:{$stat['total']})";
        }

        return "{$stat['queue']}(P:{$stat['pending']},D:{$stat['delayed']},R:{$stat['reserved']},T:{$stat['total']})";
    }

    /**
     * @return array<int, array{
     *     key: string,
     *     record_type: 'running',
     *     state: 'pending',
     *     queue: string,
     *     connection: string,
     *     job: string,
     *     attempts: int|null,
     *     time: string,
     *     info: string,
     *     info_full: string,
     *     uuid: string|null
     * }>
     */
    private function extractPendingRows(mixed $redis, string $baseKey, string $queueName, string $connectionName, int $limit): array
    {
        $payloads = $this->safeLRange($redis, $baseKey, $limit);
        $rows = [];

        foreach ($payloads as $index => $payload) {
            $rows[] = $this->makeRunningRow(
                payload: $payload,
                state: 'pending',
                queueName: $queueName,
                connectionName: $connectionName,
                time: null,
                sequence: $index + 1,
            );
        }

        return $rows;
    }

    /**
     * @return array<int, array{
     *     key: string,
     *     record_type: 'running',
     *     state: 'reserved'|'delayed',
     *     queue: string,
     *     connection: string,
     *     job: string,
     *     attempts: int|null,
     *     time: string,
     *     info: string,
     *     info_full: string,
     *     uuid: string|null
     * }>
     */
    private function extractSortedSetRows(
        mixed $redis,
        string $key,
        string $queueName,
        string $connectionName,
        string $state,
        int $limit
    ): array {
        $membersWithScore = $this->safeZRangeWithScore($redis, $key, $limit);
        $rows = [];
        $sequence = 1;

        foreach ($membersWithScore as $member => $score) {
            $rows[] = $this->makeRunningRow(
                payload: (string) $member,
                state: $state,
                queueName: $queueName,
                connectionName: $connectionName,
                time: is_numeric($score) ? (int) $score : null,
                sequence: $sequence,
            );

            $sequence++;
        }

        return $rows;
    }

    /**
     * @return array<int, string>
     */
    private function safeLRange(mixed $redis, string $key, int $limit): array
    {
        if ($limit <= 0) {
            return [];
        }

        try {
            $rows = $redis->lrange($key, 0, $limit - 1);

            if (! is_array($rows)) {
                return [];
            }

            return array_values(array_map(fn (mixed $item): string => (string) $item, $rows));
        } catch (Throwable) {
            return [];
        }
    }

    /**
     * @return array<string, float|int|string>
     */
    private function safeZRangeWithScore(mixed $redis, string $key, int $limit): array
    {
        if ($limit <= 0) {
            return [];
        }

        try {
            $rows = $redis->zrange($key, 0, $limit - 1, ['withscores' => true]);

            if (! is_array($rows)) {
                return [];
            }

            if (! array_is_list($rows)) {
                return $rows;
            }

            $normalized = [];

            foreach ($rows as $member) {
                $member = (string) $member;
                $score = $redis->zscore($key, $member);

                $normalized[$member] = is_numeric($score) ? (float) $score : 0;
            }

            return $normalized;
        } catch (Throwable) {
            return [];
        }
    }

    /**
     * @return array{
     *     key: string,
     *     record_type: 'running',
     *     state: 'pending'|'reserved'|'delayed',
     *     queue: string,
     *     connection: string,
     *     job: string,
     *     attempts: int|null,
     *     time: string,
     *     info: string,
     *     info_full: string,
     *     uuid: string|null
     * }
     */
    private function makeRunningRow(
        string $payload,
        string $state,
        string $queueName,
        string $connectionName,
        ?int $time,
        int $sequence
    ): array {
        $decoded = json_decode($payload, true);

        if (! is_array($decoded)) {
            $decoded = [];
        }

        $jobName = (string) (
            $decoded['displayName']
            ?? data_get($decoded, 'data.commandName')
            ?? $decoded['job']
            ?? 'unknown'
        );

        $jobName = Str::replace('\\', '/', $jobName);
        $attempts = isset($decoded['attempts']) && is_numeric($decoded['attempts'])
            ? (int) $decoded['attempts']
            : null;

        $uuid = (string) ($decoded['uuid'] ?? $decoded['id'] ?? '');
        $createdAt = isset($decoded['createdAt']) && is_numeric($decoded['createdAt'])
            ? (int) $decoded['createdAt']
            : null;

        $displayTime = $this->formatTimestamp($time ?? $createdAt) ?? '-';
        $identifier = $uuid !== '' ? $uuid : sha1($queueName . $state . $payload . $sequence);
        $infoSummary = $uuid !== '' ? "id: {$uuid}" : '-';
        $infoFull = $this->buildRunningInfoDetail(
            state: $state,
            queueName: $queueName,
            connectionName: $connectionName,
            uuid: $uuid,
            displayTime: $displayTime,
            payload: $payload,
        );

        return [
            'key' => "{$queueName}:{$state}:{$identifier}",
            'record_type' => 'running',
            'state' => $state,
            'queue' => $queueName,
            'connection' => $connectionName,
            'job' => $jobName,
            'attempts' => $attempts,
            'time' => $displayTime,
            'info' => $infoSummary,
            'info_full' => $infoFull,
            'uuid' => $uuid !== '' ? $uuid : null,
        ];
    }

    private function formatTimestamp(?int $timestamp): ?string
    {
        if (! $timestamp || $timestamp <= 0) {
            return null;
        }

        try {
            return date('Y-m-d H:i:s', $timestamp);
        } catch (Throwable) {
            return null;
        }
    }

    private function buildRunningInfoDetail(
        string $state,
        string $queueName,
        string $connectionName,
        string $uuid,
        string $displayTime,
        string $payload
    ): string {
        return implode("\n", [
            'State: ' . $state,
            'Queue: ' . $queueName,
            'Connection: ' . $connectionName,
            'ID: ' . ($uuid !== '' ? $uuid : '-'),
            'Time: ' . $displayTime,
            '',
            'Payload:',
            $this->prettyJsonOrRaw($payload),
        ]);
    }

    private function prettyJsonOrRaw(string $value): string
    {
        if ($value === '') {
            return '-';
        }

        $decoded = json_decode($value, true);

        if (! is_array($decoded)) {
            return $value;
        }

        $pretty = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if (! is_string($pretty) || $pretty === '') {
            return $value;
        }

        return $pretty;
    }
}
