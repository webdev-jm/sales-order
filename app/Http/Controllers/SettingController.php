<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use App\Http\Requests\UpdateSettingRequest;

use Maatwebsite\Excel\Facades\Excel;
use App\Imports\PurchaseOrderNumberImport;

use App\Http\Traits\MonthDeadline;

ini_set('memory_limit', '-1');
ini_set('max_execution_time', 0);
ini_set('sqlsrv.ClientBufferMaxKBSize','1000000'); // Setting to 512M
ini_set('pdo_sqlsrv.client_buffer_max_kb_size','1000000');

class SettingController extends Controller
{
    use MonthDeadline;
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $setting = Setting::find(1);

        $date = $this->getDeadlineCount('2023-02-24');
        
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

        $changes_arr['old'] = $setting->getOriginal();

        $setting->update([
            'data_per_page' => $request->data_per_page,
            'sales_order_limit' => $request->sales_order_limit,
            'mcp_deadline' => $request->mcp_deadline
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

    public function upload(Request $request) {
        $request->validate([
            'upload_file' => [
                'mimes:xlsx'
            ]
        ]);

        Excel::import(new PurchaseOrderNumberImport, $request->upload_file);

        // logs
        activity('upload')
        ->log(':causer.firstname :causer.lastname has uploaded po numbers');

        return back()->with([
            'message_success' => 'PO numbers has been uploaded.'
        ]);
    }
}
