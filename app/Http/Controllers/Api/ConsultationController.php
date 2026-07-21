<?php

namespace App\Http\Controllers\Api;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DoctorAssignment;
use App\Models\Consultation;
use App\Models\Laboratory;
use App\Models\SoapTemplate;

use Barryvdh\DomPDF\Facade\Pdf;

class ConsultationController extends Controller
{

    /** View Consultation pdf */
    public function viewPdf($assignment)
    {  
           $consultation = Consultation::with([
            'assignment.queue.triage.patient',
            //'assignment.queue.triage.clinic',
            //'assignment.queue.triage.vitalSign',
            'assignment.doctor',
            'icd',
        ])->where('doctor_assignment_id', $assignment)
        ->firstOrFail();

        $pdf = Pdf::loadView('pdf.consultation', [
            'consultation' => $consultation,
        ]);

        return $pdf->stream('consultation.pdf');
    }

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
                    'consultation_no' => Consultation::where('doctor_assignment_id', $assignment->id)
                    ->value('consultation_no') ?? $this->generateConsultationNo(),

                    'doctor_id' => $assignment->doctor_id,

                    'clinic_id' => $assignment->queue->triage->clinic_id,
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

    /** Save consultaion as draft/paused */
    public function saveDraft(Request $request, $assignmentId)
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

            $assignment = DoctorAssignment::with('queue')
                ->findOrFail($assignmentId);

            Consultation::updateOrCreate(
                [
                    'doctor_assignment_id' => $assignment->id,
                ],
                [
                    'consultation_no' => Consultation::where('doctor_assignment_id', $assignment->id)
                    ->value('consultation_no') ?? $this->generateConsultationNo(),
                    'doctor_id' => $assignment->doctor_id,
                    'clinic_id' => $assignment->queue->triage->clinic_id,
                    'chief_complaint' => $request->chief_complaint,
                    'subjective' => $request->subjective,
                    'objective' => $request->objective,
                    'assessment' => $request->assessment,
                    'plan' => $request->plan,
                    'icd_code_id' => $request->icd_code_id,
                    'status' => 'draft',
                ]
            );

            $assignment->update([
                'status' => 'paused',
                'paused_at' => now(),
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Consultation draft saved.'
        ]);
    }

    /** Resume paused consultation */
    public function resume($assignmentId)
    {
        $assignment = DoctorAssignment::with([
            'queue.triage.patient',
            'queue.triage.vitalSign',
            'consultation.icd',
        ])->findOrFail($assignmentId);

        $assignment->update([
            'status' => 'in-progress',
            'resumed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'data' => $assignment,
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

         // Create consultation if it doesn't exist yet
        $consultation = Consultation::firstOrCreate(
            [
                'doctor_assignment_id' => $assignment->id,
            ],
            [
                'consultation_no' => $this->generateConsultationNo(),

                'doctor_id' => $assignment->doctor_id,

                'clinic_id' => $assignment->queue->triage->clinic_id,
                'status' => 'draft',
            ]
        );

        // Reload with all relationships needed by the consultation workspace
        $assignment->load([
            'doctor',
            'queue.triage.patient',
            'queue.triage.clinic',
            'queue.triage.vitalSign',
        ]);

        // Load Laboratory Master
       $laboratories = Laboratory::with([
            'categories.tests'
        ])
        ->orderBy('name')
        ->get();

         // SOAP Templates
        $soapTemplates = SoapTemplate::with(['clinic', 'icdCode'])
            ->where('doctor_id', auth()->id())
            ->where('active', 'Y')
            ->orderByRaw("CASE WHEN is_default = 'Y' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->get();

        return response()->json([
            'message' => 'Consultation started.',
            'data' => $assignment,
            'laboratories'=>$laboratories,
            'soaptemplates' => $soapTemplates,
            'consultation' => $consultation,
            
        ]);
    }

    /**Generate Consultation No */
    private function generateConsultationNo()
    {
        $date = now()->format('Ymd');

        $count = Consultation::whereDate('created_at', today())->count() + 1;

        return sprintf(
            'EVMC-OPD-%s-%04d',
            $date,
            $count
        );
    }

}
