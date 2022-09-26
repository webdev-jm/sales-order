<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use App\Http\Requests\UpdateSettingRequest;

class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $setting = Setting::find(1);
        return view('settings')->with([
            'setting' => $setting
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Setting  $setting
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateSettingRequest $request, $id)
    {
        $setting = Setting::findOrFail($id);

        $changes_arr['old'] = $setting;

        $setting->update([
            'data_per_page' => $request->data_per_page,
            'sales_order_limit' => $request->sales_order_limit
        ]);

        $changes_arr['changes'] = $setting->getChanges();

        // logs
        activity('update')
        ->performedOn($setting)
        ->withProperties($changes_arr)
        ->log(':causer.firstname :causer.lastname has updated settings');

        return back()->with([
            'message_success' => 'Settings was updated.'
        ]);
    }
}
