<?php

use App\Http\Controllers\WialonBackup;
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
    return view('welcome');
});

Route::get('/download', [WialonBackup::class, 'download']);
Route::get('/test', [WialonBackup::class, 'test']);
Route::get('/add', [WialonBackup::class, 'addIds']);
Route::get('/checkFiles', [WialonBackup::class, 'checkFiles']);