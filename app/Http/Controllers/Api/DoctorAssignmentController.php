<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\User;

use App\Models\ClinicQueue;
use App\Models\DoctorAssignment;
use Illuminate\Support\Facades\DB;



class DoctorAssignmentController extends Controller
{
    /**
     * Display patient per doc
     */
    public function queue(Request $request)
    {
         $doctor = $request->user();

        $queue = DoctorAssignment::with([
            'queue.triage.patient',
            'queue.triage.clinic',
            'queue.triage.vitalSign',
            'consultation.laboratoryRequests.items.laboratory',
        ])
        ->where('doctor_id', $doctor->id)
        ->whereIn('status', ['assigned', 'in-progress','paused','done'])
        ->whereDate('created_at', today())
        ->orderBy('doctor_queue_no')
        ->get()
        ->map(function ($item) {
             if ($patient = $item->queue?->triage?->patient) {
                $patient->age = $patient->computed_age;
            }

            $item->status_label = match ($item->status) {
                'assigned'     => 'Waiting',
                'in-progress'  => 'In progress',
                'paused'       => 'Paused',
                'done'         => 'Completed',
                default        => ucfirst($item->status),
            };

            return $item;
        });

        //now serving
        // Current consultation (if any)
        $current = DoctorAssignment::where('doctor_id', $doctor->id)
        ->where('status', 'in-progress')
        ->whereDate('created_at', today())
        ->first();

        $completedToday = DoctorAssignment::where('doctor_id', $doctor->id)
            ->where('status', 'done')
            ->whereDate('updated_at', today())
            ->count();
        $pausedToday = DoctorAssignment::where('doctor_id', $doctor->id)
            ->where('status', 'paused')
            ->whereDate('updated_at', today())
            ->count();

        return response()->json([
            'queue' => $queue,
            'completed_today' => $completedToday,
            'paused_today'=>$pausedToday,
            'now_serving' => $current?->doctor_queue_no,
        ]);
    }
     /**
     * Display all online doctors assigned to the secretary's clinic.
     */
    public function online(Request $request)
    {
        $secretary = $request->user();

        $doctors = User::select(
        'id',
        'name',
        'department',
        'clinic_id',
        'last_login_at'
    )
    ->where('role', '7')
    ->where('clinic_id', $secretary->clinic_id)
    ->whereHas('tokens', function ($query) {
        $query->where('last_used_at', '>=', Carbon::now()->subMinutes(15));
    })
    ->withCount([
        'doctorAssignments as waiting_patients' => function ($query) {
            $query->whereIn('status', ['assigned', 'in-progress'])
             ->whereDate('assigned_at', today());
        }
    ])
    ->orderBy('name')
    ->get();

            
         return response()->json([
                'user' => $secretary,
                'doctors' => $doctors,
            ]);
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
        $request->validate([
            'clinic_queue_id' => 'required|exists:clinic_queue,id',
            'doctor_id'       => 'required|exists:users,id',
        ]);

        DB::beginTransaction();

        try {

            // Prevent duplicate assignment
            $exists = DoctorAssignment::where(
                'clinic_queue_id',
                $request->clinic_queue_id
            )->exists();

            if ($exists) {
                return response()->json([
                    'message' => 'This patient is already assigned to a doctor.'
                ], 422);
            }

            // Get next queue number for this doctor
            $nextQueueNo = DoctorAssignment::where('doctor_id', $request->doctor_id)
                ->max('doctor_queue_no');

            $nextQueueNo = $nextQueueNo ? $nextQueueNo + 1 : 1;

            // Save assignment
            $assignment = DoctorAssignment::create([
                'clinic_queue_id' => $request->clinic_queue_id,
                'doctor_id'       => $request->doctor_id,
                'doctor_queue_no' => $nextQueueNo,
                'status'          => 'assigned',
                'assigned_at'     => now(),
            ]);

            // Update clinic queue status
            ClinicQueue::where('id', $request->clinic_queue_id)
                ->update([
                    'status' => 'assigned'
                ]);

            DB::commit();

            return response()->json([
                'message' => 'Patient successfully assigned.',
                'assignment' => $assignment
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'message' => 'Assignment failed.',
                'error'   => $e->getMessage()
            ], 500);
        }
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
