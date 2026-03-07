<?php

namespace Keysoft\HelperLibrary\Support;

use Illuminate\Container\Container;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use Keysoft\HelperLibrary\Models\Central\MsTenantCentral;
use RuntimeException;
use Symfony\Component\Finder\SplFileInfo;

class TenantSeederManager
{
    public const DEFAULT_SEEDERS_DIRECTORY = 'database/seeders_tenant';
    public const CUSTOM_SEEDERS_DIRECTORY = 'database/seeder_tenant_custom';
    private const SEEDER_LOG_TABLE = 'tenant_seeders';

    /**
     * @return array<int, array{
     *     file_name: string,
     *     seeder: string,
     *     relative_path: string,
     *     has_database_state: bool,
     *     is_ran: bool,
     *     modified_at: string
     * }>
     */
    public function getFiles(MsTenantCentral $tenant, string $directory = self::DEFAULT_SEEDERS_DIRECTORY): array
    {
        $files = $this->getSeederFilesFromDisk($directory);
        $ranSeeders = $this->getRanSeeders($tenant);

        return array_map(
            fn (array $file): array => [
                ...$file,
                'has_database_state' => true,
                'is_ran' => in_array($file['seeder'], $ranSeeders, true),
            ],
            $files,
        );
    }

    /**
     * @return array<int, array{
     *     file_name: string,
     *     seeder: string,
     *     relative_path: string,
     *     has_database_state: bool,
     *     is_ran: bool,
     *     modified_at: string
     * }>
     */
    public function getFilesWithoutDatabaseState(string $directory = self::DEFAULT_SEEDERS_DIRECTORY): array
    {
        return array_map(
            fn (array $file): array => [
                ...$file,
                'has_database_state' => false,
                'is_ran' => false,
            ],
            $this->getSeederFilesFromDisk($directory),
        );
    }

    public function runAll(MsTenantCentral $tenant, string $directory = self::DEFAULT_SEEDERS_DIRECTORY): string
    {
        $this->guardSeederAllowed($tenant);

        $files = $this->getSeederFilesFromDisk($directory);

        if ($files === []) {
            return 'Tidak ada file seeder untuk dijalankan.';
        }

        return $this->withTenantConnection($tenant, function () use ($files): string {
            $this->ensureSeederLogTableExists();
            $ranSeeders = $this->getRanSeedersFromDatabase();

            $executed = 0;
            $skipped = 0;

            foreach ($files as $file) {
                if (in_array($file['seeder'], $ranSeeders, true)) {
                    $skipped++;
                    continue;
                }

                $this->guardSeederFileIsNotEmpty($file['relative_path']);
                $this->runSeederFileFromPath($file['relative_path']);
                $this->markSeederAsRan($file['seeder'], $file['file_name']);

                $executed++;
                $ranSeeders[] = $file['seeder'];
            }

            return "Seeder selesai dijalankan. Executed: {$executed}, Skipped: {$skipped}.";
        });
    }

    public function runFile(MsTenantCentral $tenant, string $directory, string $fileName): string
    {
        $this->guardSeederAllowed($tenant);

        $file = collect($this->getSeederFilesFromDisk($directory))
            ->firstWhere('file_name', basename($fileName));

        if (! $file) {
            throw new InvalidArgumentException("Seeder file [{$fileName}] tidak ditemukan.");
        }

        return $this->withTenantConnection($tenant, function () use ($file): string {
            $this->ensureSeederLogTableExists();

            $ranSeeders = $this->getRanSeedersFromDatabase();

            if (in_array($file['seeder'], $ranSeeders, true)) {
                return "Seeder [{$file['seeder']}] sudah pernah dijalankan. Dilewati.";
            }

            $this->guardSeederFileIsNotEmpty($file['relative_path']);
            $this->runSeederFileFromPath($file['relative_path']);
            $this->markSeederAsRan($file['seeder'], $file['file_name']);

            return "Seeder [{$file['seeder']}] selesai dijalankan.";
        });
    }

    public function countFiles(string $directory = self::DEFAULT_SEEDERS_DIRECTORY): int
    {
        return count($this->getSeederFilesFromDisk($directory));
    }

    /**
     * @return array<int, string>
     */
    public function getRanSeeders(MsTenantCentral $tenant): array
    {
        return $this->withTenantConnection($tenant, fn (): array => $this->getRanSeedersFromDatabase());
    }

    /**
     * @return array<int, string>
     */
    private function getRanSeedersFromDatabase(): array
    {
        if (! Schema::connection('tenant')->hasTable(self::SEEDER_LOG_TABLE)) {
            return [];
        }

        return DB::connection('tenant')
            ->table(self::SEEDER_LOG_TABLE)
            ->orderBy('seeder')
            ->pluck('seeder')
            ->all();
    }

