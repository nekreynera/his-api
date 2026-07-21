<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Clinic;
use App\Models\IcdCode;
use App\Models\SoapTemplate;

class SettingsController extends Controller
{
    //
   public function index(Request $request)
    {
            //$clinics = Clinic::where('type', 'c')->where('department','c')
        return response()->json([
            'clinics' => Clinic::where('type', 'c')->orderBy('name')->get(),

            'templates' => SoapTemplate::with(['clinic','icdCode'])
                ->where('doctor_id', auth()->id())
                ->where('active', 'Y')
                ->orderBy('is_default','ASC')
                ->orderBy('name')
                ->get(),
            // 'icd_codes' => IcdCode::orderBy('code')
            // ->get([
            //     'id',
            //     'code',
            //     'description',
            //     'case_type'
            // ])
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:150',
            'description'=>'required',
            'clinic_id' => 'nullable|exists:clinics,id',
            'chief_complaint' => 'nullable|string',
            'subjective' => 'nullable|string',
            'objective' => 'nullable|string',
            'assessment' => 'nullable|string',
            'plan' => 'nullable|string',
            'icd_code_id' => 'nullable|exists:icd_codes,id',
            'is_default' => 'required|in:Y,N',
        ]);

        if ($request->is_default === 'Y') {

                SoapTemplate::where('doctor_id', auth()->id())
                    ->update([
                        'is_default' => 'N'
                    ]);
            }

        $template = SoapTemplate::create([
            'doctor_id' => auth()->id(),
            'clinic_id' => $request->clinic_id ?: null,
            'name' => $request->name,
            'description' => $request->description,
            'chief_complaint' => $request->chief_complaint,
            'subjective' => $request->subjective,
            'objective' => $request->objective,
            'assessment' => $request->assessment,
            'plan' => $request->plan,
            'icd_code_id' => $request->icd_code_id,
            'is_default' => $request->is_default,
            'active' => 'Y',
        ]);

        return response()->json([
            'message' => 'SOAP template saved successfully.',
            'template' => $template
        ], 201);
    }

    public function update(Request $request, SoapTemplate $template)
    {
        // Ensure the logged-in doctor owns this template
        if ($template->doctor_id != auth()->id()) {
            return response()->json([
                'message' => 'Unauthorized.'
            ], 403);
        }

        $request->validate([
            'name' => 'required|max:150',
            'clinic_id' => 'nullable|exists:clinics,id',
            'chief_complaint' => 'nullable|string',
            'subjective' => 'nullable|string',
            'objective' => 'nullable|string',
            'assessment' => 'nullable|string',
            'plan' => 'nullable|string',
            'icd_code_id' => 'nullable|exists:icd_codes,id',
            'is_default' => 'required|in:Y,N',
        ]);

        // Clear previous default for the same doctor & clinic
        if ($request->is_default === 'Y') {

           SoapTemplate::where('doctor_id', auth()->id())
                ->where('id', '!=', $template->id)
                ->update([
                    'is_default' => 'N'
                ]);
        }

        $template->update([
            'clinic_id' => $request->clinic_id ?: null,
            'name' => $request->name,
            'description' => $request->description,
            'chief_complaint' => $request->chief_complaint,
            'subjective' => $request->subjective,
            'objective' => $request->objective,
            'assessment' => $request->assessment,
            'plan' => $request->plan,
            'icd_code_id' => $request->icd_code_id,
            'is_default' => $request->is_default,
        ]);

        return response()->json([
            'message' => 'SOAP template updated successfully.',
            'template' => $template
        ]);
    }
}
