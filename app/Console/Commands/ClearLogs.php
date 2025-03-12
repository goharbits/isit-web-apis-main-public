<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class ClearLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $logPath = storage_path('logs');
        $files = File::allFiles($logPath);

        foreach ($files as $file) {
            File::delete($file);
        }

        $this->info('Laravel logs cleared successfully.');
        Log::info("Log was cleared");
    }
}
