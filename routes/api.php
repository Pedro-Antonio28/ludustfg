<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\DirectorAuthController;
use App\Http\Controllers\Api\Auth\StudentAuthController;
use App\Http\Controllers\Api\Auth\TeacherAuthController;
use App\Http\Controllers\Api\Director\ClassesController as DClassesController;
use App\Http\Controllers\Api\Director\DirectorProfileController;
use App\Http\Controllers\Api\Student\ClassesController as SClassesController;
use App\Http\Controllers\Api\Student\StudentProfileController;
use App\Http\Controllers\Api\Student\TestController;
use App\Http\Controllers\Api\Teacher\ClassesController as TClassesController;
use App\Http\Controllers\Api\Teacher\ClassMemberController;
use App\Http\Controllers\Api\Teacher\QuestionController;
use App\Http\Controllers\Api\Teacher\TeacherProfileController;
use App\Http\Controllers\Api\Teacher\TestTeacherController;
use App\Http\Middleware\EnsureRoleGuard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/student/register', [StudentAuthController::class, 'register']);
Route::post('/student/login', [StudentAuthController::class, 'login']);

Route::post('/teacher/register', [TeacherAuthController::class, 'register']);
Route::post('/teacher/login', [TeacherAuthController::class, 'login']);

Route::post('/director/register', [DirectorAuthController::class, 'register']);
Route::post('/director/login', [DirectorAuthController::class, 'login']);

Route::post('/logout', [AuthController::class, 'logout']);

Route::prefix('student')->middleware(['auth:sanctum', EnsureRoleGuard::class . ':student'])->group(function () {
    Route::get('/dashboard', [SClassesController::class, 'index']);
    Route::post('/join-class', [SClassesController::class, 'joinClass']);
    Route::get('/class/{classId}/activities', [SClassesController::class, 'activities']);
    Route::get('/class/{classId}/results', [SClassesController::class, 'results']);
    Route::get('/class/{classId}/members', [ClassMemberController::class, 'index']);
    Route::get('/class/{classId}/exam/{testId}', [TestController::class, 'edit']);
    Route::post('/class/{classId}/exam/{testId}/submit', [TestController::class, 'update']);
    Route::get('/profile', [StudentProfileController::class, 'show']);
    Route::put('/profile', [StudentProfileController::class, 'update']);
});

Route::prefix('teacher')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/dashboard', [TClassesController::class, 'index']);
    Route::get('/bank-questions', [QuestionController::class, 'indexBank']);
    Route::post('/question', [QuestionController::class, 'store']);
    Route::put('/question/{questionId}', [QuestionController::class, 'update']);
    Route::delete('/question/{questionId}', [QuestionController::class, 'destroy']);
    Route::post('/classes', [TClassesController::class, 'store']);
    Route::get('/class/{classId}/join-code', [TClassesController::class, 'generateJoinCode']);
    Route::get('/class/{classId}/activities', [TClassesController::class, 'activities']);
    Route::get('/class/{classId}/results', [TClassesController::class, 'results']);
    Route::post('/test', [TestTeacherController::class, 'store']);
    Route::post('/question/assign-test', [QuestionController::class, 'assignToTest']);
    Route::get('/test/{testId}', [TestTeacherController::class, 'show']);
    Route::put('/test/{testId}', [TestTeacherController::class, 'update']);
    Route::get('/class/{classId}/members', [ClassMemberController::class, 'index']);
    Route::delete('/class/{classId}/member/{studentId}', [ClassMemberController::class, 'destroy']);
    Route::get('/profile', [TeacherProfileController::class, 'show']);
    Route::get('/tests', [TeacherProfileController::class, 'index']);
    Route::put('/profile', [TeacherProfileController::class, 'update']);
    Route::get('/test/{testId}/submissions', [TestTeacherController::class, 'submissions']);
    Route::get('/test/{testId}/submissions/{studentId}', [TestTeacherController::class, 'submissionDetail']);
    Route::post('/test/{test}/submissions/{student}/grade', [TestTeacherController::class, 'gradeSubmission']);
});

Route::prefix('director')->middleware(['auth:sanctum', EnsureRoleGuard::class . ':director'])->group(function () {
    Route::get('/dashboard', [DClassesController::class, 'index']);
    Route::get('/profile', [DirectorProfileController::class, 'show']);
    Route::put('/profile', [DirectorProfileController::class, 'update']);
});

Route::middleware('auth:sanctum')->get('/student/classes/{id}/tests', [TestController::class, 'index']);
