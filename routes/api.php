<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;

use App\Http\Middleware\InputSanitization;

use App\Http\Controllers\ShortUrlController;


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


// Route::middleware(['check.site.prefix'])->name('member.')->prefix('member')->namespace('User')->group(function () {
//     Route::middleware(['guest'])->group(function () {
Route::middleware(['sanitize'])->name('shorturl.')->prefix('shorturl')->group(function () {

    // Route::get('/',   'ShortUrlController@redirect');
    // Route::get('/{shorturl}',   'ShortUrlController@redirect');
    // Route::post('/',            'ShortUrlController@store');

    // Route::get('/',   [ShortUrlController::class, 'redirect']);
    Route::get('/{shorturl}',   [ShortUrlController::class, 'getRedirectUrl']);
    Route::post('/',            [ShortUrlController::class, 'store']);


    // Route::name('test.')->prefix('test')->group(function () {
    //     Route::get('/', 'testController@index');
    //     Route::get('/{id}', 'testController@show');
    // });

});

// Route::middleware([])->get('/your-route', 'YourController@yourMethod');
