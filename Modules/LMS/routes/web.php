<?php

use Illuminate\Support\Facades\Route;
use Modules\LMS\Http\Controllers\InstallerController;
use Modules\LMS\Http\Controllers\Auth\LoginController;
use Modules\LMS\Http\Controllers\LocalizationController;
use Modules\LMS\Http\Controllers\Auth\RegisterController;
use Modules\LMS\Http\Controllers\Frontend\BlogController;
use Modules\LMS\Http\Controllers\Frontend\CartController;
use Modules\LMS\Http\Controllers\Frontend\ExamController;
use Modules\LMS\Http\Controllers\Frontend\HomeController;
use Modules\LMS\Http\Controllers\Frontend\ForumController;
use Modules\LMS\Http\Controllers\Frontend\CourseController;
use Modules\LMS\Http\Controllers\Frontend\ContactController;
use Modules\LMS\Http\Controllers\Auth\ForgotPasswordController;
use Modules\LMS\Http\Controllers\Frontend\InstructorController;
use Modules\LMS\Http\Controllers\Frontend\OrganizationController;
use Modules\LMS\Http\Controllers\Admin\Courses\Quizzes\QuizController;
use Modules\LMS\Http\Controllers\Admin\ThemeController;

/*
 *--------------------------------------------------------------------------
 * API Routes
 *--------------------------------------------------------------------------
 *
 * Here is where you can register API routes for your application. These
 * routes are loaded by the RouteServiceProvider within a group which
 * is assigned the "api" middleware group. Enjoy building your API!
 *
*/

/* ==================== */


Route::group(['middleware' => ['checkInstaller']], function () {
    Route::get('/', [HomeController::class, 'index'])->name('home.index');
    Route::get('/about-us', [HomeController::class, 'aboutUs'])->name('about.us');
    Route::get('/category-course/{slug}', [HomeController::class, 'categoryCourse'])->name('category.course');
    Route::get('success', [HomeController::class, 'success'])->name('success');
    Route::get('verify-mail/{id}/{hash}', [HomeController::class, 'verificationMail'])->name('mail.verify');
    Route::post('subscribe', [HomeController::class, 'newsletterSubscribe'])->name('newsletter.subscribe');
    Route::get('privacy-policy', [HomeController::class, 'policyContent'])->name('privacy.policy');
    Route::get('terms-conditions', [HomeController::class, 'termsCondition'])->name('terms.condition');
    Route::get('categories', [HomeController::class, 'categoryList'])->name('category.list');

    Route::get('blogs', [BlogController::class, 'blogs'])->name('blog.list');
    Route::get('blogs/{slug}', [BlogController::class, 'blogDetail'])->name('blog.detail');

    Route::get('instructors', [InstructorController::class, 'index'])->name('instructor.list');
    Route::get('users/{id}/profile', [HomeController::class, 'userDetail'])->name('users.detail');
    Route::get('courses', [CourseController::class, 'courseList'])->name('course.list');
    Route::get('courses/{slug}', [CourseController::class, 'courseDetail'])->name('course.detail');
    Route::get('course-bundles', [CourseController::class, 'courseBundle'])->name('course.bundle');

    Route::get('forums', [ForumController::class, 'forumsList']);
    Route::get('login', [LoginController::class, 'showForm'])->name('login');
    Route::post('login', [LoginController::class, 'login'])->name('auth.login');
    Route::get('register', [RegisterController::class, 'registerForm'])->name('register.page');
    Route::post('register', [RegisterController::class, 'register'])->name('auth.register');
    Route::get('forgot-password', [ForgotPasswordController::class, 'showForm'])->name('password.request');
    Route::post('forgot-password', [ForgotPasswordController::class, 'forgotPassword'])->name('forgot.password');
    Route::get('reset-password/{token}', [ForgotPasswordController::class, 'passwordReset'])->name('password.reset');
    Route::post('reset-password', [ForgotPasswordController::class, 'passwordUpdate'])->name('password.update');

    Route::get('add-to-cart', [CartController::class, 'addToCart'])->name('add.to.cart');
    Route::get('remove-cart', [CartController::class, 'removeCart'])->name('remove.cart');
    Route::get('cart', [CartController::class, 'cartCourseList'])->name('cart.page');
    Route::get('checkout', [CartController::class, 'checkoutPage'])->name('checkout.page');
    Route::get('contact', [ContactController::class, 'index'])->name('contact.page');
    Route::post('contact', [ContactController::class, 'store'])->name('contact.store');
    Route::get('organizations', [OrganizationController::class, 'index'])->name('organization.list');
    Route::get('apply-coupon', [CartController::class, 'applyCoupon'])->name('apply.coupon');

    Route::group(
        ['middleware' => 'auth'],
        function () {
            Route::post('forum-post', [ForumController::class, 'forumPost']);
            Route::post('checkout', [CartController::class, 'checkout'])->name('checkout');
            Route::get('success', [CartController::class, 'transactionSuccess'])->name('transaction.success');
            Route::post('blog/store', [BlogController::class, 'store'])->name('blog.comment');
            Route::get('payment-form', [CartController::class, 'paymentFormRender'])->name('payment.form');
            Route::get('paypal/cancel', [CartController::class, 'paypalCancel'])->name('paypal.cancel');
            Route::get('paypal/success', [CartController::class, 'success'])->name('payment.success');
            Route::get('learn/course/{slug}', [CourseController::class, 'courseVideoPlayer'])->name('play.course');
            Route::get('learn/course-topic', [CourseController::class, 'leanCourseTopic'])->name('learn.course.topic');
            Route::post('course-review', [CourseController::class, 'review'])->name('review');
            Route::post('enrolled', [CartController::class, 'courseEnrolled'])->name('course.enrolled');
            Route::post('quiz/{id}/store', [QuizController::class, 'quizStoreResult'])->name('quiz.store.result');
            Route::post('user/submit-quiz-answer/{quiz_id}/{type}', [QuizController::class, 'submitQuizAnswer'])->name('user.submit.quiz.answer');
            Route::get('exam/{type}/{exam_type_id}/{course_id}', [ExamController::class, 'examStart'])->name('exam.start');
            Route::post('exam-store', [ExamController::class, 'store'])->name('exam.store');
            Route::get('add-wishlist', [HomeController::class, 'addWishlist'])->name('add.wishlist');
        }
    );
    Route::get('language', [LocalizationController::class, 'setLanguage'])->name('language.set');
    // Theme.
    Route::get('theme/activation/{slug}/{uuid}', [ThemeController::class, 'activationByUrl'])->name('theme.activation_by_uuid');
});

// Localizations.
Route::controller(InstallerController::class)->group(
    function () {
        Route::get('install', 'installContent')->name('install');
        Route::get('install/requirements', 'requirement')->name('install.requirement');
        Route::get('install/permission', 'permission')->name('install.permission');
        Route::get('install/environment', 'environmentForm')->name('install.environment.form');
        Route::post('install/environment', 'environment')->name('install.environment');
        Route::get('install/database', 'databaseForm')->name('install.database.form');

        Route::post('install/database', 'database')->name('install.database');
        Route::get('install/import-demo', 'importDemo')->name('install.import-demo');
        Route::get('install/license', 'licenseForm')->name('license.form');
        Route::post('install/purchase-code', 'purchaseCode')->name('purchase.code');
        Route::get('install/demo', 'imported')->name('install.demo');
        Route::get('install/final', 'finish')->name('install.final');
        Route::get('license', 'licenseVerifyForm')->name('license.verify.form');
        Route::post('license', 'licenseVerify')->name('license.verify');
    }
);
