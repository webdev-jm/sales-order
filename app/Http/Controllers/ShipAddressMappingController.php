<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AccountShipAddressMapping;

class ShipAddressMappingController extends Controller
{
    public function index(Request $request) {
        $search = trim($request->get('search'));

        $ship_address_mappings = AccountShipAddressMapping::orderBy('id', 'desc')
            ->paginate(10);

        return view('ship-address-mappings.index')->with([
            'search' => $search,
            'ship_address_mappings' => $ship_address_mappings
        ]);
    }

    public function create() {
        return view('ship-address-mappings.create');
    }

    public function edit($id) {
        $id = decrypt($id);

        $ship_address_mapping = AccountShipAddressMapping::findOrFail($id);

        return view('ship-address-mappings.edit')->with([
            'ship_address_mapping' => $ship_address_mapping
        ]);
    }

}
