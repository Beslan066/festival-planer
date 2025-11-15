<?php

use App\Http\Controllers\EventController;
use App\Http\Controllers\ScheduleController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ScheduleController::class, 'index'])->name('home');

// Маршруты для событий
Route::post('/events/toggle', [EventController::class, 'toggle'])->name('events.toggle');
Route::get('/events/stats', [EventController::class, 'getStats'])->name('events.stats');
Route::post('/events/clear', [EventController::class, 'clear'])->name('events.clear');

Route::get('/events/current-state', [EventController::class, 'getCurrentState'])->name('events.current-state');
Route::post('/schedules/clear-current', [ScheduleController::class, 'clearCurrent'])->name('schedules.clear-current');

// Маршруты для расписаний
Route::post('/schedules/generate', [ScheduleController::class, 'generate'])->name('schedules.generate');
Route::post('/schedules/save-selected', [ScheduleController::class, 'saveSelectedSchedule'])->name('schedules.save-selected');
Route::post('/schedules/save-list', [ScheduleController::class, 'saveCustomSchedule'])->name('schedules.save-list');
Route::get('/schedules', [ScheduleController::class, 'getSavedSchedules'])->name('schedules.list');
Route::get('/schedules/load/{id}', [ScheduleController::class, 'load'])->name('schedules.load');
Route::delete('/schedules/delete/{id}', [ScheduleController::class, 'delete'])->name('schedules.delete');
Route::post('/schedules/adjust', [ScheduleController::class, 'adjust'])->name('schedules.adjust');

// Групповое планирование
Route::post('/group/save', [ScheduleController::class, 'saveGroupSelection'])->name('group.save');
Route::post('/group/merge', [ScheduleController::class, 'mergeGroupSelections'])->name('group.merge');
Route::post('/group/clear', [ScheduleController::class, 'clearGroupSelection'])->name('group.clear');

Route::delete('/schedules/delete-multiple', [ScheduleController::class, 'deleteMultiple'])->name('schedules.delete-multiple');
Route::get('/schedules/saved', [ScheduleController::class, 'getSavedSchedules'])->name('schedules.saved');
