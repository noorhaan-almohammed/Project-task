<?php

use App\Models\Task;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\Auth\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Routes accessible without authentication
Route::post('login', [AuthController::class, 'login']);

Route::middleware(['auth:api'])->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('profile', [AuthController::class, 'profile']);

    Route::GET('user/tasks', [UserController::class, 'userTasks']);
    Route::GET('users/tasks/latest', [UserController::class, 'getLatestestTask']);
    Route::GET('users/tasks/oldest', [UserController::class, 'getOldestTask']);
    Route::GET('users/tasks/important', [UserController::class, 'getImportantTask']);

    Route::GET('project/taskProject', [ProjectController::class, 'getTasksWithFilter']);

    Route::GET('task', [TaskController::class, 'index']);
    Route::GET('task/{task}', [TaskController::class, 'show']);
});
Route::middleware(['auth:api', 'admin'])->group(function () {
    Route::POST('project', [ProjectController::class, 'store']);
    Route::POST('project/assignMnager/{project}', [ProjectController::class, 'assignMnager']);
    Route::PUT('project/update/{project}', [ProjectController::class, 'update']);
    Route::DELETE('project/delete/{project}', [ProjectController::class, 'destroy']);

    Route::get('users', [UserController::class, 'index']);
    Route::get('users/{id}', [UserController::class, 'show']);
    Route::post('users', [UserController::class, 'store']);
    Route::put('users/{user}', [UserController::class, 'update']);
    Route::delete('users/{user}', [UserController::class, 'destroy']);
});
Route::middleware(['auth:api', 'check.role:Manager'])->group(function () {
        Route::GET('project/{project}', [ProjectController::class, 'show']);
    Route::POST('project/assignManyUsers/{project}', [ProjectController::class, 'assignManyUsersToProject']);
    Route::POST('project/assignUsers/{project}', [ProjectController::class, 'assignUsersToProject']);
    Route::delete('/project/{project}/detach', [ProjectController::class, 'detachProject']);

    Route::GET('user/project/allTasks', [UserController::class, 'tasksOfUserProjects']);
    Route::post('task', [TaskController::class, 'store']);
    Route::PATCH('task/{taskid}/assign', [TaskController::class, 'assignTask']);
    Route::PATCH('task/{taskid}/unAssign', [TaskController::class, 'unAssignTask']);
    Route::put('task/{task}', [TaskController::class, 'update']);
    Route::delete('task/{task}', [TaskController::class, 'destroy']);

});

Route::middleware(['auth:api', 'check.role:Developer'])->group(function () {

    Route::PATCH('task/{taskId}/status', [TaskController::class, 'updateStatusTask']);
});

Route::middleware(['auth:api', 'check.role:Tester'])->group(function () {

    Route::PATCH('task/{taskId}/tester/status', [TaskController::class, 'updateStatusTaskTester']);

});
