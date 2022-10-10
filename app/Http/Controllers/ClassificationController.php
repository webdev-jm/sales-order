<?php

namespace App\Http\Controllers;

use App\Models\Classification;
use App\Http\Requests\StoreClassificationRequest;
use App\Http\Requests\UpdateClassificationRequest;

use Illuminate\Http\Request;

use App\Imports\ClassificationImport;
use Maatwebsite\Excel\Facades\Excel;

use App\Http\Traits\GlobalTrait;

class ClassificationController extends Controller
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

        $classifications = Classification::ClassificationSearch($search, $this->settings->data_per_page);

        return view('classifications.index')->with([
            'search' => $search,
            'classifications' => $classifications
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
     * @param  \App\Http\Requests\StoreClassificationRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreClassificationRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Classification  $classification
     * @return \Illuminate\Http\Response
     */
    public function show(Classification $classification)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Classification  $classification
     * @return \Illuminate\Http\Response
     */
    public function edit(Classification $classification)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateClassificationRequest  $request
     * @param  \App\Models\Classification  $classification
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateClassificationRequest $request, Classification $classification)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Classification  $classification
     * @return \Illuminate\Http\Response
     */
    public function destroy(Classification $classification)
    {
        
    }

    public function upload(Request $request) {
        $request->validate([
            'upload_file' => [
                'mimes:xlsx'
            ]
        ]);

        Excel::import(new ClassificationImport, $request->upload_file);

        // logs
        activity('upload')
        ->log(':causer.firstname :causer.lastname has uploaded classifications');

        return back()->with([
            'message_success' => 'Classifications has been uploaded.'
        ]);
    }
}
