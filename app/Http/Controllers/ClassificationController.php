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
        return view('classifications.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreClassificationRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreClassificationRequest $request)
    {
        $classification = new Classification([
            'classification_code' => $request->classification_code,
            'classification_name' => $request->classification_name
        ]);
        $classification->save();

        // logs
        activity('create')
        ->performedOn($classification)
        ->log(':causer.firstname :causer.lastname has created classification :subject.classification_name');

        return redirect()->route('classification.index')->with([
            'message_success' => 'Classification '.$classification->classification_name.' was created.'
        ]);
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
    public function edit($id)
    {
        $classification = Classification::findOrFail($id);

        return view('classifications.edit')->with([
            'classification' => $classification
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateClassificationRequest  $request
     * @param  \App\Models\Classification  $classification
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateClassificationRequest $request, $id)
    {
        $classification = Classification::findOrFail($id);

        $old = $classification->getOriginal();

        $classification->update([
            'classification_code' => $request->classification_code,
            'classification_name' => $request->classification_name
        ]);

        // logs
        activity('update')
        ->performedOn($classification)
        ->withProperties([
            'old' => $old,
            'changes' => $classification->getChanges()
        ])
        ->log(':causer.firstname :causer.lastname has updated product :subject.classification_name .');

        return redirect()->route('classification.index')->with([
            'message_success' => 'Classification '.$classification->classification_name.' was updated.'
        ]);
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
