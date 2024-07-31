<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\ShippingAddress;
use App\Http\Requests\StoreShippingAddressRequest;
use App\Http\Requests\UpdateShippingAddressRequest;
use Illuminate\Http\Request;

use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ShippingAddressImport;

use App\Http\Traits\GlobalTrait;

class ShippingAddressController extends Controller
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
    public function index(Request $request, $id)
    {
        $search = trim($request->get('search'));
        $account = Account::findOrFail(decrypt($id));

        $shipping_addresses = ShippingAddress::ShippingAddressSearch($search, $account->id, $this->settings->data_per_page);

        return view('shipping-addresses.index')->with([
            'search' => $search,
            'account' => $account,
            'shipping_addresses' => $shipping_addresses
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($id)
    {
        $account = Account::findOrFail(decrypt($id));
        
        return view('shipping-addresses.create')->with([
            'account' => $account
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreShippingAddressRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreShippingAddressRequest $request)
    {
        $shipping_address = new ShippingAddress([
            'account_id' => $request->account_id,
            'address_code' => $request->address_code,
            'ship_to_name' => $request->ship_to_name,
            'building' => $request->building,
            'street' => $request->street,
            'city' => $request->city,
            'tin' => $request->tin,
            'postal' => $request->postal,
        ]);
        $shipping_address->save();

        // logs
        activity('create')
        ->performedOn($shipping_address)
        ->log(':causer.firstname :causer.lastname has created shipping address :subject.address_code');

        return redirect()->route('shipping-address.index', encrypt($shipping_address->account_id))->with([
            'message_success' => 'Shipping address '.$shipping_address->address_code.' was created.'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ShippingAddress  $shippingAddress
     * @return \Illuminate\Http\Response
     */
    public function show(ShippingAddress $shippingAddress)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ShippingAddress  $shippingAddress
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $shipping_address = ShippingAddress::findOrFail(decrypt($id));

        return view('shipping-addresses.edit')->with([
            'shipping_address' => $shipping_address
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateShippingAddressRequest  $request
     * @param  \App\Models\ShippingAddress  $shippingAddress
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateShippingAddressRequest $request, $id)
    {
        $shipping_address = ShippingAddress::findOrFail(decrypt($id));

        $changes_arr['old'] = $shipping_address->getOriginal();

        $shipping_address->update([
            'account_id' => $request->account_id,
            'address_code' => $request->address_code,
            'ship_to_name' => $request->ship_to_name,
            'building' => $request->building,
            'street' => $request->street,
            'city' => $request->city,
            'tin' => $request->tin,
            'postal' => $request->postal,
        ]);

        $changes_arr['changes'] = $shipping_address->getChanges();

        // logs
        activity('update')
        ->performedOn($shipping_address)
        ->withProperties($changes_arr)
        ->log(':causer.firstname :causer.lastname has updated shipping address :subject.address_code .');

        return back()->with([
            'message_success' => 'Shipping address '.$shipping_address->address_code.' was updated.'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ShippingAddress  $shippingAddress
     * @return \Illuminate\Http\Response
     */
    public function destroy(ShippingAddress $shippingAddress)
    {
        //
    }

    public function upload(Request $request) {
        $request->validate([
            'upload_file' => [
                'mimes:xlsx'
            ]
        ]);

        Excel::import(new ShippingAddressImport, $request->upload_file);

        // logs
        activity('upload')
        ->log(':causer.firstname :causer.lastname has uploaded shipping address.');

        return back()->with([
            'message_success' => 'Shipping Addresses has been uploaded.'
        ]);
    }

    public function ajax(Request $request) {
        $search = $request->search;
        $account_id = $request->account_id;
        $response = ShippingAddress::ShippingAddressAjax($search, $account_id);
        return response()->json($response);
    }
}
