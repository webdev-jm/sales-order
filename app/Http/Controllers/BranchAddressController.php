<?php

namespace App\Http\Controllers;

use App\Models\BranchAddress;
use Illuminate\Http\Request;

use Maatwebsite\Excel\Facades\Excel;
use App\Imports\BranchAddressImport;


class BranchAddressController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $search = trim($request->get("search"));

        $branch_address = BranchAddress::orderBy('created_at', 'DESC')
            ->when(!empty($search), function($query) use ($search) {
                $query->whereHas('branch', function($qry) use ($search) {
                    $qry->where('branch_code', 'LIKE', '%'. $search .'%')
                        ->orWhere('branch_name', 'LIKE', '%'. $search .'%');
                })
                ->orWhere('street1', 'like', '%'. $search .'%')
                ->orWhere('street2', 'like', '%'. $search .'%')
                ->orWhere('city', 'like', '%'. $search .'%')
                ->orWhere('state', 'like', '%'. $search .'%')
                ->orWhere('zip', 'like', '%'. $search .'%')
                ->orWhere('country', 'like', '%'. $search .'%')
                ->orWhere('address', 'like', '%'. $search .'%');
            })
            ->paginate(10)->onEachSide(1)->appends($request->query());

        return view('branch-address.index')->with([
            'search' => $search,
            'branch_address' => $branch_address,
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\BranchAddress  $branchAddress
     * @return \Illuminate\Http\Response
     */
    public function show(BranchAddress $branchAddress)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\BranchAddress  $branchAddress
     * @return \Illuminate\Http\Response
     */
    public function edit(BranchAddress $branchAddress)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\BranchAddress  $branchAddress
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, BranchAddress $branchAddress)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\BranchAddress  $branchAddress
     * @return \Illuminate\Http\Response
     */
    public function destroy(BranchAddress $branchAddress)
    {
        //
    }

    public function upload(Request $request) {
        $request->validate([
            'upload_file' => [
                'mimes:xlsx'
            ]
        ]);

        Excel::import(new BranchAddressImport, $request->upload_file);

        // logs
        activity('upload')
            ->log(':causer.firstname :causer.lastname has uploaded branch address');

        return back()->with([
            'message_success' => 'Branch address has been uploaded.'
        ]);
    }
}
