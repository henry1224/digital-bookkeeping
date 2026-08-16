<?php

use App\Http\Controllers\MasterData\AccountController;
use App\Http\Controllers\MasterData\BankAccountController;
use App\Http\Controllers\MasterData\ItemController;
use App\Http\Controllers\MasterData\ItemGroupController;
use App\Http\Controllers\MasterData\OutletController;
use App\Http\Controllers\MasterData\SupplierController;
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

    Route::get('master-data/suppliers', [SupplierController::class, 'index'])
        ->middleware('can:master-data.view')
        ->name('master-data.suppliers.index');
    Route::post('master-data/suppliers', [SupplierController::class, 'store'])->name('master-data.suppliers.store');
    Route::patch('master-data/suppliers/{supplier}', [SupplierController::class, 'update'])->name('master-data.suppliers.update');
    Route::patch('master-data/suppliers/{supplier}/toggle', [SupplierController::class, 'toggle'])->name('master-data.suppliers.toggle');
    Route::delete('master-data/suppliers/{supplier}', [SupplierController::class, 'destroy'])->name('master-data.suppliers.destroy');

    Route::get('master-data/accounts', [AccountController::class, 'index'])
        ->middleware('can:master-data.view')
        ->name('master-data.accounts.index');
    Route::post('master-data/accounts', [AccountController::class, 'store'])->name('master-data.accounts.store');
    Route::patch('master-data/accounts/{account}', [AccountController::class, 'update'])->name('master-data.accounts.update');
    Route::patch('master-data/accounts/{account}/toggle', [AccountController::class, 'toggle'])->name('master-data.accounts.toggle');
    Route::delete('master-data/accounts/{account}', [AccountController::class, 'destroy'])->name('master-data.accounts.destroy');

    Route::get('master-data/items', [ItemController::class, 'index'])
        ->middleware('can:master-data.view')
        ->name('master-data.items.index');
    Route::post('master-data/items', [ItemController::class, 'store'])->name('master-data.items.store');
    Route::patch('master-data/items/{item}', [ItemController::class, 'update'])->name('master-data.items.update');
    Route::patch('master-data/items/{item}/toggle', [ItemController::class, 'toggle'])->name('master-data.items.toggle');
    Route::delete('master-data/items/{item}', [ItemController::class, 'destroy'])->name('master-data.items.destroy');

    Route::get('master-data/bank-accounts', [BankAccountController::class, 'index'])->middleware('can:master-data.view')->name('master-data.bank-accounts.index');
    Route::post('master-data/bank-accounts', [BankAccountController::class, 'store'])->name('master-data.bank-accounts.store');
    Route::patch('master-data/bank-accounts/{bankAccount}', [BankAccountController::class, 'update'])->name('master-data.bank-accounts.update');
    Route::patch('master-data/bank-accounts/{bankAccount}/toggle', [BankAccountController::class, 'toggle'])->name('master-data.bank-accounts.toggle');
    Route::delete('master-data/bank-accounts/{bankAccount}', [BankAccountController::class, 'destroy'])->name('master-data.bank-accounts.destroy');
});

require __DIR__.'/settings.php';
