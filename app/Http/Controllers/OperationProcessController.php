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
        $companies = Company::all();
        $companies_arr = [];
        foreach($companies as $company) {
            $companies_arr[$company->id] = $company->name;
        }

        return view('operation-processes.create')->with([
            'companies' => $companies_arr
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreOperationProcessRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreOperationProcessRequest $request)
    {
        $operation_process = new OperationProcess([
            'company_id' => $request->company_id,
            'operation_process' => $request->operation_process
        ]);
        $operation_process->save();

        $number = 0;
        foreach($request->description as $key => $description) {
            $number++;
            $activity = new Activity([
                'number' => $number,
                'operation_process_id' => $operation_process->id,
                'description' => $request->description[$key],
                'remarks' => $request->remarks[$key],
            ]);
            $activity->save();
        }

        // logs
        activity('create')
        ->performedOn($operation_process)
        ->log(':causer.firstname :causer.lastname has created operation process :subject.operation_process');

        return redirect()->route('operation-process.index')->with([
            'message_success' => 'Operation process was created'
        ]);
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
    public function edit($id)
    {
        $operation_process = OperationProcess::findOrFail($id);

        $companies = Company::all();
        $companies_arr = [];
        foreach($companies as $company) {
            $companies_arr[$company->id] = $company->name;
        }

        return view('operation-processes.edit')->with([
            'companies' => $companies_arr,
            'operation_process' => $operation_process
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateOperationProcessRequest  $request
     * @param  \App\Models\OperationProcess  $operationProcess
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateOperationProcessRequest $request, $id)
    {
        $operation_process = OperationProcess::findOrFail($id);

        $changes_arr['old'] = $operation_process->getOriginal();

        $operation_process->update([
            'company_id' => $request->company_id,
            'operation_process' => $request->operation_process
        ]);
        
        $operation_process->activities()->forceDelete();
        $number = 0;
        foreach($request->description as $key => $description) {
            $number++;
            $activity = new Activity([
                'number' => $number,
                'operation_process_id' => $operation_process->id,
                'description' => $request->description[$key],
                'remarks' => $request->remarks[$key],
            ]);
            $activity->save();
        }

        $changes_arr['changes'] = $operation_process->getChanges();

        // logs
        activity('update')
        ->performedOn($operation_process)
        ->withProperties($changes_arr)
        ->log(':causer.firstname :causer.lastname has updated operation process :subject.operation_process .');

        return back()->with([
            'message_success' => 'Operation process was updated.'
        ]);

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
                    'number' => $row[2],
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
