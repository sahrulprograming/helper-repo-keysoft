<?php

namespace Keysoft\HelperLibrary\Support;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use Keysoft\HelperLibrary\Models\MsTenantCentral;
use RuntimeException;
use Symfony\Component\Finder\SplFileInfo;

class TenantMigrationManager
{
    public const DEFAULT_MIGRATIONS_DIRECTORY = 'database/migrations_tenant';
    public const CUSTOM_MIGRATIONS_DIRECTORY = 'database/migration_tenant_custom';

    /**
     * @return array<int, array{
     *     file_name: string,
     *     migration: string,
     *     relative_path: string,
     *     has_database_state: bool,
     *     is_ran: bool,
     *     modified_at: string
     * }>
     */
    public function getFiles(MsTenantCentral $tenant, string $directory = self::DEFAULT_MIGRATIONS_DIRECTORY): array
    {
        $files = $this->getMigrationFilesFromDisk($directory);
        $ranMigrations = $this->getRanMigrations($tenant);

        return array_map(
            fn (array $file): array => [
                ...$file,
                'has_database_state' => true,
                'is_ran' => in_array($file['migration'], $ranMigrations, true),
            ],
            $files,
        );
    }

    /**
     * @return array<int, array{
     *     file_name: string,
     *     migration: string,
     *     relative_path: string,
     *     has_database_state: bool,
     *     is_ran: bool,
     *     modified_at: string
     * }>
     */
    public function getFilesWithoutDatabaseState(string $directory = self::DEFAULT_MIGRATIONS_DIRECTORY): array
    {
        return array_map(
            fn (array $file): array => [
                ...$file,
                'has_database_state' => false,
                'is_ran' => false,
            ],
            $this->getMigrationFilesFromDisk($directory),
        );
    }

    public function runAll(MsTenantCentral $tenant, string $directory = self::DEFAULT_MIGRATIONS_DIRECTORY): string
    {
        $this->guardMigrationAllowed($tenant);

        return $this->runMigrateCommand($tenant, [$this->normalizeDirectory($directory)]);
    }

    public function refreshAll(MsTenantCentral $tenant, string $directory = self::DEFAULT_MIGRATIONS_DIRECTORY): string
    {
        $this->guardMigrationAllowed($tenant);

        return $this->runRefreshCommand($tenant, [$this->normalizeDirectory($directory)]);
    }

    public function runFile(MsTenantCentral $tenant, string $directory, string $fileName): string
    {
        $this->guardMigrationAllowed($tenant);

        $file = collect($this->getMigrationFilesFromDisk($directory))
            ->firstWhere('file_name', basename($fileName));

        if (! $file) {
            throw new InvalidArgumentException("Migration file [{$fileName}] tidak ditemukan.");
        }

        $this->guardMigrationFileIsNotEmpty($file['relative_path']);

        return $this->runMigrateCommand($tenant, [$file['relative_path']]);
    }

    public function refreshFile(MsTenantCentral $tenant, string $directory, string $fileName): string
    {
        $this->guardMigrationAllowed($tenant);

        $plan = $this->buildRefreshPlan($tenant, $directory, $fileName);

        if ($plan['strategy'] === 'native') {
            return $this->runRefreshCommand($tenant, [$plan['selected']['relative_path']]);
        }

        return $this->executeDependencyRefreshPlan($tenant, $plan);
    }

    /**
     * @return array{
     *     strategy: 'native'|'dependency',
     *     selected: array{
     *         file_name: string,
     *         migration: string,
     *         relative_path: string,
     *         tables: array<int, string>
     *     },
     *     down_tables: array<int, string>,
     *     up_tables: array<int, string>,
     *     down_migrations: array<int, array{
     *         file_name: string,
     *         migration: string,
     *         relative_path: string,
     *         tables: array<int, string>
     *     }>,
     *     up_migrations: array<int, array{
     *         file_name: string,
     *         migration: string,
     *         relative_path: string,
     *         tables: array<int, string>
     *     }>
     * }
     */
    public function previewRefreshFile(MsTenantCentral $tenant, string $directory, string $fileName): array
    {
        return $this->buildRefreshPlan($tenant, $directory, $fileName);
    }

    public function countFiles(string $directory = self::DEFAULT_MIGRATIONS_DIRECTORY): int
    {
        return count($this->getMigrationFilesFromDisk($directory));
    }

