<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Process\Process;
use anlutro\LaravelSettings\Facade as ContentSetting;
use App\Models\Blog;
use App\Models\Category;
use App\Models\User;
use App\Models\Translation;
use App\Models\Language;
use App\Models\BlogAnalytic;

use Illuminate\Support\Facades\Log;
use ZipArchive;
use Firebase\JWT\JWT;
use Google\Client;
use Google\Service\Drive;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use DB;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['blog'] = Blog::where('status',1)->where('type','post')->count();
        $data['quote'] = Blog::where('status',1)->where('type','quote')->count();
        $data['category'] = Category::where('status',1)->count();
        $data['user'] = User::where('status',1)->where('type','user')->count();
        $data['most_viewed_blogs'] = Blog::getMostViewedBlogs();
        $data['most_selected_categories'] = Category::getMostSelectedCategories();
        $totalGmailUsers = User::where('type','user')->where('login_from','google')->where('status',1)->count();
        $data['total_gmail'] = ($totalGmailUsers > 0) ? ($data['user'] / $totalGmailUsers) * 100 : 0;
        $totalEmailUsers = User::where('type','user')->where('login_from','email')->where('status',1)->count();
        $data['total_email'] = ($totalEmailUsers > 0) ? ($data['user'] / $totalEmailUsers) * 100 : 0;
        // echo json_encode($data['most_viewed_blogs']);exit;

        
        return view('admin.dashboard.index',$data);
    }

    public function updateWebsite()
    {
        // Put the application into maintenance mode
        // Artisan::call('down');
        // Artisan::call('up');
        // Add your database backup logic here
        $newColumns = [
            'cms_contents' => [
                ['name' => 'meta_keywords', 'type' => 'string', 'nullable' => true],
            ],
        ];
        // Download the update
        $this->info('Downloading the update...');
        $zipFile = 'update.zip'; // The name of your zip file
        $updateUrl = 'https://newincite.technofox.co.in/' . $zipFile; // The URL where the update is hosted

        $process = new Process(['curl', '-o', $zipFile, $updateUrl]);
        $process->mustRun();
        $zip = new ZipArchive;

        // Attempt to open the zip file
        if ($zip->open($zipFile) === TRUE) {
            // Extract it to the path we determined above
            $zip->extractTo(base_path());
            $zip->close();
            Log::info('Update extracted.');
            // foreach ($newColumns as $table => $columns) {
            //     Schema::table($table, function (Blueprint $tableBlueprint) use ($columns) {
            //         foreach ($columns as $column) {
            //             if (!Schema::hasColumn($table, $column['name'])) {
            //                 $type = $column['type'];
            //                 $columnBlueprint = $tableBlueprint->$type($column['name']);
                            
            //                 if (isset($column['nullable']) && $column['nullable']) {
            //                     $columnBlueprint->nullable();
            //                 }
            //                 if (isset($column['default'])) {
            //                     $columnBlueprint->default($column['default']);
            //                 }
            //             }
            //         }
            //     });
            // }
            // Perform any post-update logic you might have

        } else {
            // Log error if the zip file can't be opened
            Log::error('Failed to open update.zip');
        }

        // Bring the application out of maintenance mode
        Artisan::call('up');
        ContentSetting::set('website_updates', false);
        ContentSetting::save();
        return 'The application has been updated to latestVersion';
    }

    private function info($string)
    {
        // If you want to log this information, you can use Laravel's logging facilities
        \Log::info($string);

        // If you want to send output back to the browser, you could collect the messages in an array and return them
        // Or, if running a job synchronously, you could use echo or print to send the output to the screen
    }
}
