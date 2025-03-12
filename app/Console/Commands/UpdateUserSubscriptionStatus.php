<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\SubscriptionExpiredEmail;
class UpdateUserSubscriptionStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check user subscriptions and update status if expired';
    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info('Subscription Cronjob started');
        $this->info('Checking subscriptions...');

        $subscriptions = Subscription::with('user')
                        ->whereIn('status', ['active','cancelled'])
                        ->get();

        foreach ($subscriptions as $subscription) {

            if (Carbon::parse($subscription->end_date)->isPast()) {
                $subscription->update(['status' => 'inactive']);
                $subscription->user->update(['status' => 'Inactive']);
                Mail::to($subscription->user->email)->send(new SubscriptionExpiredEmail($subscription->user));
                $this->info("Subscription ID {$subscription->id} marked as expired.");
            }
        }


        $this->info('Subscription check completed.');
    }
}
