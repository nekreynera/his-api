<?php

namespace App\Http\Controllers\Api;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DoctorAssignment;
use App\Models\Consultation;

class ConsultationController extends Controller
{
    public function finish(Request $request, $assignmentId)
    {
        $request->validate([
            'subjective' => 'nullable|string',
            'objective' => 'nullable|string',
            'assessment' => 'nullable|string',
            'plan' => 'nullable|string',
            'chief_complaint' => 'nullable|string',
            'icd_code_id' => 'nullable|exists:icd_codes,id',
        ]);

        DB::transaction(function () use ($request, $assignmentId) {

            $assignment = DoctorAssignment::with([
                'queue.triage'
            ])->findOrFail($assignmentId);

            Consultation::updateOrCreate(

                [
                    'doctor_assignment_id' => $assignment->id
                ],

                [
                    'chief_complaint' => $request->chief_complaint,
                    'subjective' => $request->subjective,
                    'objective' => $request->objective,
                    'assessment' => $request->assessment,
                    'plan' => $request->plan,
                    'icd_code_id' => $request->icd_code_id,
                    'status' => 'completed',
                    'consultation_completed_at' => now(),
                ]
            );

            // Doctor assignment
            $assignment->update([
                'status' => 'done',
                'completed_at' => now(),
            ]);

            // Clinic queue
            $assignment->queue->update([
                'status' => 'done',
                'completed_at' => now(),
            ]);

            // Triage
            $assignment->queue->triage->update([
                'finished' => 'Y',
            ]);

        });

        return response()->json([
            'success' => true,
            'message' => 'Consultation completed successfully.'
        ]);
    }
     public function show($id)
    {
        $assignment = DoctorAssignment::with([
            'doctor',
            'queue.triage.patient',
            'queue.triage.clinic',
            'queue.triage.vitalSign',
        ])->find($id);

        if (!$assignment) {
            return response()->json([
                'message' => 'Consultation not found.'
            ], 404);
        }

        return response()->json($assignment);
    }

      public function start($id)
    {
        $assignment = DoctorAssignment::findOrFail($id);

        // Prevent restarting an already completed consultation
        if ($assignment->status === 'done') {
            return response()->json([
                'message' => 'Consultation has already been completed.'
            ], 422);
        }

        // Update only if not already started
        if ($assignment->status !== 'in-progress') {
            $assignment->update([
                'status' => 'in-progress',
                'started_at' => now(),
            ]);
        }

        // Reload with all relationships needed by the consultation workspace
        $assignment->load([
            'doctor',
            'queue.triage.patient',
            'queue.triage.clinic',
            'queue.triage.vitalSign',
        ]);

        return response()->json([
            'message' => 'Consultation started.',
            'data' => $assignment,
        ]);
    }
}
