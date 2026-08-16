<?php

namespace Database\Seeders;

use App\MCF\Audit\AuditSettings;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        /*
         * Seeders are initialization operations, not user actions.
         *
         * Disable Audit while running the application's seeders
         * to prevent initialization data from being recorded
         * as Audit Log entries.
         */
        AuditSettings::$enabled = false;

        try {
            $this->call([
                MCFTestSeeder::class,
            ]);
        } finally {
            /*
             * Restore Audit after all seeders have completed.
             */
            AuditSettings::$enabled = true;
        }
    }
}
