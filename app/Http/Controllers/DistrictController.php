<?php

namespace App\Http\Controllers;

use App\Models\District;
use App\Http\Requests\StoreDistrictRequest;
use App\Http\Requests\UpdateDistrictRequest;

use Illuminate\Http\Request;

use App\Http\Traits\GlobalTrait;

class DistrictController extends Controller
{
    use GlobalTrait;

    public $setting;

    public function __construct() {
        $this->setting = $this->getSettings();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $search = trim($request->input('search'));

        $districts = District::DistrictSearch($search, $this->setting->data_per_page);

        return view('pages.districts.index')->with([
            'search' => $search,
            'districts' => $districts
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('pages.districts.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreDistrictRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreDistrictRequest $request)
    {
        $district = new District([
            'district_code' => $request->district_code,
            'district_name' => $request->district_name
        ]);
        $district->save();

        // logs
        activity('create')
        ->performedOn($district)
        ->log(':causer.firstname :causer.lastname has created district :subject.district_name');

        return redirect()->route('district.index')->with([
            'message_success' => 'District '.$district->district_name.' was created.'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  District  $district
     * @return void
     */
    public function show(District $district)
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
        $district = District::findOrFail($id);

        return view('pages.districts.edit')->with([
            'district' => $district
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateDistrictRequest  $request
     * @param  int|string  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateDistrictRequest $request, int|string $id)
    {
        $district = District::findOrFail($id);
        $district_name = $district->district_name;

        $changes_arr['old'] = $district->getOriginal();

        $district->update([
            'district_code' => $request->district_code,
            'district_name' => $request->district_name
        ]);

        $changes_arr['changes'] = $district->getChanges();

        // logs
        activity('update')
        ->performedOn($district)
        ->withProperties($changes_arr)
        ->log(':causer.firstname :causer.lastname has updated district :subject.district_name .');

        return back()->with([
            'message_success' => 'District '.$district_name.' was updated.'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  District  $district
     * @return void
     */
    public function destroy(District $district)
    {
        //
    }
}
