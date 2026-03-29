<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use App\Services\command\ApiRouteService;
use App\Services\command\RequestGenerator;
use App\Services\command\ResourceGenerator;
use App\Services\command\ProviderBindService;
use App\Services\command\RepositoryGenerator;
use App\Services\command\ControllerGeneratorDRY;
use Illuminate\Support\Facades\Log;

class CrudGeneratorCommand extends Command
{
        protected $signature = 'ahmed {model} ';

        protected $description = 'Generate CRUD (Controller, Requests, Resource, Repository) inside an HMVC Module';

        public function handle()
        {
                // $module = $this->argument('module');
                $model = $this->argument('model');
                Log::info("Starting CRUD generation for model: {$model}");
                // $seeder = $this->argument('seed') ?? 'True';



                RepositoryGenerator::generate($model);
                // Generate Request Validation
                RequestGenerator::make($model);
                ResourceGenerator::make($model);
                // Generate Api Resource
                ApiRouteService::make($model);
                // Generate Bind Repository
                ProviderBindService::make($model);


                ControllerGeneratorDRY::make($model);

                $this->info("CRUD generated for {$model} inside{");

                Artisan::call('optimize');
                $this->info("Artisan optimize executed successfully.");
                $this->info('Artisan optimize executed successfully.');

                // Sync Info
                // InfoSyncService::make($module, $model);
                // RelationSyncService::make($module, $model);

                // $this->info("CRUD generated for {$model} ");

                Artisan::call('optimize');
                $this->info("Artisan optimize executed successfully.");
        }
}