    /**
     * @return array<int, string>
     */
    public function getRanMigrations(MsTenantCentral $tenant): array
    {
        $this->setTenantConnection($tenant);

        $migrationsTable = Config::get('database.migrations.table', 'migrations');

        if (! Schema::connection('tenant')->hasTable($migrationsTable)) {
            return [];
        }

        return DB::connection('tenant')
            ->table($migrationsTable)
            ->orderBy('migration')
            ->pluck('migration')
            ->all();
    }

    /**
     * @return array<int, array{
     *     file_name: string,
     *     migration: string,
     *     relative_path: string,
     *     modified_at: string
     * }>
     */
    private function getMigrationFilesFromDisk(string $directory): array
    {
        $directory = $this->normalizeDirectory($directory);
        $absoluteDirectory = $this->absolutePath($directory);

        if (! File::isDirectory($absoluteDirectory)) {
            return [];
        }

        $files = collect(File::files($absoluteDirectory));

        if ($files->isEmpty()) {
            return [];
        }

        return $files
            ->sortBy(fn (SplFileInfo $file): string => $file->getFilename())
            ->values()
            ->map(function (SplFileInfo $file) use ($directory): array {
                $fileName = $file->getFilename();

                return [
                    'file_name' => $fileName,
                    'migration' => $file->getBasename('.php'),
                    'relative_path' => str_replace('\\', '/', $directory . '/' . $fileName),
                    'modified_at' => date('Y-m-d H:i:s', $file->getMTime()),
                ];
            })
            ->all();
    }

