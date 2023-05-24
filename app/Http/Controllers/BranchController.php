<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Account;
use App\Models\Region;
use App\Models\Classification;
use App\Models\Area;
use App\Models\BranchUpload;
use App\Http\Requests\StoreBranchRequest;
use App\Http\Requests\UpdateBranchRequest;
use Illuminate\Http\Request;

use Maatwebsite\Excel\Facades\Excel;
use App\Imports\BranchImport;
use App\Exports\BranchExport;
use App\Imports\BranchUploadImport;

use App\Http\Traits\GlobalTrait;

ini_set('memory_limit', '-1');
ini_set('max_execution_time', 0);
ini_set('sqlsrv.ClientBufferMaxKBSize','1000000'); // Setting to 512M
ini_set('pdo_sqlsrv.client_buffer_max_kb_size','1000000');

class BranchController extends Controller
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

        if(auth()->user()->hasRole('superadmin') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('sales') || auth()->user()->hasRole('finance')) {
            $branches = Branch::BranchSearch($search, $this->settings->data_per_page);
        } else {
            $branches = Branch::RestrictedBranchSearch($search, $this->settings->data_per_page);
        }
        return view('branches.index')->with([
            'search' => $search,
            'branches' => $branches
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $regions = Region::orderBy('region_name', 'ASC')->get();
        $regions_arr = [];
        foreach($regions as $region) {
            $regions_arr[$region->id] = $region['region_name'];
        }

        $classifications = Classification::orderBy('classification_name', 'ASC')->get();
        $classifications_arr = [];
        foreach($classifications as $classification) {
            $classifications_arr[$classification->id] = '['.$classification->classification_code.'] '.$classification->classification_name;
        }

        $areas = Area::orderBy('area_name', 'ASC')->get();
        $areas_arr = [];
        foreach($areas as $area) {
            $areas_arr[$area->id] = '['.$area->area_code.'] '.$area->area_name;
        }

        return view('branches.create')->with([
            'regions' => $regions_arr,
            'classifications' => $classifications_arr,
            'areas' => $areas_arr
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreBranchRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreBranchRequest $request)
    {
        $branch = new Branch([
            'account_id' => $request->account_id,
            'region_id' => $request->region_id,
            'classification_id' => $request->classification_id,
            'area_id' => $request->area_id,
            'branch_code' => $request->branch_code,
            'branch_name' => $request->branch_name,
        ]);
        $branch->save();

        // logs
        activity('create')
        ->performedOn($branch)
        ->log(':causer.firstname :causer.lastname has created branch [ :subject.branch_code ] :subject.branch_name');

        return redirect()->route('branch.index')->with([
            'message_success' => 'Branch '.$branch->branch_code.' was created.'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Branch  $branch
     * @return \Illuminate\Http\Response
     */
    public function show(Branch $branch)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Branch  $branch
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $branch = Branch::findOrFail($id);

        $regions = Region::orderBy('region_name', 'ASC')->get();
        $regions_arr = [];
        foreach($regions as $region) {
            $regions_arr[$region->id] = $region['region_name'];
        }

        $classifications = Classification::orderBy('classification_name', 'ASC')->get();
        $classifications_arr = [];
        foreach($classifications as $classification) {
            $classifications_arr[$classification->id] = '['.$classification->classification_code.'] '.$classification->classification_name;
        }

        $areas = Area::orderBy('area_name', 'ASC')->get();
        $areas_arr = [];
        foreach($areas as $area) {
            $areas_arr[$area->id] = '['.$area->area_code.'] '.$area->area_name;
        }

        return view('branches.edit')->with([
            'branch' => $branch,
            'regions' => $regions_arr,
            'classifications' => $classifications_arr,
            'areas' => $areas_arr
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateBranchRequest  $request
     * @param  \App\Models\Branch  $branch
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateBranchRequest $request, $id)
    {
        $branch = Branch::findOrFail($id);

        $changes_arr['old'] = $branch->getOriginal();

        $branch->update([
            'account_id' => $request->account_id,
            'region_id' => $request->region_id,
            'classification_id' => $request->classification_id,
            'area_id' => $request->area_id,
            'branch_code' => $request->branch_code,
            'branch_name' => $request->branch_name,
        ]);

        $changes_arr['changes'] = $branch->getChanges();

        // logs
        activity('update')
        ->performedOn($branch)
        ->withProperties($changes_arr)
        ->log(':causer.firstname :causer.lastname has updated branch :subject.branch_code .');

        return back()->with([
            'message_success' => 'Branch '.$branch->branch_code.' was updated.'
        ]);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Branch  $branch
     * @return \Illuminate\Http\Response
     */
    public function destroy(Branch $branch)
    {
        //
    }

    public function upload(Request $request) {
        $request->validate([
            'upload_file' => [
                'mimes:xlsx'
            ]
        ]);

        Excel::import(new BranchUploadImport, $request->upload_file);

        // logs
        activity('upload')
        ->log(':causer.firstname :causer.lastname has uploaded branches');

        return back()->with([
            'message_success' => 'Branches has been uploaded.'
        ]);
    }

    public function mergeUploads() {
        BranchUpload::whereNull('status')->chunk(500, function($data) {
            foreach($data as $row) {
                $account = Account::where('account_code', $row['account_code'])->first();

                $region = Region::where('region_name', $row['region'])->first();
                if(empty($region)) {
                    $region = new Region([
                        'region_name' => $row['region']
                    ]);
                    $region->save();
                }

                $classification = Classification::where('classification_name', $row['classification'])
                    ->orWhere('classification_code', $row['classification_code'])
                    ->first();
                if(empty($classification)) {
                    $classification = new Classification([
                        'classification_name' => $row['classification'],
                        'classification_code' => $row['classification_code'],
                    ]);
                    $classification->save();
                }

                $area = Area::where('area_code', $row['area_code'])
                    ->orWhere('area_name', $row['area_name'])
                    ->first();
                if(empty($area)) {
                    $area = new Area([
                        'area_code' => $row['area_code'],
                        'area_name' => $row['area_name']
                    ]);
                    $area->save();
                }

                // check
                $check = Branch::where('branch_code', $row['branch_code'])
                ->where('account_id', $account->id)->first();

                if(empty($check)) {
                    if(!empty($account) && !empty($region) && !empty($classification) && !empty($area)) {
                        $branch = new Branch([
                            'account_id' => $account->id,
                            'region_id' => $region->id,
                            'classification_id' => $classification->id,
                            'area_id' => $area->id,
                            'branch_code' => $row['branch_code'],
                            'branch_name' => $row['branch_name'],
                        ]);
                        $branch->save();

                        $row->delete();
                    } else {
                        $undefined_arr = [];
                        if(empty($account)) {
                            $undefined_arr[] = 'account';
                        }
                        if(empty($region)) {
                            $undefined_arr[] = 'region';
                        }
                        if(empty($classification)) {
                            $undefined_arr[] = 'classification';
                        }
                        if(empty($area)) {
                            $undefined_arr[] = 'area';
                        }

                        $undefined_column = implode(',', $undefined_arr);
                        
                        $row->update([
                            'status' => $undefined_column
                        ]);
                    }
                } else {
                    $row->update([
                        'status' => 'exist'
                    ]);
                }
            }
        });

        return back()->with([
            'message_success' => 'branch has been merged'
        ]);
    }

    public function ajax(Request $request) {
        $search = $request->search;
        $response = Branch::BranchAjax($search);
        return response()->json($response);
    }

    public function export(Request $request) {
        $search = trim($request->get('search'));

        return Excel::download(new BranchExport($search), 'SMS Branch List'.time().'.xlsx');
    }
}
