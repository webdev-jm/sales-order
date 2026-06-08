<?php

namespace App\Http\Controllers;

use App\Models\Region;
use App\Http\Requests\StoreRegionRequest;
use App\Http\Requests\UpdateRegionRequest;
use Illuminate\Http\Request;

use App\Imports\RegionImport;
use Maatwebsite\Excel\Facades\Excel;

use App\Http\Traits\GlobalTrait;

class RegionController extends Controller
{
    use GlobalTrait;

    public $settings;

    public function __construct() {
        $this->settings = $this->getSettings();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $search = trim($request->input('search'));

        $regions = Region::RegionSearch($search, $this->settings->data_per_page);

        return view('pages.regions.index')->with([
            'search' => $search,
            'regions' => $regions
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('pages.regions.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreRegionRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreRegionRequest $request)
    {
        $region = new Region([
            'region_name' => $request->region_name
        ]);
        $region->save();

        // logs
        activity('create')
        ->performedOn($region)
        ->log(':causer.firstname :causer.lastname has created region :subject.region_name');

        return redirect()->route('region.index')->with([
            'message_success' => 'Region '.$region->region_name.' was created.'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  Region  $region
     * @return void
     */
    public function show(Region $region)
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
        $region = Region::findOrFail($id);

        return view('pages.regions.edit')->with([
            'region' => $region
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateRegionRequest  $request
     * @param  int|string  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateRegionRequest $request, int|string $id)
    {
        $region = Region::findOrFail($id);

        $old = $region->getOriginal();

        $region->update([
            'region_name' => $request->region_name
        ]);

        // logs
        activity('update')
        ->performedOn($region)
        ->withProperties([
            'old' => $old,
            'changes' => $region->getChanges()
        ])
        ->log(':causer.firstname :causer.lastname has updated product :subject.region_name .');

        return redirect()->route('region.index')->with([
            'message_success' => 'Region '.$region->region_name.' was updated.'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Region  $region
     * @return void
     */
    public function destroy(Region $region)
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

        Excel::import(new RegionImport, $request->upload_file);

        // logs
        activity('upload')
        ->log(':causer.firstname :causer.lastname has uploaded regions');

        return back()->with([
            'message_success' => 'Regions has been uploaded.'
        ]);
    }
}
