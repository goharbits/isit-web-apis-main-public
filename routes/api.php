<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\AvailService\AvailServiceController;
use App\Http\Controllers\Chat\ChatController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Employee\EmployeeController;
use App\Http\Controllers\Feedback\FeedbackController;
use App\Http\Controllers\Frontend\FrontendController;
use App\Http\Controllers\Notification\NotificationController;
use App\Http\Controllers\Profile\ProfileController;
use App\Http\Controllers\Request\RequestController;
use App\Http\Controllers\Role\RoleController;
use App\Http\Controllers\Schedule\ScheduleController;
use App\Http\Controllers\Service\ServiceController;
use App\Http\Controllers\Setting\SettingController;
use App\Http\Controllers\Subscription\SubscriptionController;
use App\Http\Controllers\Subscription\WebhookController;
use App\Http\Controllers\SuperAdmin\CorporateController;
use App\Http\Controllers\SuperAdmin\ProfessionalController;
use App\Http\Controllers\SuperAdmin\ReviewProfileController;
use App\Http\Controllers\SuperAdmin\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

Route::get('/feedback/empty', function () {
    try {
        // Truncate the feedback table
        DB::table('subscriptions')->truncate();
        return response()->json(['success' => true, 'message' => 'Feedback table emptied successfully.']);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => 'Failed to empty feedback table.', 'error' => $e->getMessage()], 500);
    }
})->name('feedback.empty');

Route::get('/clear', function () {
    Artisan::call('cache:clear');
    Artisan::call('route:clear');
    Artisan::call('config:clear');
    Artisan::call('view:clear');
    Artisan::call('optimize:clear');
    Artisan::call('optimize');
    return "All caches cleared and optimized!";
});

