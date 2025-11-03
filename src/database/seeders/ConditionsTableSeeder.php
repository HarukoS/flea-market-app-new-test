<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Condition;

class ConditionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Condition::insert([
            ['id' => 1, 'condition_name' => '良好'],
            ['id' => 2, 'condition_name' => '目立った傷や汚れなし'],
            ['id' => 3, 'condition_name' => 'やや傷や汚れあり'],
            ['id' => 4, 'condition_name' => '状態が悪い'],
        ]);
    }
}
