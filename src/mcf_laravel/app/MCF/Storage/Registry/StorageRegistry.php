<?php
namespace App\MCF\Storage\Registry;

use App\MCF\Storage\Data\StorageRecord;
use App\MCF\Storage\Data\StorageReference;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class StorageRegistry
{
    private const TABLE = 'mcf_storage';

    /*
    |--------------------------------------------------------------------------
    | Table
    |--------------------------------------------------------------------------
    */

    public function tableExists(): bool
    {
        return DB::getSchemaBuilder()->hasTable(
            self::TABLE,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | All
    |--------------------------------------------------------------------------
    */

    public function all(): array
    {
        $this->ensureTableExists();

        return DB::table(self::TABLE)
            ->orderByDesc('id')
            ->get()
            ->map(
                fn(object $row): StorageRecord => $this->toRecord(
                    $row,
                ),
            )
            ->all();
    }

    /*
    |--------------------------------------------------------------------------
    | Find
    |--------------------------------------------------------------------------
    */

    public function find(
        StorageReference | string $reference,
    ): ?StorageRecord {
        $this->ensureTableExists();

        $row = DB::table(self::TABLE)
            ->where(
                'reference',
                (string) $reference,
            )
            ->first();

        if ($row === null) {
            return null;
        }

        return $this->toRecord(
            $row,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Find Many
    |--------------------------------------------------------------------------
    */

    public function findMany(
        array $references,
    ): array {
        $this->ensureTableExists();

        $references = array_values(
            array_unique(
                array_map(
                    fn(StorageReference | string $reference): string =>
                    (string) $reference,
                    $references,
                ),
            ),
        );

        if ($references === []) {
            return [];
        }

        return DB::table(self::TABLE)
            ->whereIn(
                'reference',
                $references,
            )
            ->get()
            ->map(
                fn(object $row): StorageRecord => $this->toRecord(
                    $row,
                ),
            )
            ->all();
    }

    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */

    public function create(
        array $data,
    ): StorageRecord {
        $this->ensureTableExists();

        $now = now();

        $data['created_at'] ??= $now;
        $data['updated_at'] ??= $now;

        $id = DB::table(self::TABLE)
            ->insertGetId(
                $data,
            );

        $row = (object) array_merge(
            $data,
            [
                'id' => $id,
            ],
        );

        return $this->toRecord(
            $row,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(
        StorageReference | string $reference,
        array $data,
    ): ?StorageRecord {
        $this->ensureTableExists();

        $reference = (string) $reference;

        $record = $this->find(
            $reference,
        );

        if ($record === null) {
            return null;
        }

        $data['updated_at'] = now();

        DB::table(self::TABLE)
            ->where(
                'reference',
                $reference,
            )
            ->update(
                $data,
            );

        $row = (object) array_merge(
            $record->toArray(),
            $data,
        );

        return $this->toRecord(
            $row,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Delete
    |--------------------------------------------------------------------------
    */

    public function delete(
        StorageReference | string $reference,
    ): bool {
        $this->ensureTableExists();

        return DB::table(self::TABLE)
            ->where(
                'reference',
                (string) $reference,
            )
            ->delete() > 0;
    }

    /*
    |--------------------------------------------------------------------------
    | Exists
    |--------------------------------------------------------------------------
    */

    public function exists(
        StorageReference | string $reference,
    ): bool {
        $this->ensureTableExists();

        return DB::table(self::TABLE)
            ->where(
                'reference',
                (string) $reference,
            )
            ->exists();
    }

    /*
    |--------------------------------------------------------------------------
    | Count
    |--------------------------------------------------------------------------
    */

    public function count(
        ?string $folder = null,
        ?string $provider = null,
        ?string $storageRoot = null,
    ): int {
        $this->ensureTableExists();

        $query = DB::table(
            self::TABLE,
        );

        if ($folder !== null) {
            $query->where(
                'folder',
                $folder,
            );
        }

        if ($provider !== null) {
            $query->where(
                'provider',
                $provider,
            );
        }

        if ($storageRoot !== null) {
            $query->where(
                'storage_root',
                $storageRoot,
            );
        }

        return (int) $query->count();
    }

    public function deleteMany(
        array $references,
    ): int {
        $this->ensureTableExists();

        $references = array_values(
            array_unique(
                array_map(
                    fn(
                        StorageReference | string $reference,
                    ): string => (string) $reference,
                    $references,
                ),
            ),
        );

        if ($references === []) {
            return 0;
        }

        return DB::table(self::TABLE)
            ->whereIn(
                'reference',
                $references,
            )
            ->delete();
    }

    /*
    |--------------------------------------------------------------------------
    | Internal
    |--------------------------------------------------------------------------
    */

    private function ensureTableExists(): void
    {
        if (! $this->tableExists()) {
            throw new RuntimeException(
                'The MCF Storage table does not exist.',
            );
        }
    }

    private function toRecord(
        object $row,
    ): StorageRecord {
        return new StorageRecord(
            id: (int) $row->id,

            reference: StorageReference::fromString(
                $row->reference,
            ),

            originalName: $row->original_name,

            extension: $row->extension,

            type: $row->type,

            mimeType: $row->mime_type,

            size: (int) $row->size,

            folder: $row->folder,

            provider: $row->provider,

            storageRoot: $row->storage_root,

            access: $row->access,

            createdAt: $row->created_at,

            updatedAt: $row->updated_at,
        );
    }
}
