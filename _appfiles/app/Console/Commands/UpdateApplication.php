<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Session;
use anlutro\LaravelSettings\Facade as ContentSetting;

class UpdateApplication extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the application code from the folder';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // dd('The handle method is called.');
        $this->info('Checking for new updates...');

        // Check for the current local version
        $localVersion = json_decode(file_get_contents(base_path('public\version.json')), true)['version'];

        // Replace with the actual URL of your version check endpoint
        // $response = file_get_contents('http://example.com/latest-version');
        $latestVersion = json_decode(file_get_contents('https://newincite.technofox.co.in/version.json'), true)['version'];

        if (version_compare($localVersion, $latestVersion, '<')) {
            // Session::put('website_updates', 'Akshita');
            ContentSetting::set('website_updates', true);
            ContentSetting::save();
            $this->info("New update available: {$latestVersion}. Starting update process...");
            // echo json_encode("New update available: {$latestVersion}. Starting update process...");exit;
            // $this->updateApplication();
        } else {
            // echo json_encode("No update available. The current version is {$localVersion}.");exit;
            ContentSetting::set('website_updates', false);
            ContentSetting::save();
            $this->info("No update available. The current version is {$localVersion}.");
        }
    }
}
