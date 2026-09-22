<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        $userIdColumn = DB::selectOne("SHOW COLUMNS FROM users WHERE Field = 'user_id'");
        $primaryKeyExists = DB::selectOne("SHOW KEYS FROM users WHERE Key_name = 'PRIMARY' AND Column_name = 'user_id'");

        if ($userIdColumn && stripos($userIdColumn->Type, 'int') === 0 && stripos((string) ($userIdColumn->Extra ?? ''), 'auto_increment') === false) {
            if ($primaryKeyExists) {
                DB::statement('ALTER TABLE users DROP PRIMARY KEY');
            }

            DB::statement('ALTER TABLE users MODIFY user_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY');
        }

        if (! $primaryKeyExists && (! $userIdColumn || stripos($userIdColumn->Type, 'int') !== 0 || stripos((string) ($userIdColumn->Extra ?? ''), 'auto_increment') === false)) {
            DB::statement('ALTER TABLE users MODIFY user_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        $primaryKeyExists = DB::selectOne("SHOW KEYS FROM users WHERE Key_name = 'PRIMARY' AND Column_name = 'user_id'");
        $userIdColumn = DB::selectOne("SHOW COLUMNS FROM users WHERE Field = 'user_id'");

        if ($primaryKeyExists) {
            DB::statement('ALTER TABLE users DROP PRIMARY KEY');
        }

        if ($userIdColumn && stripos($userIdColumn->Type, 'int') === 0) {
            DB::statement('ALTER TABLE users MODIFY user_id INT NOT NULL');
        }
    }
};
