<?php

namespace App\Console\Commands;

use App\Models\RefreshToken;
use Illuminate\Console\Command;

class CleanupExpiredRefreshTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tokens:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove expired and revoked refresh tokens from the database';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Cleaning up expired and revoked refresh tokens...');

        $deleted = RefreshToken::where(function ($query) {
            $query->where('expires_at', '<', now())
                ->orWhere('is_revoked', true);
        })->delete();

        $this->info("Deleted {$deleted} expired/revoked refresh tokens.");

        return Command::SUCCESS;
    }
}
