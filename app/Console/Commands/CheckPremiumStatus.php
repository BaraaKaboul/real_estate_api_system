<?php

namespace App\Console\Commands;

use App\Models\Premium;
use Illuminate\Console\Command;

class CheckPremiumStatus extends Command
{
    protected $signature = 'premium:check';
    protected $description = 'Deactivate premium users when subscription ends';


    public function handle()
    {
        $now = now();

        // نجيب كل المستخدمين اللي انتهت اشتراكاتهم
        $expired = Premium::where('end_date', '<', $now)
            ->where('status', 'accepted')
            ->get();

        foreach ($expired as $premium) {
            $user = $premium->user;

            if ($user->is_verified_agent) {
                $user->is_verified_agent = false;
                $user->save();

                $this->info("Premium removed for user ID: {$user->id}");
            }
        }

        return Command::SUCCESS;
    }
}
