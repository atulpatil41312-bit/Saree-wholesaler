<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Attach product lookup foreign keys after the referenced tables exist.
     *
     * The original products migration predates the saree_types and fabrics
     * migrations. Keeping these constraints here makes a fresh PostgreSQL
     * deployment deterministic while the checks keep existing SQLite installs
     * from receiving duplicate constraints.
     */
    public function up(): void
    {
        $foreignKeys = [];

        if (Schema::hasTable('products') && Schema::hasTable('saree_types') && ! $this->hasForeignKey('products', 'saree_type_id', 'saree_types')) {
            $foreignKeys[] = ['column' => 'saree_type_id', 'table' => 'saree_types'];
        }

        if (Schema::hasTable('products') && Schema::hasTable('fabrics') && ! $this->hasForeignKey('products', 'fabric_id', 'fabrics')) {
            $foreignKeys[] = ['column' => 'fabric_id', 'table' => 'fabrics'];
        }

        if ($foreignKeys === []) {
            return;
        }

        Schema::table('products', function (Blueprint $table) use ($foreignKeys): void {
            foreach ($foreignKeys as $foreignKey) {
                $table->foreign($foreignKey['column'])
                    ->references('id')
                    ->on($foreignKey['table'])
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Remove only the constraints this migration owns.
     */
    public function down(): void
    {
        $foreignKeys = [];

        if ($this->hasForeignKey('products', 'saree_type_id', 'saree_types')) {
            $foreignKeys[] = 'saree_type_id';
        }

        if ($this->hasForeignKey('products', 'fabric_id', 'fabrics')) {
            $foreignKeys[] = 'fabric_id';
        }

        if ($foreignKeys === []) {
            return;
        }

        Schema::table('products', function (Blueprint $table) use ($foreignKeys): void {
            foreach ($foreignKeys as $column) {
                $table->dropForeign([$column]);
            }
        });
    }

    private function hasForeignKey(string $table, string $column, string $referencedTable): bool
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        if ($driver === 'sqlite') {
            return collect(DB::select("PRAGMA foreign_key_list('{$table}')"))
                ->contains(fn (object $foreignKey): bool => ($foreignKey->from ?? null) === $column && ($foreignKey->table ?? null) === $referencedTable);
        }

        if ($driver === 'pgsql') {
            return DB::selectOne(
                'select 1 from pg_constraint c join pg_class t on t.oid = c.conrelid join pg_attribute a on a.attrelid = t.oid and a.attnum = any(c.conkey) join pg_class r on r.oid = c.confrelid where t.relname = ? and a.attname = ? and r.relname = ?',
                [$table, $column, $referencedTable],
            ) !== null;
        }

        if ($driver === 'mysql') {
            return DB::table('information_schema.KEY_COLUMN_USAGE')
                ->where('TABLE_SCHEMA', $connection->getDatabaseName())
                ->where('TABLE_NAME', $table)
                ->where('COLUMN_NAME', $column)
                ->where('REFERENCED_TABLE_NAME', $referencedTable)
                ->exists();
        }

        return false;
    }
};
