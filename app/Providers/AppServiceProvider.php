<?php

namespace App\Providers;

use App\Repositories\Subject\SubjectRepositoryInterface;
use App\Repositories\Subject\SubjectRepository;

use App\Repositories\Term\TermRepositoryInterface;
use App\Repositories\Term\TermRepository;

use App\Repositories\Grade\GradeRepositoryInterface;
use App\Repositories\Grade\GradeRepository;

use App\Repositories\AcademicYear\AcademicYearRepositoryInterface;
use App\Repositories\AcademicYear\AcademicYearRepository;

use App\Repositories\BagPurchase\BagPurchaseRepositoryInterface;
use App\Repositories\BagPurchase\BagPurchaseRepository;

use App\Repositories\Bag\BagRepositoryInterface;
use App\Repositories\Bag\BagRepository;

use App\Repositories\LandingPage\LandingPageRepositoryInterface;
use App\Repositories\LandingPage\LandingPageRepository;


use App\Repositories\Section\SectionRepositoryInterface;
use App\Repositories\Section\SectionRepository;

use App\Repositories\Pages\PagesRepositoryInterface;
use App\Repositories\Pages\PagesRepository;



use App\Repositories\InstructorReceiverAccount\InstructorReceiverAccountRepositoryInterface;
use App\Repositories\InstructorReceiverAccount\InstructorReceiverAccountRepository;

use App\Repositories\ReceiverAccount\ReceiverAccountRepositoryInterface;
use App\Repositories\ReceiverAccount\ReceiverAccountRepository;

use App\Repositories\AcademyWithdraw\AcademyWithdrawRepositoryInterface;
use App\Repositories\AcademyWithdraw\AcademyWithdrawRepository;

use App\Repositories\AcademyPaymentMethod\AcademyPaymentMethodRepositoryInterface;
use App\Repositories\AcademyPaymentMethod\AcademyPaymentMethodRepository;

use App\Repositories\Plan\PlanRepositoryInterface;
use App\Repositories\Plan\PlanRepository;

use App\Repositories\PaymentMethod\PaymentMethodRepositoryInterface;
use App\Repositories\PaymentMethod\PaymentMethodRepository;



use App\Repositories\Country\CountryRepositoryInterface;
use App\Repositories\Country\CountryRepository;

use App\Repositories\UserWithdraw\UserWithdrawRepositoryInterface;
use App\Repositories\UserWithdraw\UserWithdrawRepository;

use App\Repositories\UserPaymentInfo\UserPaymentInfoRepositoryInterface;
use App\Repositories\UserPaymentInfo\UserPaymentInfoRepository;

use App\Repositories\paymentInfo\paymentInfoRepositoryInterface;
use App\Repositories\paymentInfo\paymentInfoRepository;

use App\Repositories\UserSubscribe\UserSubscribeRepositoryInterface;
use App\Repositories\UserSubscribe\UserSubscribeRepository;

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
use App\Repositories\UserPlan\UserPlanRepository;
use App\Repositories\UserPlan\UserPlanRepositoryInterface;
use Dedoc\Scramble\Scramble;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Illuminate\Http\JsonResponse;

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
        $this->app->bind(UserSubscribeRepositoryInterface::class, UserSubscribeRepository::class);
        $this->app->bind(paymentInfoRepositoryInterface::class, paymentInfoRepository::class);
        $this->app->bind(UserPaymentInfoRepositoryInterface::class, UserPaymentInfoRepository::class);
        $this->app->bind(UserWithdrawRepositoryInterface::class, UserWithdrawRepository::class);
        $this->app->bind(CountryRepositoryInterface::class, CountryRepository::class);
        $this->app->bind(PaymentMethodRepositoryInterface::class, PaymentMethodRepository::class);
        $this->app->bind(PlanRepositoryInterface::class, PlanRepository::class);
        $this->app->bind(AcademyPaymentMethodRepositoryInterface::class, AcademyPaymentMethodRepository::class);
        $this->app->bind(AcademyWithdrawRepositoryInterface::class, AcademyWithdrawRepository::class);
        $this->app->bind(ReceiverAccountRepositoryInterface::class, ReceiverAccountRepository::class);
        $this->app->bind(InstructorReceiverAccountRepositoryInterface::class, InstructorReceiverAccountRepository::class);
        $this->app->bind(UserPlanRepositoryInterface::class, UserPlanRepository::class);
        $this->app->bind(PagesRepositoryInterface::class, PagesRepository::class);
        $this->app->bind(SectionRepositoryInterface::class, SectionRepository::class);
        $this->app->bind(LandingPageRepositoryInterface::class, LandingPageRepository::class);
        $this->app->bind(BagRepositoryInterface::class, BagRepository::class);
        $this->app->bind(BagPurchaseRepositoryInterface::class, BagPurchaseRepository::class);
        $this->app->bind(AcademicYearRepositoryInterface::class, AcademicYearRepository::class);
        $this->app->bind(GradeRepositoryInterface::class, GradeRepository::class);
        $this->app->bind(TermRepositoryInterface::class, TermRepository::class);
        $this->app->bind(SubjectRepositoryInterface::class, SubjectRepository::class);
}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->resolving(JsonResponse::class, function (JsonResponse $response) {
            $response->setEncodingOptions(JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        });
        TenantObserver::class;
        Model::unguard();
        Scramble::routes(function (Route $route) {
            return str_starts_with($route->uri(), 'api/')
                || str_starts_with($route->uri(), 'admin/');
        });
    }
}
