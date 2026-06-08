<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Http\Requests\StoreAreaRequest;
use App\Http\Requests\UpdateAreaRequest;
use Illuminate\Http\Request;

use Maatwebsite\Excel\Facades\Excel;
use App\Imports\AreaImport;

use App\Http\Traits\GlobalTrait;
use Illuminate\View\View;

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
     * @return View
     */
    public function index(Request $request): View
    {
        $search = trim($request->input('search'));

        $areas = Area::AreaSearch($search, $this->settings->data_per_page);

        return view('pages.areas.index')->with([
            'search' => $search,
            'areas' => $areas
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('pages.areas.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreAreaRequest  $request
     * @return \Illuminate\Http\RedirectResponse
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
     * @param  Area  $area
     * @return void
     */
    public function show(Area $area)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int|string  $id
     * @return \Illuminate\View\View
     */
    public function edit(int|string $id)
    {
        $area = Area::findOrFail($id);

        return view('pages.areas.edit')->with([
            'area' => $area
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateAreaRequest  $request
     * @param  int|string  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateAreaRequest $request, int|string $id)
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
     * @param  Area  $area
     * @return void
     */
    public function destroy(Area $area)
    {
        //
    }

    /**
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
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
