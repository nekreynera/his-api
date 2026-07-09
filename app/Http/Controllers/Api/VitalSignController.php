<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\ClinicQueue;
use App\Models\TriageVitalSign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VitalSignController extends Controller
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
         $validated = $request->validate([
            'clinic_queue_id'    => 'required|exists:clinic_queue,id',
            'chief_complaint'    => 'nullable|string|max:1000',
            'systolic_bp'        => 'nullable|integer',
            'diastolic_bp'       => 'nullable|integer',
            'pulse_rate'         => 'nullable|integer',
            'respiratory_rate'   => 'nullable|integer',
            'temperature'        => 'nullable|numeric',
            'oxygen_saturation'  => 'nullable|integer|min:0|max:100',
            'weight'             => 'nullable|numeric',
            'height'             => 'nullable|numeric',
            'bmi'                => 'nullable|numeric',
        ]);

        return DB::transaction(function () use ($validated, $request) {

            $queue = ClinicQueue::with('triage')->findOrFail($validated['clinic_queue_id']);

            // Prevent duplicate vital signs
            if (TriageVitalSign::where('triage_id', $queue->triage_id)->exists()) {
                return response()->json([
                    'message' => 'Vital signs have already been recorded for this patient.'
                ], 409);
            }

            $vitalSign = TriageVitalSign::create([
                'triage_id'          => $queue->triage_id,
                'chief_complaint'    => $validated['chief_complaint'] ?? null,
                'systolic_bp'        => $validated['systolic_bp'] ?? null,
                'diastolic_bp'       => $validated['diastolic_bp'] ?? null,
                'pulse_rate'         => $validated['pulse_rate'] ?? null,
                'respiratory_rate'   => $validated['respiratory_rate'] ?? null,
                'temperature'        => $validated['temperature'] ?? null,
                'oxygen_saturation'  => $validated['oxygen_saturation'] ?? null,
                'weight'             => $validated['weight'] ?? null,
                'height'             => $validated['height'] ?? null,
                'bmi'                => $validated['bmi'] ?? null,
                'taken_by'           => $request->user()->id,
            ]);

            return response()->json([
                'message' => 'Vital signs saved successfully.',
                'data' => $vitalSign->load('user', 'triage.patient'),
            ], 201);
        });
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
