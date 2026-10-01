<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddAttendanceSettingKeys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $defaults = [
            'term1_start' => '',
            'term1_end' => '',
            'term2_start' => '',
            'term2_end' => '',
            'term3_start' => '',
            'term3_end' => '',
        ];

        foreach ($defaults as $key => $value) {
            $exists = DB::table('settings')->where('type', $key)->exists();
            if (!$exists) {
                DB::table('settings')->insert([
                    'type' => $key,
                    'description' => $value,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('settings')->whereIn('type', [
            'term1_start',
            'term1_end',
            'term2_start',
            'term2_end',
            'term3_start',
            'term3_end',
        ])->delete();
    }
}
