<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API;
use App\Http\Controllers\API\GoalChallengeController;
use App\Http\Controllers\API\HomeInformationController;
use App\Http\Controllers\API\MentalPreparationController;
use App\Http\Controllers\API\NutritionElementController;
use App\Http\Controllers\API\ProgramController;
use App\Http\Controllers\API\ProgramPurchaseController;
use App\Http\Controllers\API\UserWorkoutLogController;
use App\Http\Controllers\API\UserGoalAchievementController;
use App\Http\Controllers\CompetitionFeedbackController;
use App\Http\Controllers\QuestionnaireApiController;
use App\Http\Controllers\TrainingLogController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::post('addTransactionId',[ API\AddTransactionIdController::class, 'addTransactionId']);
Route::post('/webhooks/subscriptions/apple',[ API\AppleNotificationController::class, 'handleNotification']);
Route::post('/webhooks/subscriptions/google', [API\GoogleNotificationController::class, 'handleNotification']);


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});



 Route::get('programs', [API\ProgramController::class, 'fetchUserPrograms'])->name('mobile.user-programs');
 Route::get('programs-free', [API\ProgramController::class, 'fetchUserProgramsFree'])->name('mobile.user-programs');

 Route::get('getVideoUrl', [API\ProgramController::class, 'getVideoStream'])->name('video.stream');
 Route::get('getVideoUrlFree', [API\ProgramController::class, 'getVideoStreamFree'])->name('video.stream');





Route::post('register',[ API\UserController::class, 'register']);
Route::post('login',[ API\UserController::class, 'login']);
Route::post('forget-password',[ API\UserController::class, 'forgetPassword']);
Route::post('social-mail-login',[ API\UserController::class, 'socialMailLogin' ]);
Route::post('social-otp-login',[ API\UserController::class, 'socialOTPLogin' ]);
Route::get('user-detail',[ API\UserController::class, 'userDetail']);
Route::get('get-appsetting', [ API\UserController::class, 'getAppSetting'] );

