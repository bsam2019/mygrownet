<?php

namespace App\Domain\Core\Traits;

use App\Domain\Core\Exceptions\ConcurrencyException;
use Illuminate\Support\Facades\DB;

trait OptimisticLocking
{
    protected int $expectedVersion = 0;

    public function setExpectedVersion(int $version): static
    {
        $this->expectedVersion = $version;
        return $this;
    }

    public function getExpectedVersion(): int
    {
        return $this->expectedVersion;
    }

    /**
     * Execute an update with optimistic locking on a given table.
     *
     * @param string $table  Database table name
     * @param int    $id     Primary key value
     * @param array  $data   Key-value pairs to update (excluding version)
     * @param string $idColumn  Primary key column name
     * @param string $versionColumn  Version column name
     * @throws ConcurrencyException
     */
    protected function optimisticUpdate(
        string $table,
        int $id,
        array $data,
        string $idColumn = 'id',
        string $versionColumn = 'version',
    ): void {
        $data[$versionColumn] = DB::raw("{$versionColumn} + 1");

        $affected = DB::table($table)
            ->where($idColumn, $id)
            ->where($versionColumn, $this->expectedVersion)
            ->update($data);

        if ($affected === 0) {
            $currentVersion = DB::table($table)
                ->where($idColumn, $id)
                ->value($versionColumn);

            throw new ConcurrencyException(
                "Optimistic lock failed for {$table}#{$id}: expected version {$this->expectedVersion}, current version " . ($currentVersion ?? 'deleted')
            );
        }

        $this->expectedVersion = $this->expectedVersion + 1;
    }
}