    /**
     * @param  array<int, string>  $paths
     */
    private function runMigrateCommand(MsTenantCentral $tenant, array $paths): string
    {
        $this->setTenantConnection($tenant);

        Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path' => $paths,
            '--force' => true,
        ]);

        return trim(Artisan::output());
    }

    /**
     * @param  array<int, string>  $paths
     */
    private function runRefreshCommand(MsTenantCentral $tenant, array $paths): string
    {
        $this->setTenantConnection($tenant);

        Artisan::call('migrate:refresh', [
            '--database' => 'tenant',
            '--path' => $paths,
            '--force' => true,
        ]);

        return trim(Artisan::output());
    }

    /**
     * @return array{
     *     strategy: 'native'|'dependency',
     *     selected: array{
     *         file_name: string,
     *         migration: string,
     *         relative_path: string,
     *         tables: array<int, string>
     *     },
     *     down_tables: array<int, string>,
     *     up_tables: array<int, string>,
     *     down_migrations: array<int, array{
     *         file_name: string,
     *         migration: string,
     *         relative_path: string,
     *         tables: array<int, string>
     *     }>,
     *     up_migrations: array<int, array{
     *         file_name: string,
     *         migration: string,
     *         relative_path: string,
     *         tables: array<int, string>
     *     }>
     * }
     */
    private function buildRefreshPlan(MsTenantCentral $tenant, string $directory, string $fileName): array
    {
        $normalizedDirectory = $this->normalizeDirectory($directory);
        $files = collect($this->getMigrationFilesFromDisk($normalizedDirectory))
            ->map(function (array $file): array {
                $file['tables'] = $this->extractCreatedTables($file['relative_path']);

                return $file;
            })
            ->values();

        $selected = $files->firstWhere('file_name', basename($fileName));

        if (! $selected) {
            throw new InvalidArgumentException("Migration file [{$fileName}] tidak ditemukan.");
        }

        $this->guardMigrationFileIsNotEmpty($selected['relative_path']);

        /** @var array<int, string> $selectedTables */
        $selectedTables = $selected['tables'];

        if ($selectedTables === []) {
            return [
                'strategy' => 'native',
                'selected' => $selected,
                'down_tables' => [],
                'up_tables' => [],
                'down_migrations' => [],
                'up_migrations' => [],
            ];
        }

        $tableToMigration = [];

        foreach ($files as $file) {
            foreach ($file['tables'] as $table) {
                if (! isset($tableToMigration[$table])) {
                    $tableToMigration[$table] = $file;
                }
            }
        }

        $this->setTenantConnection($tenant);
        $parentToChildren = $this->getParentToChildrenMap();

        $allDependentTables = $this->collectDependentTables($selectedTables, $parentToChildren);
        $dependentTables = array_values(array_diff($allDependentTables, $selectedTables));

        if ($dependentTables === []) {
            return [
                'strategy' => 'native',
                'selected' => $selected,
                'down_tables' => [],
                'up_tables' => [],
                'down_migrations' => [],
                'up_migrations' => [],
            ];
        }

        $unknownDependentTables = array_values(array_diff($dependentTables, array_keys($tableToMigration)));

        if ($unknownDependentTables !== []) {
            throw new RuntimeException(
                'Tidak bisa auto-refresh karena migration untuk tabel dependent tidak ditemukan dalam folder '
                . $normalizedDirectory
                . ': '
                . implode(', ', $unknownDependentTables),
            );
        }

        $downTables = $this->buildRollbackTableOrder($selectedTables, $parentToChildren, array_keys($tableToMigration));

        $downMigrations = [];

        foreach ($downTables as $table) {
            if (! isset($tableToMigration[$table])) {
                continue;
            }

            $migration = $tableToMigration[$table];
            $migrationKey = $migration['migration'];

            if (! isset($downMigrations[$migrationKey])) {
                $migration['tables'] = [];
                $downMigrations[$migrationKey] = $migration;
            }

            $downMigrations[$migrationKey]['tables'][] = $table;
        }

        $downMigrations = array_values($downMigrations);
        $upMigrations = array_reverse($downMigrations);
        $upTables = array_reverse($downTables);

        return [
            'strategy' => 'dependency',
            'selected' => $selected,
            'down_tables' => $downTables,
            'up_tables' => $upTables,
            'down_migrations' => $downMigrations,
            'up_migrations' => $upMigrations,
        ];
    }

    /**
     * @param  array{
     *     strategy: 'native'|'dependency',
     *     selected: array{
     *         file_name: string,
     *         migration: string,
     *         relative_path: string,
     *         tables: array<int, string>
     *     },
     *     down_tables: array<int, string>,
     *     up_tables: array<int, string>,
     *     down_migrations: array<int, array{
     *         file_name: string,
     *         migration: string,
     *         relative_path: string,
     *         tables: array<int, string>
     *     }>,
     *     up_migrations: array<int, array{
     *         file_name: string,
     *         migration: string,
     *         relative_path: string,
     *         tables: array<int, string>
     *     }>
     * }  $plan
     */
    private function executeDependencyRefreshPlan(MsTenantCentral $tenant, array $plan): string
    {
        $this->setTenantConnection($tenant);

        foreach ($plan['down_tables'] as $table) {
            if (! Schema::connection('tenant')->hasTable($table)) {
                continue;
            }

            Schema::connection('tenant')->drop($table);
        }

        $migrationsTable = Config::get('database.migrations.table', 'migrations');

        if (Schema::connection('tenant')->hasTable($migrationsTable)) {
            $migrationNames = array_values(array_map(
                fn (array $migration): string => $migration['migration'],
                $plan['down_migrations'],
            ));

            if ($migrationNames !== []) {
                DB::connection('tenant')
                    ->table($migrationsTable)
                    ->whereIn('migration', $migrationNames)
                    ->delete();
            }
        }

        $upPaths = array_values(array_map(
            fn (array $migration): string => $migration['relative_path'],
            $plan['up_migrations'],
        ));

        foreach ($upPaths as $upPath) {
            $this->guardMigrationFileIsNotEmpty($upPath);
        }

        if ($upPaths === []) {
            return 'Tidak ada migration dependent yang perlu di-refresh.';
        }

        return $this->runMigrateCommand($tenant, $upPaths);
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function getParentToChildrenMap(): array
    {
        $rows = DB::connection('tenant')->select(
            <<<'SQL'
                SELECT
                    tc.table_name AS child_table,
                    ccu.table_name AS parent_table
                FROM information_schema.table_constraints AS tc
                INNER JOIN information_schema.constraint_column_usage AS ccu
                    ON ccu.constraint_name = tc.constraint_name
                    AND ccu.table_schema = tc.table_schema
                WHERE tc.constraint_type = 'FOREIGN KEY'
                    AND tc.table_schema = current_schema()
            SQL,
        );

        $map = [];

        foreach ($rows as $row) {
            $parent = (string) ($row->parent_table ?? '');
            $child = (string) ($row->child_table ?? '');

            if ($parent === '' || $child === '') {
                continue;
            }

            if (! isset($map[$parent])) {
                $map[$parent] = [];
            }

            if (! in_array($child, $map[$parent], true)) {
                $map[$parent][] = $child;
            }
        }

        foreach ($map as &$children) {
            sort($children);
        }

        unset($children);

        return $map;
    }

    /**
     * @param  array<int, string>  $rootTables
     * @param  array<string, array<int, string>>  $parentToChildren
     * @param  array<int, string>  $knownTables
     * @return array<int, string>
     */
    private function buildRollbackTableOrder(array $rootTables, array $parentToChildren, array $knownTables): array
    {
        $knownTableLookup = array_fill_keys($knownTables, true);
        $visited = [];
        $ordered = [];

        $walk = function (string $table) use (&$walk, &$visited, &$ordered, $parentToChildren, $knownTableLookup): void {
            if (isset($visited[$table])) {
                return;
            }

            $visited[$table] = true;

            foreach ($parentToChildren[$table] ?? [] as $child) {
                if (! isset($knownTableLookup[$child])) {
                    continue;
                }

                $walk($child);
            }

            if (isset($knownTableLookup[$table])) {
                $ordered[] = $table;
            }
        };

        foreach ($rootTables as $rootTable) {
            $walk($rootTable);
        }

        return array_values(array_unique($ordered));
    }

    /**
     * @param  array<int, string>  $rootTables
     * @param  array<string, array<int, string>>  $parentToChildren
     * @return array<int, string>
     */
    private function collectDependentTables(array $rootTables, array $parentToChildren): array
    {
        $visited = [];
        $ordered = [];

        $walk = function (string $table) use (&$walk, &$visited, &$ordered, $parentToChildren): void {
            if (isset($visited[$table])) {
                return;
            }

            $visited[$table] = true;
            $ordered[] = $table;

            foreach ($parentToChildren[$table] ?? [] as $child) {
                $walk($child);
            }
        };

        foreach ($rootTables as $rootTable) {
            $walk($rootTable);
        }

        return array_values(array_unique($ordered));
    }

    /**
     * @return array<int, string>
     */
    private function extractCreatedTables(string $relativePath): array
    {
        $absolutePath = $this->absolutePath($relativePath);

        if (! File::exists($absolutePath)) {
            return [];
        }

        $contents = (string) File::get($absolutePath);

        if ($contents === '') {
            return [];
        }

        preg_match_all('/Schema::create\(\s*[\'"]([^\'"]+)[\'"]\s*,/i', $contents, $matches);

        if (! isset($matches[1]) || ! is_array($matches[1])) {
            return [];
        }

        return array_values(array_unique(array_map(
            fn (string $table): string => trim($table),
            $matches[1],
        )));
    }

    private function setTenantConnection(MsTenantCentral $tenant): void
    {
        Config::set('database.connections.tenant', [
            'driver' => 'pgsql',
            'host' => $tenant->db_host,
            'port' => (int) $tenant->db_port,
            'database' => $tenant->db_name,
            'username' => $tenant->db_user,
            'password' => $tenant->db_password,
            'charset' => 'utf8',
            'prefix' => '',
            'search_path' => 'public',
            'sslmode' => Config::get('database.connections.tenant.sslmode', 'prefer'),
        ]);

        DB::disconnect('tenant');
        DB::purge('tenant');
        DB::reconnect('tenant');
    }

    private function guardMigrationAllowed(MsTenantCentral $tenant): void
    {
        if ($tenant->allowed_migrate) {
            return;
        }

        throw new RuntimeException('Tenant ini belum diizinkan untuk menjalankan migration.');
    }

    private function normalizeDirectory(string $directory): string
    {
        $directory = trim(str_replace('\\', '/', $directory), '/');

        if ($directory === '') {
            throw new InvalidArgumentException('Directory migration tidak boleh kosong.');
        }

        return $directory;
    }

    private function guardMigrationFileIsNotEmpty(string $relativePath): void
    {
        $absolutePath = $this->absolutePath($relativePath);

        if (! File::exists($absolutePath)) {
            throw new InvalidArgumentException("Migration file [{$relativePath}] tidak ditemukan.");
        }

        $contents = trim((string) File::get($absolutePath));

        if (in_array($contents, ['', '<?php'], true)) {
            throw new InvalidArgumentException("Migration file [{$relativePath}] masih kosong.");
        }
    }

    private function absolutePath(string $relativePath): string
    {
        return App::basePath($relativePath);
    }
}
