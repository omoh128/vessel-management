<?php

use App\Http\Controllers\VesselController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Nautic Network — Web Routes
|--------------------------------------------------------------------------
*/

Route::redirect('/', '/vessels');Route::prefix('vessels')->name('vessels.')->group(function () {

    // 1. Static/Specific routes FIRST
    Route::get('/',                [VesselController::class, 'index'])->name('index');
    Route::get('/create',          [VesselController::class, 'create'])->name('create'); // MOVED UP
    Route::get('/import',          [VesselController::class, 'importForm'])->name('import');
    Route::get('/export/xml',      [VesselController::class, 'exportXml'])->name('export');

    // 2. Wildcard routes LAST
    Route::get('/{vessel}',        [VesselController::class, 'show'])->name('show');
    Route::get('/{vessel}/edit',   [VesselController::class, 'edit'])->name('edit');

    // 3. Post/Put/Delete
    Route::post('/',               [VesselController::class, 'store'])->name('store');
    Route::post('/import/upload',  [VesselController::class, 'importUpload'])->name('import.upload');
    Route::put('/{vessel}',        [VesselController::class, 'update'])->name('update');
    Route::delete('/{vessel}',     [VesselController::class, 'destroy'])->name('destroy');
});