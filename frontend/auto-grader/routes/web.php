<?php

use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\ErrorController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SubmissionController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/login/google/redirect', [AuthController::class, 'redirect'])->name('redirect');
Route::get('/login/google/callback', [AuthController::class, 'callback'])->name('callback');

Route::group(['middleware' => 'auth.token'], function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');

    Route::post('/classes/store', [ClassController::class, 'store'])->name('classes.store');
    Route::post('/classes/join', [ClassController::class, 'join'])->name('classes.join');

    Route::post("/classes/{classCode}", [ClassController::class, "addMember"])->name("classes.addMember");
    Route::put("/classes/{classId}", [ClassController::class, "update"])->name("classes.update");
    Route::delete("/classes/{classId}", [ClassController::class, "destroy"])->name("classes.destroy");
    Route::delete("/classes/{classId}/members/{userId}", [ClassController::class, "removeMember"])->name("classes.removeMember");
    Route::delete("/classes/{classId}/leave", [ClassController::class, "leave"])->name("classes.leave");

    Route::get("/classes/{classId}/assignments", [ClassController::class, "assignment"])->name("classes.assignments");
    Route::get("/classes/{classId}/members", [ClassController::class, "member"])->name("classes.member");
    Route::get("/classes/{classId}/settings", [ClassController::class, "setting"])->name("classes.setting");

    Route::get("/classes/{classId}/assignments/create", [AssignmentController::class, "create"])->name("assignments.create");
    Route::get("/classes/{classId}/assignments/{assignmentId}/show", [AssignmentController::class, "show"])->name("assignments.show");
    Route::get("/classes/{classId}/assignments/{assignmentId}/description", [AssignmentController::class, "assignmentDescription"])->name("assignments.description");
    Route::get("/classes/{classId}/assignments/{assignmentId}/edit", [AssignmentController::class, "edit"])->name("assignments.edit");
    Route::put("/classes/{classId}/assignments/{assignmentId}/update", [AssignmentController::class, "update"])->name("assignments.update");
    Route::delete("/classes/{classId}/assignments/{assignmentId}/destroy", [AssignmentController::class, "destroy"])->name("assignments.destroy");
    Route::post("/classes/{classId}/assignments/store", [AssignmentController::class, "store"])->name("assignments.store");
    Route::get("/classes/{classId}/assignments/{assignmentId}/csv", [AssignmentController::class, "csv"])->name("assignments.csv");
    Route::get("/classes/{classId}/assignments/{assignmentId}/leaderboard", [AssignmentController::class, "leaderboard"])->name("assignments.leaderboard");

    Route::get("/classes/{classId}/assignments/{assignmentId}/students/{studentId}/submissions", [SubmissionController::class, "index"])->name("submissions.index");
    Route::post("/classes/{classId}/assignments/{assignmentId}/submissions", [SubmissionController::class, "store"])->name("submissions.store");
    Route::get("/logs/{logFile}", [SubmissionController::class, "logs"])->name("submissions.logs");

    Route::get("/assignments/teachers/templates/download", [AssignmentController::class, "downloadTemplateJavaAssignment"])->name("assignments.downloadTemplateJavaAssignment");
    Route::get("/assignments/students/templates/download", [AssignmentController::class, "downloadTemplateJavaStudent"])->name("assignments.downloadTemplateJavaStudent");
    Route::get("/error", [ErrorController::class, "index"])->name("error");
});
