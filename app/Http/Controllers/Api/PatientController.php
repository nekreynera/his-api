<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Patient;
use App\Models\Clinic;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 25);
        $search = $request->get('search');

        $query = Patient::query();

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('hospital_no', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('middle_name', 'like', "%{$search}%");
            });
        }
        //order by patient.id
        /*$patients = $query
            ->orderByDesc('id')
            ->paginate($perPage); */
        $patients = $query
        ->orderBy('last_name', 'asc')
        ->orderBy('first_name', 'asc')
        ->orderBy('middle_name', 'asc')
        ->paginate($perPage);

        return response()->json([
            'current_page' => $patients->currentPage(),
            'last_page' => $patients->lastPage(),
            'per_page' => $patients->perPage(),
            'total' => $patients->total(),

            'data' => $patients->getCollection()->map(function ($patient) {
                return $patient->toSearchResponse();
            }),
        ]);
    }
    //
     /**
     * SEARCH PATIENT (QR / BARCODE / HOSPITAL NO)
     */
    public function search($keyword)
    {
         // 1. PRIORITY: QR / barcode / hospital no (EXACT MATCH)
            $exact = Patient::where('barcode', $keyword)
                ->orWhere('hospital_no', $keyword)
                ->first();

            if ($exact) {
                return response()->json([
                    'type' => 'exact',
                    'patient' => $exact->toSearchResponse()
                ]);
            }

            // 2. SECONDARY: NAME SEARCH (MAY RETURN MULTIPLE)
            $patients = Patient::where('last_name', 'like', "%$keyword%")
                ->orWhere('first_name', 'like', "%$keyword%")
                ->orWhere('middle_name', 'like', "%$keyword%")
                ->orderBy('last_name','asc')
                ->limit(10)
                ->get();

            if ($patients->count() > 0) {
                return response()->json([
                    'type' => 'multiple',
                    'results' => $patients->map->toSearchResponse()
                ]);
            }

            return response()->json([
                'type' => 'not_found',
                'message' => 'No patient found'
            ], 404);
    }

    public function show($id)
    {
        $patient = Patient::find($id);
         // GET CLINICS (TYPE = 'c')
        $clinics = Clinic::where('type', 'c')->where('department','c')
            ->orderBy('name')
            ->get();

       

        if (!$patient) {
            return response()->json([
                'message' => 'Patient not found'
            ], 404);
        }

        return response()->json([
            'patient' => $patient->toSearchResponse(),
            'clinics' => $clinics
        ]);
    }
}
