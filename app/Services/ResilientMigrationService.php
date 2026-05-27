<?php

namespace App\Services;

use Illuminate\Database\Migrations\Migrator;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

/**
 * Runs pending migrations one-by-one; when MySQL reports the change already exists,
 * records the migration as run so production DBs ahead of the migrations table can catch up.
 */
class ResilientMigrationService
{
    public function __construct(
        protected Migrator $migrator
    ) {}

    /**
     * @return array{applied: list<string>, skipped: list<string>, failed: list<array{name: string, error: string}>}
     */
    public function runPending(): array
    {
        $repository = $this->migrator->getRepository();

        if (! $repository->repositoryExists()) {
            $repository->createRepository();
        }

        $files = collect($this->migrator->getMigrationFiles($this->migrator->paths()))->sortKeys();
        $pending = $files->keys()->diff($repository->getRan())->values();

        $results = [
            'applied' => [],
            'skipped' => [],
            'failed' => [],
        ];

        foreach ($pending as $migrationName) {
            $path = $files[$migrationName];

            try {
                $this->migrator->run([$migrationName => $path]);
                $results['applied'][] = $migrationName;
            } catch (QueryException $e) {
                if ($this->isAlreadyAppliedSchemaError($e)) {
                    $this->logMigrationAsRun($migrationName);
                    $results['skipped'][] = $migrationName;
                    Log::info('Resilient migration skipped (schema already present)', [
                        'migration' => $migrationName,
                        'sql_state' => $e->errorInfo[0] ?? null,
                        'driver_code' => $e->errorInfo[1] ?? null,
                    ]);
                } else {
                    $results['failed'][] = [
                        'name' => $migrationName,
                        'error' => $e->getMessage(),
                    ];
                    Log::error('Resilient migration failed', [
                        'migration' => $migrationName,
                        'error' => $e->getMessage(),
                    ]);
                }
            } catch (\Throwable $e) {
                $results['failed'][] = [
                    'name' => $migrationName,
                    'error' => $e->getMessage(),
                ];
                Log::error('Resilient migration failed', [
                    'migration' => $migrationName,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $results;
    }

    protected function isAlreadyAppliedSchemaError(QueryException $e): bool
    {
        $driverCode = (int) ($e->errorInfo[1] ?? 0);

        return in_array($driverCode, [
            1050, // Table already exists
            1060, // Duplicate column name
            1061, // Duplicate key name
            1062, // Duplicate entry (unique)
        ], true);
    }

    protected function logMigrationAsRun(string $migrationName): void
    {
        $repository = $this->migrator->getRepository();

        if ($repository->migrationExists($migrationName)) {
            return;
        }

        $batch = $repository->getNextBatchNumber();
        $repository->log($migrationName, $batch);
    }
}
