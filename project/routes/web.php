<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WhoisController;

Route::get('/', function () {
    return view('main');
});

Route::post('/whois', [WhoisController::class, 'lookup'])->name('whois.lookup');

