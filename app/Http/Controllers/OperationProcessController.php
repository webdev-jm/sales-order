<?php

namespace App\Http\Controllers;

use App\Models\OperationProcess;
use App\Models\Activity;
use App\Models\Company;
use App\Http\Requests\StoreOperationProcessRequest;
use App\Http\Requests\UpdateOperationProcessRequest;
use Illuminate\Http\Request;

use App\Http\Traits\GlobalTrait;

use Maatwebsite\Excel\Facades\Excel;
use App\Imports\OperationProcessImport;

class OperationProcessController extends Controller
{

    use GlobalTrait;

    public $settings;

    public function __construct() {
        $this->settings = $this->getSettings();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $search = trim($request->get('search'));
        $operation_processes = OperationProcess::orderBy('id', 'DESC')->paginate($this->settings->data_per_page);

        return view('operation-processes.index')->with([
            'search' => $search,
            'operation_processes' => $operation_processes
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreOperationProcessRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreOperationProcessRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\OperationProcess  $operationProcess
     * @return \Illuminate\Http\Response
     */
    public function show(OperationProcess $operationProcess)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\OperationProcess  $operationProcess
     * @return \Illuminate\Http\Response
     */
    public function edit(OperationProcess $operationProcess)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateOperationProcessRequest  $request
     * @param  \App\Models\OperationProcess  $operationProcess
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateOperationProcessRequest $request, OperationProcess $operationProcess)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\OperationProcess  $operationProcess
     * @return \Illuminate\Http\Response
     */
    public function destroy(OperationProcess $operationProcess)
    {
        //
    }

    public function upload(Request $request) {
        $request->validate([
            'upload_file' => [
                'mimes:xlsx'
            ]
        ]);

        $imports = Excel::toArray(new OperationProcessImport, $request->upload_file);
        foreach($imports[0] as $row) {

            $company = Company::where('name', $row[0])->first();
            
            // check
            $operation_process = OperationProcess::where('company_id', $company->id)
            ->where('operation_process', $row[1])->first();
            if(empty($operation_process)) {
                $operation_process = new OperationProcess([
                    'company_id' => $company->id,
                    'operation_process' => $row[1]
                ]);
                $operation_process->save();
            }

            if(!empty($row[2])) {
                $activity = new Activity([
                    'operation_process_id' => $operation_process->id,
                    'description' => $row[3],
                    'remarks' => $row[4],
                ]);
                $activity->save();
            }

        }

       // logs
       activity('upload')
       ->log(':causer.firstname :causer.lastname has uploaded operation processes');

       return back()->with([
           'message_success' => 'Operation processes has been uploaded.'
       ]);
    }
}
