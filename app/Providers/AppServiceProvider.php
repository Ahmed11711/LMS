<?php

namespace App\Providers;

use App\Repositories\Lesson\LessonRepositoryInterface;
use App\Repositories\Lesson\LessonRepository;

use App\Repositories\Chapter\ChapterRepositoryInterface;
use App\Repositories\Chapter\ChapterRepository;

use App\Repositories\PhysicalCourseDetail\PhysicalCourseDetailRepositoryInterface;
use App\Repositories\PhysicalCourseDetail\PhysicalCourseDetailRepository;

use App\Repositories\OnlineSession\OnlineSessionRepositoryInterface;
use App\Repositories\OnlineSession\OnlineSessionRepository;

use App\Repositories\Course\CourseRepositoryInterface;
use App\Repositories\Course\CourseRepository;

use App\Repositories\Category\CategoryRepositoryInterface;
use App\Repositories\Category\CategoryRepository;


use App\Observers\TenantObserver;
use Illuminate\Routing\Route;
use App\Repositories\FeaturePackage\FeaturePackageRepository;

use App\Repositories\FeaturePackage\FeaturePackageRepositoryInterface;
use App\Repositories\Features\FeaturesRepository;

use App\Repositories\Features\FeaturesRepositoryInterface;
use App\Repositories\Package\PackageRepository;

use App\Repositories\Package\PackageRepositoryInterface;
use App\Repositories\User\UserRepository;

use App\Repositories\User\UserRepositoryInterface;
use App\Repositories\UserPackage\UserPackageRepository;
use App\Repositories\UserPackage\UserPackageRepositoryInterface;
use Dedoc\Scramble\Scramble;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void {
$this->app->bind(
            UserRepositoryInterface::class,
            UserRepository::class
        );
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(PackageRepositoryInterface::class, PackageRepository::class);
        $this->app->bind(FeaturesRepositoryInterface::class, FeaturesRepository::class);
        $this->app->bind(FeaturePackageRepositoryInterface::class, FeaturePackageRepository::class);
        $this->app->bind(UserPackageRepositoryInterface::class, UserPackageRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(CourseRepositoryInterface::class, CourseRepository::class);
        $this->app->bind(OnlineSessionRepositoryInterface::class, OnlineSessionRepository::class);
        $this->app->bind(PhysicalCourseDetailRepositoryInterface::class, PhysicalCourseDetailRepository::class);
        $this->app->bind(ChapterRepositoryInterface::class, ChapterRepository::class);
        $this->app->bind(LessonRepositoryInterface::class, LessonRepository::class);
}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        TenantObserver::class;
        Model::unguard();
        Scramble::routes(function (Route $route) {
            return str_starts_with($route->uri(), 'api/')
                || str_starts_with($route->uri(), 'admin/');
        });
    }
}
