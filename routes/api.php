<?php

use App\Http\Controllers\BlogPostsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;

use App\Http\Middleware\InputSanitization;

use App\Http\Controllers\ShortUrlController;
use App\Http\Controllers\TngFileConvertController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Middleware\ConcurrentRequestsThrottle;
use App\Http\Middleware\VerifyToken;

// use App\Models\TngFileConvert;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });





Route::middleware(['concurrent', 'sanitize','custom.auth'])->group(function () {
// Route::middleware([ConcurrentRequestsThrottle::class])->group(function () {
    // Route::middleware(['check.site.prefix'])->name('member.')->prefix('member')->namespace('User')->group(function () {
    //     Route::middleware(['guest'])->group(function () {

    Route::middleware(['auth.required'])->group(function () {
        Route::post('/logout', [GoogleAuthController::class, 'logout']);

        Route::name('auth.')->prefix('auth')->group(function(){
            Route::name('shorturl.')->prefix('shorturl')->group(function(){
                Route::get('/list',         [ShortUrlController::class, 'getListByUser']);
                Route::post('/',            [ShortUrlController::class, 'storeAuth']);
                Route::put('/',             [ShortUrlController::class, 'updateAuth']);
                Route::delete('/{id}',      [ShortUrlController::class, 'deleteAuth']);
            });
        });
    });
    
    Route::post('/googleauth/callback', [GoogleAuthController::class, 'handleGoogleCallback']);
    Route::name('shorturl.')->prefix('shorturl')->group(function(){
        Route::get('/{customPath}/{shorturl}',  [ShortUrlController::class, 'getRedirectUrl']);
        Route::get('/{shorturl}',               [ShortUrlController::class, 'getRedirectUrlGuest']);
        Route::post('/',                        [ShortUrlController::class, 'store']);
    });

    Route::name('tng.')->prefix('tng')->group(function () {
        Route::post('/',            [TngFileConvertController::class, 'store']);
    });

    Route::name('blog.')->prefix('blog')->group(function(){
        Route::get('/{slug}',   [BlogPostsController::class, 'get']);
    });

    // Route::name('test.')->prefix('test')->group(function () {
    //     Route::get('/', 'testController@index');
    //     Route::get('/{id}', 'testController@show');
    // });


});

// Route::middleware([])->get('/your-route', 'YourController@yourMethod');
