<?php

namespace App\Http\Controllers;

use App\Models\Territory;
use App\Models\District;

use App\Http\Requests\StoreTerritoryRequest;
use App\Http\Requests\UpdateTerritoryRequest;

use Illuminate\Http\Request;

use App\Http\Traits\GlobalTrait;

class TerritoryController extends Controller
{
    use GlobalTrait;

    public $setting;

    public function __construct() {
        $this->setting = $this->getSettings();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $search = trim($request->get('search'));
        $territories = Territory::TerritorySearch($search, $this->setting->data_per_page);

        return view('territories.index')->with([
            'territories' => $territories,
            'search' => $search
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('territories.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreTerritoryRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTerritoryRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Territory  $territory
     * @return \Illuminate\Http\Response
     */
    public function show(Territory $territory)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Territory  $territory
     * @return \Illuminate\Http\Response
     */
    public function edit(Territory $territory)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateTerritoryRequest  $request
     * @param  \App\Models\Territory  $territory
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTerritoryRequest $request, Territory $territory)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Territory  $territory
     * @return \Illuminate\Http\Response
     */
    public function destroy(Territory $territory)
    {
        //
    }
}
