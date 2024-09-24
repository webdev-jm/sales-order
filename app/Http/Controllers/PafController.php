<?php

namespace App\Http\Controllers;

use App\Models\Paf;
use Illuminate\Http\Request;

class PafController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $pafs = Paf::orderBy('created_at', 'DESC')
            ->paginate(10)->onEachSide(1);

        return view('pafs.index')->with([
            'pafs' => $pafs
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('pafs.create');
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
     * @param  \App\Models\Paf  $paf
     * @return \Illuminate\Http\Response
     */
    public function show(Paf $paf)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Paf  $paf
     * @return \Illuminate\Http\Response
     */
    public function edit(Paf $paf)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Paf  $paf
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Paf $paf)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Paf  $paf
     * @return \Illuminate\Http\Response
     */
    public function destroy(Paf $paf)
    {
        //
    }
}
