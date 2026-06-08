<?php

namespace App\Http\Controllers;

use App\Models\Territory;
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
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $search = trim($request->input('search'));
        $territories = Territory::TerritorySearch($search, $this->setting->data_per_page);

        return view('pages.territories.index')->with([
            'territories' => $territories,
            'search' => $search
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('pages.territories.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreTerritoryRequest  $request
     * @return void
     */
    public function store(StoreTerritoryRequest $request): void
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  Territory  $territory
     * @return void
     */
    public function show(Territory $territory): void
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Territory  $territory
     * @return void
     */
    public function edit(Territory $territory): void
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateTerritoryRequest  $request
     * @param  Territory  $territory
     * @return void
     */
    public function update(UpdateTerritoryRequest $request, Territory $territory): void
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Territory  $territory
     * @return void
     */
    public function destroy(Territory $territory): void
    {
        //
    }
}
