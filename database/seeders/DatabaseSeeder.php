<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Interaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     * @throws \Exception
     */
    public function run()
    {
        User::factory()->create(['name' => 'Jonathan Reinink', 'email' => 'jonathan@reinink.ca']);
        User::factory()->create(['name' => 'Taylor Otwell', 'email' => 'taylor@laravel.com']);
        User::factory()->create(['name' => 'Ian Landsman', 'email' => 'ian@userscape.com', 'is_admin' => true]);

        Customer::factory(1000)->create()->each(function ($customer) {
            $customer->update([
                'company_id' => Company::factory()->create()->id,
                'sales_rep_id' => random_int(1, 2),
            ]);

            $customer->interactions()->saveMany(Interaction::factory(500)->make());
        });

    }
}