Route::get('exercise-list', [ API\ExerciseController::class, 'getList' ]);
Route::get('exercise-detail', [ API\ExerciseController::class, 'getDetail' ]);


Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('verifyAndCreateSubscription',[ API\VerifyAndCreateSubscriptionController::class, 'verifyAndCreateSubscription']);

    Route::get('home-informations', [HomeInformationController::class, 'show']);
    Route::get('/user-workout-logs', [UserWorkoutLogController::class, 'index']);
    Route::post('/user-workout-logs', [UserWorkoutLogController::class, 'store']);
    Route::get('/user-workout-logs/weekly', [UserWorkoutLogController::class, 'getWeeklyLogs']);
    Route::post('/user-workout-logs/manual', [UserWorkoutLogController::class, 'storeManualEntry']);
    // Route::get('programs', [API\ProgramController::class, 'fetchUserPrograms'])->name('mobile.user-programs');

    Route::get('getUserSubscription',[ API\UserController::class, 'getUserSubscription']);

    Route::get('dashboard-detail',[ API\DashboardController::class, 'dashboard']);
    Route::post('update-profile', [ API\UserController::class, 'updateProfile']);
    Route::post('change-password', [ API\UserController::class, 'changePassword']);
    Route::post('update-user-status', [ API\UserController::class, 'updateUserStatus']);
    Route::post('delete-user-account', [ API\UserController::class, 'deleteUserAccount']);
    Route::get('logout',[ API\UserController::class, 'logout']);

    Route::get('payment-gateway-list', [ API\PaymentGatewayController::class, 'getList'] );

    Route::get('assign-diet-list', [ API\AssignUserController::class, 'getAssignDiet' ]);
    Route::get('assign-workout-list', [ API\AssignUserController::class, 'getAssignWorkout' ]);

    Route::get('equipment-list', [ API\EquipmentController::class, 'getList' ]);

    Route::get('categorydiet-list', [ API\CategoryDietController::class, 'getList' ]);

    Route::get('workouttype-list', [ API\WorkoutTypeController::class, 'getList' ]);

    Route::get('diet-list', [ API\DietController::class, 'getList' ]);
    Route::post('diet-detail', [ API\DietController::class, 'getDetail' ]);

    Route::get('category-list', [ API\CategoryController::class, 'getList' ]);
    Route::get('tags-list', [ API\TagsController::class, 'getList' ]);

    Route::get('level-list', [ API\LevelController::class, 'getList' ]);
    
    Route::get('bodypart-list', [ API\BodyPartController::class, 'getList' ]);
    
    Route::get('workout-list', [ API\WorkoutController::class, 'getList' ]);
    Route::get('workout-monthly-programs', [ API\WorkoutController::class, 'getMonthlyPrograms' ]);
    Route::get('workout-detail', [ API\WorkoutController::class, 'getDetail' ]);
    Route::get('workout-detail-with-access', [ API\WorkoutController::class, 'getDetailWithAccess' ]);
    Route::get('workoutday-list', [ API\WorkoutController::class, 'workoutDayList' ]);
    Route::get('workoutday-exercise-list', [ API\WorkoutController::class, 'workoutDayExerciseList' ]);
    
    Route::get('get-favourite-diet', [ API\DietController::class, 'getUserFavouriteDiet' ]);
    Route::post('set-favourite-diet', [ API\DietController::class, 'userFavouriteDiet' ]);

    //Route::get('exercise-list', [ API\ExerciseController::class, 'getList' ]);
    //Route::get('exercise-detail', [ API\ExerciseController::class, 'getDetail' ]);
   
    Route::get('post-list', [ API\PostController::class, 'getList' ]);
    Route::post('post-detail', [ API\PostController::class, 'getDetail' ]);

    Route::get('get-favourite-workout', [ API\WorkoutController::class, 'getUserFavouriteWorkout' ]);
    Route::post('set-favourite-workout', [ API\WorkoutController::class, 'userFavouriteWorkout' ]);
    
    Route::get('product-list', [ API\ProductController::class, 'getlist']);
    Route::get('productcategory-list', [ API\ProductCategoryController::class, 'getlist']);
    Route::post('product-detail', [ API\ProductController::class, 'getDetail']);

    Route::get('package-list', [ API\PackageController::class, 'getList' ]);

    Route::get('subscriptionplan-list',[ API\SubscriptionController::class, 'getList']);
    Route::post('subscribe-package',[ API\SubscriptionController::class, 'subscriptionSave']);
    Route::post('cancel-subscription',[ API\SubscriptionController::class, 'cancelSubscription']);
    Route::post('create-subscription',[ API\SubscriptionController::class, 'createSubscription']);
    Route::post('subscribe',[ API\SubscriptionController::class, 'subscribe']);



    Route::get('get-setting',[ API\DashboardController::class, 'getSetting']);

    Route::post('usergraph-save', [ API\UserGraphController::class, 'saveGraphData']);
    Route::get('usergraph-list', [ API\UserGraphController::class, 'getGraphDataList']);
    Route::post('usergraph-delete', [ API\UserGraphController::class, 'deleteGraphData']);

    Route::post('notification-list', [ API\NotificationController::class, 'getList'] );
    Route::get('notification-detail', [ API\NotificationController::class, 'getNotificationDetail'] );

    Route::get('nutrition-elements', [NutritionElementController::class,'index'])->name('nutrition-elements.index');;
    Route::get('nutrition-elements/{slug}', [NutritionElementController::class,'show']);

    Route::get('goal-challenges/{theme}', [GoalChallengeController::class,'listByTheme']);
    Route::get('mental-preparations', [MentalPreparationController::class, 'index']);
    Route::get('mental-preparations-list', [MentalPreparationController::class, 'getList']);
    Route::get('mental-preparations-detail', [MentalPreparationController::class, 'getDetail']);
    Route::get('mental-preparations-detail-with-access', [MentalPreparationController::class, 'getDetailWithAccess']);
    Route::get('mental-preparations/{slug}', [MentalPreparationController::class, 'show']);
    Route::post('/nutrition-analysis', [API\GeminiController::class, 'generateCaption']);

    Route::get('/user/weight-entries', [API\WeightController::class, 'getWeightHistory']);
    Route::post('/user/weight-entries', [API\WeightController::class, 'storeWeight']);
    Route::post('user/update-ideal-weight', [API\UserController::class, 'updateIdealWeight']);

    // Goal Achievements
    Route::get('goal-achievements', [UserGoalAchievementController::class, 'index']);
    Route::post('goal-achievements', [UserGoalAchievementController::class, 'store']);
    Route::get('goal-achievements/stats', [UserGoalAchievementController::class, 'stats']);
    Route::delete('goal-achievements/{id}', [UserGoalAchievementController::class, 'destroy']);

    // Routes pour les achats de programmes
    Route::post('purchase-program', [API\ProgramPurchaseController::class, 'verifyAndCreatePurchase']);
    Route::get('my-purchased-programs', [API\ProgramPurchaseController::class, 'getUserPurchases']);
    Route::post('check-program-access', [API\ProgramPurchaseController::class, 'checkProgramAccess']);

    // Routes pour les paiements Stripe
    Route::post('create-program-payment-intent', [API\ProgramPurchaseController::class, 'createPaymentIntent']);
    Route::post('confirm-program-purchase', [API\ProgramPurchaseController::class, 'confirmPurchase']);

    // Routes pour les questionnaires de compétition
    Route::post('competition-feedback', [CompetitionFeedbackController::class, 'store']);
    Route::get('competition-feedback', [CompetitionFeedbackController::class, 'index']);
    Route::get('competition-feedback/{id}', [CompetitionFeedbackController::class, 'show']);
    Route::put('competition-feedback/{id}', [CompetitionFeedbackController::class, 'update']);
    Route::delete('competition-feedback/{id}', [CompetitionFeedbackController::class, 'destroy']);

    // Routes pour les carnets d'entraînement
    Route::post('training-logs', [TrainingLogController::class, 'store']);
    Route::get('training-logs', [TrainingLogController::class, 'index']);
    Route::get('training-logs/stats', [TrainingLogController::class, 'getStats']);
    Route::get('training-logs/discipline-stats', [TrainingLogController::class, 'getDisciplineStats']);
    Route::get('training-logs/by-date/{date}', [TrainingLogController::class, 'getByDate']);
    Route::get('training-logs/{id}', [TrainingLogController::class, 'show']);
    Route::put('training-logs/{id}', [TrainingLogController::class, 'update']);
    Route::delete('training-logs/{id}', [TrainingLogController::class, 'destroy']);

    // Route pour les moyennes des questionnaires de compétition
    Route::get('competition-feedback-averages', [QuestionnaireApiController::class, 'getCompetitionFeedbackAverages']);

});