Route::prefix('v1')->name('api.v1.')->group(function () {
    //Frontend
    Route::prefix('frontend')->name('frontend.')->group(function () {
        Route::post('/filter', [FrontendController::class, 'filter'])->name('filter');
        Route::get('/user/{id}', [FrontendController::class, 'userDetail'])->name('user.detail');
        Route::post('/popular/professional', [FrontendController::class, 'getPopularProfessional']);
    });

    // Authentication
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/forget-password', [AuthController::class, 'forgetPassword'])->name('forget.password');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('reset.password');
    Route::post('/verify-otp', [AuthController::class, 'verifyOTP'])->name('verify.otp');
    Route::post('/verify/email/{id}', [AuthController::class, 'verifyEmail'])->name('verify.email');

    // Route::middleware('auth:api')->group(function () {

    //GET Auth User
    Route::get('/auth/user/{id}', [AuthController::class, 'getUser']);

    //SuperAdmin
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/statistics', [DashboardController::class, 'getStatisticsOfSuperAdmin'])->name('dashboard');
        Route::apiResource('corporate', CorporateController::class);
        Route::post('corporate/status/{status}/update/{id}', [CorporateController::class, 'updateStatus'])->name('corporate.status');
        Route::apiResource('professional', ProfessionalController::class);
        Route::post('professional/status/{status}/update/{id}', [ProfessionalController::class, 'updateStatus'])->name('professional.status');
        Route::apiResource('user', UserController::class);
        Route::post('user/status/{status}/update/{id}', [UserController::class, 'updateStatus'])->name('user.status');

        Route::prefix('review')->name('review.')->group(function () {
            Route::get('/profiles', [ReviewProfileController::class, 'getReviewProfiles'])->name('profiles');
            Route::get('/profile/{id}', [ReviewProfileController::class, 'getReviewProfile'])->name('profile');
            Route::post('/profile/status', [ReviewProfileController::class, 'updateReviewProfile'])->name('profile.status');
        });
    });

    Route::prefix('professional')->name('professional.')->group(function () {
        Route::post('/schedule', [ScheduleController::class, 'store'])->name('store.schedule');
        Route::post('/schedule/update', [ScheduleController::class, 'update'])->name('update.schedule');
        Route::get('/schedule/{id}', [ScheduleController::class, 'index'])->name('schedule');
        Route::delete('/schedule/{id}', [ScheduleController::class, 'delete'])->name('delete.schedule');
        Route::get('/feedback/{id}', [FeedbackController::class, 'getFeedback'])->name('feedback');
        Route::get('/feedback/single/{id}', [FeedbackController::class, 'getSingleFeedback'])->name('single.feedback');
    });

    Route::prefix('user')->name('user.')->group(function () {
        Route::post('/feedback/reply',  [FeedbackController::class, 'giveFeedbackReply'])->name('give.feedback.reply');
        Route::post('/feedback',  [FeedbackController::class, 'giveFeedback'])->name('give.feedback');
        Route::get('/{userId}/availservices', [AvailServiceController::class, 'getAvailServices'])->name('avail.services');
    });

    //Settings
    Route::prefix('setting')->name('setting.')->group(function () {
        Route::post('reset-password', [SettingController::class, 'resetPassword'])->name('reset.password');
    });


    // Route::middleware(['role:corporate'])->group(function () {
    Route::apiResource('/role', RoleController::class);
    // });

    Route::apiResource('/service', ServiceController::class);
    Route::get('/service/user/{userId}', [ServiceController::class, 'getUserService']);

    Route::prefix('corporate')->name('corporate.')->group(function () {
        //Statistics
        Route::get('{id}/statistics', [DashboardController::class, 'getStatisticsOfCorporate'])->name('statistics');
        //Profile
        Route::post('profile/{id}', [ProfileController::class, 'updateCorporateProfile'])->name('profile');
        //Employees
        Route::prefix('{id}/employee')->name('employee.')->group(function () {
            Route::get('/', [EmployeeController::class, 'index'])->name('index');
            Route::get('/{employee_id}', [EmployeeController::class, 'show'])->name('show');
            Route::post('/', [EmployeeController::class, 'store'])->name('store');
            Route::post('/{employee_id}', [EmployeeController::class, 'update'])->name('update');
            Route::post('/{employee_id}/{status}', [EmployeeController::class, 'updateStatus'])->name('update.status');
        });
    });

    Route::prefix('employee')->name('emp.')->group(function () {
        Route::post('profile/{id}', [ProfileController::class, 'updateEmployeeProfile'])->name('profile');
    });

    Route::prefix('user')->name('user')->group(function () {
        Route::post('profile/{id}', [ProfileController::class, 'updateUserProfile'])->name('profile');
        Route::post('request', [RequestController::class, 'sendRequest'])->name('request');
        Route::post('request/reschedule', [RequestController::class, 'rescheduleRequest'])->name('reschedule.request');
    });

    Route::prefix('professional')->name('professional.')->group(function () {
        Route::post('profile/{id}', [ProfileController::class, 'updateProfessionalProfile'])->name('profile');
        Route::post('request', [RequestController::class, 'updateRequest'])->name('request');
    });

    //Common
    Route::get('/conversations/{id}', [ChatController::class, 'getConversations']);
    Route::post('/send/message', [ChatController::class, 'sendMessage']);
    Route::get('/get/messages/{id}', [ChatController::class, 'getMessages']);
    Route::get('/notifications/{userId}', [NotificationController::class, 'index']);
    Route::get('/notification/{id}/{userId}', [NotificationController::class, 'show']);
    Route::get('/request/{id}', [RequestController::class, 'getRequest']);

    //Subscription
    Route::get('/plans/{roleName}', [SubscriptionController::class, 'getPlans']);
    Route::post('/plans/subscribe', [SubscriptionController::class, 'subscriptionSession']);
    Route::post('/check/subscription-status', [SubscriptionController::class, 'checkSubscriptionStatus']);
    Route::post('/plans/subscription/cancel', [SubscriptionController::class, 'subscriptionCancel']);
    Route::post('/webhook', [WebhookController::class, 'handle']);
    Route::get('/check-status', [WebhookController::class, 'checkStatus']);

    // });
});
