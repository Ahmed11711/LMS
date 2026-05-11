<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Artisan;

class ClearCrudCommand extends Command
{
    // الأمر: php artisan ahmed:clear Category
    protected $signature = 'ahmed:clear {model}';

    protected $description = 'Delete all generated files and folders for a specific CRUD model';

    public function handle()
    {
        // التعديل الصحيح
        $model = $this->argument('model');
        $snakeModel = Str::snake($model);
        $pluralKebab = Str::kebab(Str::plural($model));

        $this->warn("⚠️  Starting to clear all files for: {$model}...");

        // 1. حذف المجلدات (Folders) لأن الـ Generators عندك بتعمل فولدر لكل موديل
        $directoriesToDelete = [
            app_path("Repositories/{$model}"),
            app_path("Http/Requests/Admin/{$model}"),
            app_path("Http/Resources/Admin/{$model}"),
            app_path("Http/Controllers/Admin/{$model}"),
        ];

        foreach ($directoriesToDelete as $dir) {
            if (File::isDirectory($dir)) {
                File::deleteDirectory($dir);
                $this->info("✔ Deleted Directory: " . basename($dir));
            }
        }

        // 2. حذف الملفات المنفردة
        $filesToDelete = [
            // app_path("Models/{$model}.php"),
            database_path("factories/{$model}Factory.php"),
            database_path("seeders/{$model}Seeder.php"),
            base_path("docs/api/{$snakeModel}.md"),
            storage_path("app/postman/{$snakeModel}_collection.json"),
            resource_path("js/forms/{$model}Fields.js"),
        ];

        foreach ($filesToDelete as $path) {
            if (File::exists($path)) {
                File::delete($path);
                $this->info("✔ Deleted File: " . basename($path));
            }
        }

        // 3. تنظيف الـ AppServiceProvider من الـ Binds
        $this->removeFromAppServiceProvider($model);

        // 4. تنظيف الـ DatabaseSeeder من الـ Call
        $this->removeFromDatabaseSeeder($model);

        // 5. تنظيف الـ Routes من admin.php
        $this->removeFromAdminRoutes($model);

        Artisan::call('optimize');
        $this->info("🚀 CRUD for {$model} has been completely wiped!");
    }

    private function removeFromAppServiceProvider($model)
    {
        $path = app_path('Providers/AppServiceProvider.php');
        if (File::exists($path)) {
            $content = File::get($path);

            // حذف الـ Use statements
            $content = preg_replace("/use App\\\\Repositories\\\\{$model}\\\\{$model}RepositoryInterface;\n/", "", $content);
            $content = preg_replace("/use App\\\\Repositories\\\\{$model}\\\\{$model}Repository;\n/", "", $content);

            // حذف سطر الـ Bind
            $bindLine = "\$this->app->bind({$model}RepositoryInterface::class, {$model}Repository::class);";
            $content = str_replace($bindLine, "", $content);

            File::put($path, $content);
            $this->info("✔ Cleaned AppServiceProvider.");
        }
    }

    private function removeFromDatabaseSeeder($model)
    {
        $path = database_path('seeders/DatabaseSeeder.php');
        if (File::exists($path)) {
            $content = File::get($path);
            $seederCall = "\$this->call({$model}Seeder::class);";
            $content = str_replace($seederCall, "", $content);
            File::put($path, $content);
            $this->info("✔ Cleaned DatabaseSeeder.");
        }
    }

    private function removeFromAdminRoutes($model)
    {
        $path = base_path('routes/admin.php');
        if (File::exists($path)) {
            $content = File::get($path);

            $plural = Str::plural(Str::snake($model));
            $name = Str::snake($model);

            // 1. حذف الـ Use بتاع الـ Controller
            $useLine = "use App\\Http\\Controllers\\Admin\\{$model}\\{$model}Controller;";
            $content = str_replace($useLine, "", $content);

            // 2. حذف سطر الـ Route (بندور عليه بالـ Regex عشان لو فيه مسافات زيادة)
            $routePattern = "/Route::apiResource\('{$plural}', {$model}Controller::class\)->names\('{$name}'\);/";
            $content = preg_replace($routePattern, "", $content);

            File::put($path, $content);
            $this->info("✔ Cleaned admin.php routes.");
        }
    }
}
