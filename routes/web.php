<?php

use App\Http\Controllers\MasterData\ItemGroupController;
use App\Http\Controllers\MasterData\OutletController;
use App\Http\Controllers\MasterData\UnitOfMeasureController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => Auth::check()
    ? redirect()->route('dashboard')
    : redirect()->route('login'))->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::get('master-data/outlets', [OutletController::class, 'index'])
        ->middleware('can:master-data.view')
        ->name('master-data.outlets.index');
    Route::post('master-data/outlets', [OutletController::class, 'store'])->name('master-data.outlets.store');
    Route::patch('master-data/outlets/{outlet}', [OutletController::class, 'update'])->name('master-data.outlets.update');
    Route::patch('master-data/outlets/{outlet}/toggle', [OutletController::class, 'toggle'])->name('master-data.outlets.toggle');
    Route::delete('master-data/outlets/{outlet}', [OutletController::class, 'destroy'])->name('master-data.outlets.destroy');

    Route::get('master-data/uom', [UnitOfMeasureController::class, 'index'])
        ->middleware('can:master-data.view')
        ->name('master-data.uom.index');
    Route::post('master-data/uom', [UnitOfMeasureController::class, 'store'])->name('master-data.uom.store');
    Route::patch('master-data/uom/{unitOfMeasure}', [UnitOfMeasureController::class, 'update'])->name('master-data.uom.update');
    Route::patch('master-data/uom/{unitOfMeasure}/toggle', [UnitOfMeasureController::class, 'toggle'])->name('master-data.uom.toggle');
    Route::delete('master-data/uom/{unitOfMeasure}', [UnitOfMeasureController::class, 'destroy'])->name('master-data.uom.destroy');

    Route::get('master-data/item-groups', [ItemGroupController::class, 'index'])
        ->middleware('can:master-data.view')
        ->name('master-data.item-groups.index');
    Route::post('master-data/item-groups', [ItemGroupController::class, 'store'])->name('master-data.item-groups.store');
    Route::patch('master-data/item-groups/{itemGroup}', [ItemGroupController::class, 'update'])->name('master-data.item-groups.update');
    Route::patch('master-data/item-groups/{itemGroup}/toggle', [ItemGroupController::class, 'toggle'])->name('master-data.item-groups.toggle');
    Route::delete('master-data/item-groups/{itemGroup}', [ItemGroupController::class, 'destroy'])->name('master-data.item-groups.destroy');
});

require __DIR__.'/settings.php';