    /**
     * @return array<int, array{
     *     file_name: string,
     *     seeder: string,
     *     relative_path: string,
     *     modified_at: string
     * }>
     */
    private function getSeederFilesFromDisk(string $directory): array
    {
        $directory = $this->normalizeDirectory($directory);
        $absoluteDirectory = $this->absolutePath($directory);

        if (! File::isDirectory($absoluteDirectory)) {
            return [];
        }

        $files = collect(File::files($absoluteDirectory))
            ->filter(fn (SplFileInfo $file): bool => strtolower($file->getExtension()) === 'php');

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
                    'seeder' => $file->getBasename('.php'),
                    'relative_path' => str_replace('\\', '/', $directory . '/' . $fileName),
                    'modified_at' => date('Y-m-d H:i:s', $file->getMTime()),
                ];
            })
            ->all();
    }

    private function runSeederFileFromPath(string $relativePath): void
    {
        $absolutePath = $this->absolutePath($relativePath);

        if (! File::exists($absolutePath)) {
            throw new InvalidArgumentException("Seeder file [{$relativePath}] tidak ditemukan.");
        }

        $seeder = $this->resolveSeederInstance($absolutePath, $relativePath);

        $container = Container::getInstance();
        $seeder->setContainer($container);

        $container->call([$seeder, 'run']);
    }

    private function resolveSeederInstance(string $absolutePath, string $relativePath): Seeder
    {
        $declaredClassesBefore = get_declared_classes();
        $resolved = require $absolutePath;

        if ($resolved instanceof Seeder) {
            return $resolved;
        }

        if (is_string($resolved) && class_exists($resolved) && is_subclass_of($resolved, Seeder::class)) {
            /** @var Seeder $instance */
            $instance = Container::getInstance()->make($resolved);

            return $instance;
        }

        $declaredClassesAfter = get_declared_classes();
        $newClasses = array_values(array_diff($declaredClassesAfter, $declaredClassesBefore));

        foreach (array_reverse($newClasses) as $className) {
            if (! is_subclass_of($className, Seeder::class)) {
                continue;
            }

            /** @var Seeder $instance */
            $instance = Container::getInstance()->make($className);

            return $instance;
        }

        throw new RuntimeException("Seeder file [{$relativePath}] harus return instance Seeder atau mendefinisikan class Seeder.");
    }

    private function ensureSeederLogTableExists(): void
    {
        if (Schema::connection('tenant')->hasTable(self::SEEDER_LOG_TABLE)) {
            return;
        }

        Schema::connection('tenant')->create(self::SEEDER_LOG_TABLE, function (Blueprint $table): void {
            $table->id();
            $table->string('seeder', 255)->unique();
            $table->string('file_name', 255);
            $table->timestamp('executed_at')->useCurrent();
        });
    }

    private function markSeederAsRan(string $seeder, string $fileName): void
    {
        $exists = DB::connection('tenant')
            ->table(self::SEEDER_LOG_TABLE)
            ->where('seeder', $seeder)
            ->exists();

        if ($exists) {
            return;
        }

        DB::connection('tenant')
            ->table(self::SEEDER_LOG_TABLE)
            ->insert([
                'seeder' => $seeder,
                'file_name' => $fileName,
                'executed_at' => Carbon::now(),
            ]);
    }

    private function withTenantConnection(MsTenantCentral $tenant, callable $callback): mixed
    {
        $this->setTenantConnection($tenant);

        $originalDefault = (string) Config::get('database.default');

        Config::set('database.default', 'tenant');
        DB::setDefaultConnection('tenant');

        try {
            return $callback();
        } finally {
            DB::setDefaultConnection($originalDefault);
            Config::set('database.default', $originalDefault);
        }
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

    private function guardSeederAllowed(MsTenantCentral $tenant): void
    {
        if ($tenant->allowed_migrate) {
            return;
        }

        throw new RuntimeException('Tenant ini belum diizinkan untuk menjalankan seeder.');
    }

    private function normalizeDirectory(string $directory): string
    {
        $directory = trim(str_replace('\\', '/', $directory), '/');

        if ($directory === '') {
            throw new InvalidArgumentException('Directory seeder tidak boleh kosong.');
        }

        return $directory;
    }

    private function guardSeederFileIsNotEmpty(string $relativePath): void
    {
        $absolutePath = $this->absolutePath($relativePath);

        if (! File::exists($absolutePath)) {
            throw new InvalidArgumentException("Seeder file [{$relativePath}] tidak ditemukan.");
        }

        $contents = trim((string) File::get($absolutePath));

        if (in_array($contents, ['', '<?php'], true)) {
            throw new InvalidArgumentException("Seeder file [{$relativePath}] masih kosong.");
        }
    }

    private function absolutePath(string $relativePath): string
    {
        return App::basePath($relativePath);
    }
}
