<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return 'Connecté avec succès via Google!';
});

//
//Route::get('/', function () {
//   return view('app'); // This will load resources/views/app.blade.php
//});

//Route::get('/{any}', function () {
 //   return view('app'); // Loads the same Vue.js entry point
//})->where('any', '.*');