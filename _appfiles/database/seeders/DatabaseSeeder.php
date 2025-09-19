<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run()
    {
        $this->call(AdActionsSeeder::class);
        $this->call(AdImagesSeeder::class);
        $this->call(AdsSeeder::class);
        $this->call(BlogBookmarksSeeder::class);
        $this->call(BlogCategoriesSeeder::class);
        $this->call(BlogImagesSeeder::class);
        $this->call(BlogsSeeder::class);
        $this->call(BlogTranslationsSeeder::class);
        $this->call(BlogViewsSeeder::class);
        $this->call(CategoriesSeeder::class);
        $this->call(CategoryTranslationsSeeder::class);
        $this->call(CmsContentsSeeder::class);
        $this->call(CmsContentTranslationsSeeder::class);
        $this->call(EPapersSeeder::class);
        $this->call(EPaperTranslationsSeeder::class);
        $this->call(LanguageCodesSeeder::class);
        $this->call(LanguagesSeeder::class);
        $this->call(LiveNewsSeeder::class);
        $this->call(LiveNewsTranslationsSeeder::class);
        $this->call(QuotesSeeder::class);
        $this->call(QuoteTranslationsSeeder::class);
        $this->call(RssFeedsSeeder::class);
        $this->call(SearchLogsSeeder::class);
        $this->call(SettingsSeeder::class);
        $this->call(SocialMediaLinksSeeder::class);
        $this->call(TranslationsSeeder::class);
        $this->call(UserFeedsSeeder::class);
        $this->call(VotesSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(PermissionSeeder::class);
        $this->call(RoleSeeder::class);
        $this->call(RoleHasPermissionSeeder::class);

        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
