<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ClinicQueue;
use App\Models\Triage;
use Illuminate\Support\Facades\DB;

class ClinicQueueController extends Controller
{
    public function triageList($clinicId)
    {
        
        $data = Triage::with(['vitalSign','patient', 'clinic', 'queue.assignment'])
            ->where('clinic_id', $clinicId)
            ->where('finished', 'N')
            ->whereDate('created_at', today())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($data);
    }

    public function queue($clinicId)
    {
        return ClinicQueue::with([
            'triage.patient'
        ])
        ->whereHas('triage', function ($q) use ($clinicId) {
            $q->where('clinic_id', $clinicId);
        })
        ->where('status', 'waiting')
        ->orderBy('queue_number')
        ->get();
    }
    
    public function arrive(Request $request)
    {
        $request->validate([
            'triage_id' => 'required|integer|exists:triage,id'
        ]);

        return DB::transaction(function () use ($request) {

            // 1. Get triage record
            $triage = Triage::with('clinic', 'patient')
                ->findOrFail($request->triage_id);

            // 2. Prevent duplicate queue entry
            $existing = ClinicQueue::where('triage_id', $triage->id)->first();

            if ($existing) {
                return response()->json([
                    'message' => 'Patient already in queue',
                    'data' => $existing
                ], 409);
            }

            // 3. Generate queue number per clinic
            $lastQueueNumber = ClinicQueue::whereHas('triage', function ($q) use ($triage) {
                    $q->where('clinic_id', $triage->clinic_id);
                })
                ->max('queue_number');

            $nextQueueNumber = $lastQueueNumber ? $lastQueueNumber + 1 : 1;

            // 4. Create queue entry
            $queue = ClinicQueue::create([
                'triage_id' => $triage->id,
                'queue_number' => $nextQueueNumber,
                'status' => 'waiting',
            ]);

            // 5. Return full data for UI
            return response()->json([
                'message' => 'Patient added to queue successfully',
                'queue' => $queue->load('assignment','triage.patient', 'triage.clinic')
            ]);
        });
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
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
