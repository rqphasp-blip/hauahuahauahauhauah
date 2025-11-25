<?php

use Illuminate\Support\Facades\Route;
use App\Providers\plugins\leads01\Leads01Controller;

Route::get('/user/{username}/leads01', [Leads01Controller::class, 'publicList'])
    ->name('leads01.public');

Route::get('/leads01/form/{slug}', [Leads01Controller::class, 'publicForm'])
    ->name('leads01.form');

Route::post('/leads01/form/{slug}', [Leads01Controller::class, 'submit'])
    ->name('leads01.submit');