<?php

use Illuminate\Support\Facades\Route;

Route::prefix('themeswitcherwidget')->group(function () {
    Route::get('/', 'ThemeSwitcherWidgetController@index');
});

Route::get('/app/set-theme-color', function () {
    foreach (request()->except('_token') as $key => $part) {
        session([$key => $part]);
    }

    return back();
})->name('set.themeColor');
