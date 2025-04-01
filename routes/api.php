<?php

use App\Http\Controllers\Task\TaskDestroyController;
use App\Http\Controllers\Task\TaskIndexController;
use App\Http\Controllers\Task\TaskStoreController;
use App\Http\Controllers\Task\TaskUpdateController;
use App\Http\Controllers\User\UserIndexController;
use Illuminate\Support\Facades\Route;

Route::get('/users', UserIndexController::class)->name('user.index');

Route::post('/tasks', TaskStoreController::class)->name('task.store');
Route::patch('/tasks/{task}', TaskUpdateController::class)->name('task.update');
Route::delete('/tasks/{task}', TaskDestroyController::class)->name('task.destroy');
Route::get('/tasks', TaskIndexController::class)->name('task.index');
