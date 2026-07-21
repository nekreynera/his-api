<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Consultation;
use App\Models\LaboratoryRequest;
use App\Models\LaboratoryRequestItem;
use Illuminate\Support\Facades\DB;

class LaboratoryRequestController extends Controller
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

            'consultation_id' => 'required|exists:consultations,id',

            'priority' => 'required|in:Routine,Urgent,STAT',

            'remarks' => 'nullable|string',

            'tests' => 'required|array|min:1',

            'tests.*' => 'exists:laboratory_sub_list,id',

        ]);

        DB::transaction(function () use ($request) {

            $consultation = Consultation::findOrFail(
                $request->consultation_id
            );

            $requestNo = $this->generateRequestNo();

            $labRequest = LaboratoryRequest::create([

                'consultation_id' => $consultation->id,

                'request_no' => $requestNo,

                'priority' => $request->priority,

                'remarks' => $request->remarks,

            ]);

            foreach ($request->tests as $testId) {

                LaboratoryRequestItem::create([

                    'laboratory_request_id' => $labRequest->id,

                    'laboratory_sub_list_id' => $testId,

                ]);

            }

        });

        return response()->json([

            'success' => true,

            'message' => 'Laboratory request submitted.'

        ]);
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

    //Generate Request No.
     private function generateRequestNo()
    {

        $date = now()->format('Ymd');

        $series = LaboratoryRequest::whereDate(
            'created_at',
            today()
        )->count() + 1;

        return sprintf(
            'LAB-%s-%04d',
            $date,
            $series
        );

    }
}
