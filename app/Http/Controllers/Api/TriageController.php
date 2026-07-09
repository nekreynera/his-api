<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Triage;
use Illuminate\Support\Facades\Auth;

class TriageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
         $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'clinic_id'  => 'required|exists:clinics,id',
        ]);

        // Prevent duplicate active triage
        $existing = Triage::where('patients_id', $request->patient_id)
            ->where('finished', 'N')
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Patient already has an active clinic assignment.'
            ], 422);
        }

        $triage = Triage::create([
            'patients_id' => $request->patient_id,
            'clinic_id'   => $request->clinic_id,
            'users_id'    => Auth::id(), // logged-in triage personnel
            'finished'    => 'N',
        ]);

        return response()->json([
            'message' => 'Patient assigned successfully.',
            'data'    => $triage
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
