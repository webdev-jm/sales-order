<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Http\Requests\StoreAreaRequest;
use App\Http\Requests\UpdateAreaRequest;
use Illuminate\Http\Request;

use Maatwebsite\Excel\Facades\Excel;
use App\Imports\AreaImport;

use App\Http\Traits\GlobalTrait;

class AreaController extends Controller
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

        $areas = Area::AreaSearch($search, $this->settings->data_per_page);

        return view('areas.index')->with([
            'search' => $search,
            'areas' => $areas
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('areas.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreAreaRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAreaRequest $request)
    {
        $area = new Area([
            'area_code' => $request->area_code,
            'area_name' => $request->area_name
        ]);
        $area->save();

        // logs
        activity('create')
        ->performedOn($area)
        ->log(':causer.firstname :causer.lastname has created area :subject.area_name');

        return redirect()->route('area.index')->with([
            'message_success' => 'Area '.$area->are_name.' was created.'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Area  $area
     * @return \Illuminate\Http\Response
     */
    public function show(Area $area)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Area  $area
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $area = Area::findOrFail($id);

        return view('areas.edit')->with([
            'area' => $area
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateAreaRequest  $request
     * @param  \App\Models\Area  $area
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAreaRequest $request, $id)
    {
        $area = Area::findOrFail($id);

        $old = $area->getOriginal();

        $area->update([
            'area_code' => $request->area_code,
            'area_name' => $request->area_name
        ]);

        // logs
        activity('update')
        ->performedOn($area)
        ->withProperties([
            'old' => $old,
            'changes' => $area->getChanges()
        ])
        ->log(':causer.firstname :causer.lastname has updated product :subject.region_name .');

        return redirect()->route('area.index')->with([
            'message_success' => 'Area '.$area->area_name.' was updated.'
        ]);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Area  $area
     * @return \Illuminate\Http\Response
     */
    public function destroy(Area $area)
    {
        //
    }

    public function upload(Request $request) {
        $request->validate([
            'upload_file' => [
                'mimes:xlsx'
            ]
        ]);

        Excel::import(new AreaImport, $request->upload_file);

        // logs
        activity('upload')
        ->log(':causer.firstname :causer.lastname has uploaded areas');

        return back()->with([
            'message_success' => 'Areas has been uploaded.'
        ]);
    }
}
