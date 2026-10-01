<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PassportSkipAuthorization extends Command
{
    protected $signature = 'passport:skip-authorization {client_id : OAuth client ID to auto-approve}';
    protected $description = 'Enable skip_authorization for a Passport client';

    public function handle()
    {
        $clientId = (int) $this->argument('client_id');

        $updated = DB::table('oauth_clients')
            ->where('id', $clientId)
            ->update(['skip_authorization' => true]);

        if ($updated === 0) {
            $this->error('Client not found or already updated.');
            return 1;
        }

        $this->info("skip_authorization enabled for client ID {$clientId}.");
        return 0;
    }
}
