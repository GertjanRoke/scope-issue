<?php

use App\Models\Item;
use App\Models\Category;
use Illuminate\Support\Facades\Route;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
    DB::enableQueryLog();

    Item::query()
        ->select([
            'items.id',
            'name',
        ])
        ->get();

    Item::query()
        ->applyScopes()
        ->withoutGlobalScope('sort')
        ->select([
            'items.id',
            'name',
        ])
        ->get();

    Category::query()
        ->with([
            'items' => function (HasMany $query) {
                $query
                    ->applyScopes()
                    ->withoutGlobalScope('sort')
                    ->select([
                        'name',
                    ]);
            },
        ])
        ->select([
            'id',
            'name',
        ])
        ->get();

    Category::query()
        ->with([
            'items' => function (HasMany $query) {
                $query
                    ->select([
                        'name',
                    ]);
            },
        ])
        ->select([
            'id',
            'name',
        ])
        ->get();

    return DB::getQueryLog();
});
