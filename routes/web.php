<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect('/home');
});


Route::post('/chatbot/callback/accept', 'App\Http\Controllers\Controller@chatbot_callback_api')->name('chatbot-callback');

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/contacts', [App\Http\Controllers\ContactController::class, 'index'])->name('contacts');
Route::get('/counsellors', [App\Http\Controllers\CounsellorController::class, 'index'])->name('counsellors');
Route::post('/counsellors/bulk', [App\Http\Controllers\CounsellorController::class, 'bulk'])->name('bulk-counsellors');
Route::post('/counsellors/store', [App\Http\Controllers\CounsellorController::class, 'store'])->name('store-counsellor');
Route::get('/messages', [App\Http\Controllers\MessageController::class, 'index'])->name('messages');
