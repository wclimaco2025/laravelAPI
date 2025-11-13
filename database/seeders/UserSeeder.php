<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Generate 50 users with distributed created_at dates
        // Distribute across different days, weeks, and months for statistics testing

        $now = Carbon::now();

        // Create users distributed over the last 90 days
        for ($i = 0; $i < 50; $i++) {
            // Distribute users across different time periods:
            // - Some in the last week (days)
            // - Some in the last month (weeks)
            // - Some in the last 3 months (months)

            $daysAgo = rand(0, 90);
            $createdAt = $now->copy()->subDays($daysAgo);

            User::factory()->create([
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }
    }
}
