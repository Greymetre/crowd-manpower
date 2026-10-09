<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CandidateController;
use App\Http\Controllers\CandidateExportController;
use App\Http\Controllers\CandidateImportController;
use App\Models\Candidate;
use Illuminate\Support\Facades\Route;

Route::get('/', [LoginController::class, 'create'])->name('login');
Route::get('/login', [LoginController::class, 'create'])->name('login.form');
Route::post('/login', [LoginController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('login.store');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', fn () => view('dashboard', [
        'stats' => [
            'total' => Candidate::count(),
            'today' => Candidate::whereDate('created_at', today())->count(),
            'month' => Candidate::where('created_at', '>=', now()->startOfMonth())->count(),
        ],
        'recent' => Candidate::latest('id')->limit(5)->get(),
    ]))->name('dashboard');

    // Export & import (registered before the resource so "/candidates/import" isn't read as an ID)
    Route::get('/candidates/export/excel', [CandidateExportController::class, 'excel'])->name('candidates.export.excel');
    Route::get('/candidates/export/pdf', [CandidateExportController::class, 'pdf'])->name('candidates.export.pdf');
    Route::get('/candidates/import', [CandidateImportController::class, 'create'])->name('candidates.import');
    Route::post('/candidates/import', [CandidateImportController::class, 'store'])->name('candidates.import.store');
    Route::get('/candidates/import/template', [CandidateImportController::class, 'template'])->name('candidates.import.template');

    Route::resource('candidates', CandidateController::class)->except('show');

    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
});
