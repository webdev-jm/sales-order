<?php

namespace App\Http\Controllers;

use App\Models\Paf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class PafController extends Controller
{
    public $status_arr = [
        'draft' => 'secondary',
        'submitted' => 'warning',
        'approved'  => 'info',
        'approved by brand' => 'primary',
        'cancelled' => 'danger',
        'completed' => 'success',
    ];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        Session::forget('paf_data');

        $pafs = Paf::orderBy('created_at', 'DESC')
            ->paginate(10)->onEachSide(1);

        return view('pafs.index')->with([
            'pafs' => $pafs,
            'status_arr' => $this->status_arr
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
    public function show($id)
    {
        $paf = Paf::findOrFail($id);
        $paf_detail = $paf->paf_details()
            ->paginate(10);

        return view('pafs.show')->with([
            'paf' => $paf,
            'paf_detail' => $paf_detail,
            'status_arr' => $this->status_arr
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Paf  $paf
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $paf = Paf::findOrFail($id);

        return view('pafs.edit')->with([
            'paf' => $paf
        ]);
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
