<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Api\ClinicQueueController;
use App\Http\Controllers\Api\DoctorAssignmentController;
use App\Http\Controllers\Api\ConsultationController;
use App\Http\Controllers\Api\TriageController;
use App\Http\Controllers\Api\IcdCodeController;
use App\Http\Controllers\Api\VitalSignController;

use LdapRecord\Container;
use Illuminate\Http\Request;

Route::get('/test', function () {
    
    return response()->json([
        'message' => 'API is working'
    ]);
});

Route::post('/login', [AuthController::class, 'login']);



Route::middleware('auth:sanctum')->group(function () {

    Route::get('/me', function (Request $request) {
        return response()->json($request->user());
    });

    /**Search patient */
    Route::get('/patients/search/{keyword}', [PatientController::class, 'search']);

    /**Show patient profile */
    Route::get('/patients/{id}', [PatientController::class, 'show']);
    
    Route::get('/patients', [PatientController::class, 'index']);

    Route::get('/patients/search/{keyword}', [PatientController::class, 'search']);

    
    Route::post('/clinic/queue/arrive', [ClinicQueueController::class, 'arrive']);

    Route::get('/clinic/{clinicId}/triage', [ClinicQueueController::class, 'triageList']);

    Route::get('/clinic/{clinicId}/queue', [ClinicQueueController::class, 'queue']);
    
    Route::get('/doctor-assignment', [DoctorAssignmentController::class, 'online']);

    Route::post('/doctor-assignment', [DoctorAssignmentController::class, 'store']);

    Route::get('/consultation-queue', [DoctorAssignmentController::class, 'queue']);

    Route::get('/consultation/{id}', [ConsultationController::class, 'show']);

    Route::post('/consultation/{id}/start', [ConsultationController::class, 'start']);
    
    Route::get('/icd-codes', [IcdCodeController::class, 'index']);

    Route::post('/consultation/{assignment}/finish',[ConsultationController::class, 'finish']);
    
    Route::post('/triage', [TriageController::class, 'store']);

    Route::post('/vital-signs', [VitalSignController::class, 'store']);

    Route::post('/consultation/{assignment}/draft', [ConsultationController::class, 'saveDraft']);

    Route::post('/consultation/{assignment}/resume', [ConsultationController::class, 'resume']);
});

 


