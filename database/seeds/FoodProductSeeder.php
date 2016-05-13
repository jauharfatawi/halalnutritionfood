<?php

use Illuminate\Database\Seeder;

class FoodProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('foodProducts')->delete();
    }
}
