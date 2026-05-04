<?php

use Illuminate\Support\Facades\Route;

// Auth
Route::middleware('guest')->group(function () {
    Route::livewire('/login', 'pages::auth.login')->name('login');
});

Route::post('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login');
})->name('logout');

// Routes protégées
Route::middleware(['auth'])->group(function () {
    Route::livewire('/dashboard', 'pages::dashboard')->name('dashboard');

    // Patients
    Route::livewire('/patients', 'pages::patients.index')->name('patients.index');

    Route::middleware(['role:admin,medecin'])->group(function () {
        Route::livewire('/patients/create', 'pages::patients.create')->name('patients.create');
        Route::livewire('/patients/{id}/edit', 'pages::patients.edit')->name('patients.edit');

        // Consultations
        // Route::livewire('/consultations/toxicologie', 'pages::consultations.toxicologie')->name('consultations.toxicologie');
        Route::livewire('/consultations/psychopathologie', 'pages::consultations.psychopathologie')->name('consultations.psychopathologie');
        Route::livewire('/consultations/medecine', 'pages::consultations.medecine')->name('consultations.medecine');

        // Traitements
        Route::livewire('/treatments', 'pages::treatments.index')->name('treatments.index');

        // Traitements
        Route::prefix('treatments')->name('treatments.')->group(function () {
            Route::livewire('/', 'pages::treatments.index')->name('index');
            Route::livewire('/create', 'pages::treatments.create')->name('create');
            Route::livewire('/{id}/edit', 'pages::treatments.edit')->name('edit');
        });

        // Rendez-vous
        Route::livewire('/appointments', 'pages::appointments.index')->name('appointments.index');
    });

    Route::livewire('/patients/{id}', 'pages::patients.show')->name('patients.show');


    // Admin
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::livewire('/users', 'pages::admin.users')->name('users');
    });
});

// Consultations Toxicologie
Route::middleware(['auth', 'role:admin,medecin'])->prefix('consultations/toxicologie')->name('consultations.toxicologie.')->group(function () {
    Route::livewire('/', 'pages::consultations.toxicologie.index')->name('index');
    Route::livewire('/create', 'pages::consultations.toxicologie.create')->name('create');
    Route::livewire('/{id}/show', 'pages::consultations.toxicologie.show')->name('show');
    Route::livewire('/{id}/edit', 'pages::consultations.toxicologie.edit')->name('edit');
});

Route::get('/', function () {
    return redirect()->route('dashboard');
});
