<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class ResetUserPassword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:reset-password {username} {newpassword}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset password user berdasarkan username';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $username = $this->argument('username');
        $newPassword = $this->argument('newpassword');

        // cari user berdasarkan username
        $user = User::where('username', $username)->first();

        if (!$user) {
            $this->error("User dengan username '{$username}' tidak ditemukan!");
            return 1;
        }

        // update password
        $user->password = Hash::make($newPassword);
        $user->save();

        $this->info("Password untuk user '{$username}' berhasil direset.");
        return 0;
    }
}
