<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSkipAuthorizationToOauthClientsTable extends Migration
{
    public function up()
    {
        Schema::table('oauth_clients', function (Blueprint $table) {
            if (!Schema::hasColumn('oauth_clients', 'skip_authorization')) {
                $table->boolean('skip_authorization')->default(false)->after('revoked');
            }
        });
    }

    public function down()
    {
        Schema::table('oauth_clients', function (Blueprint $table) {
            if (Schema::hasColumn('oauth_clients', 'skip_authorization')) {
                $table->dropColumn('skip_authorization');
            }
        });
    }
}
