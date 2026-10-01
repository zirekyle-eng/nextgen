<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdatePrimarySecondaryNextTermFees extends Migration
{
    public function up()
    {
        DB::table('settings')->updateOrInsert(
            ['type' => 'next_term_fees_p'],
            ['description' => '3900']
        );

        DB::table('settings')->updateOrInsert(
            ['type' => 'next_term_fees_j'],
            ['description' => '4400']
        );

        DB::table('settings')->updateOrInsert(
            ['type' => 'next_term_fees_s'],
            ['description' => '4400']
        );
    }

    public function down()
    {
        DB::table('settings')->where('type', 'next_term_fees_p')->update(['description' => '25000']);
        DB::table('settings')->where('type', 'next_term_fees_j')->update(['description' => '20000']);
        DB::table('settings')->where('type', 'next_term_fees_s')->update(['description' => '15600']);
    }
}
