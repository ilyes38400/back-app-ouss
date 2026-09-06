<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    /**
     * Beaucoup d'athlètes se fixent des objectifs hors sport : scolaire,
     * professionnel, familial, développement personnel. Le type "autre" leur
     * ouvre la porte sans multiplier les catégories.
     */
    public function up(): void
    {
        DB::statement(
            "ALTER TABLE user_objectives MODIFY COLUMN type
             ENUM('technique','tactique','physique','mental','autre') NOT NULL"
        );
    }

    public function down(): void
    {
        DB::table('user_objectives')->where('type', 'autre')->update(['type' => 'mental']);

        DB::statement(
            "ALTER TABLE user_objectives MODIFY COLUMN type
             ENUM('technique','tactique','physique','mental') NOT NULL"
        );
    }
};
