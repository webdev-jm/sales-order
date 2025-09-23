<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Brand;
use App\Http\Requests\StoreBrandRequest;
use App\Http\Requests\UpdateBrandRequest;

class BrandController extends Controller
{
    public function index(Request $request) {
        $search = trim($request->get('search'));
        $brands = Brand::orderBy('id', 'DESC')
            ->when(!empty($search), function($query) use($search) {
                $query->where('brand', 'LIKE', '%' . $search . '%');
            })
            ->paginate(10)->onEachSide(1)
            ->appends(request()->query());

        return view('brands.index')->with([
            'search' => $search,
            'brands' => $brands
        ]);
    }

    public function create() {
        return view('brands.create');
    }

    public function store(StoreBrandRequest $request) {
        $brand = new Brand([
            'brand' => $request->brand,
        ]);
        $brand->save();

        // logs
        activity('create')
            ->performedOn($brand)
            ->log(':causer.firstname :causer.lastname has created brand :subject.brand');

        return redirect()->route('brand.index')->with([
            'message_success' => 'Brand '.$brand->brand.' has been created successfully.'
        ]);
    }

    public function show($id) {
        $brand = Brand::findOrFail($id);

        return view('brands.show')->with([
            'brand' => $brand
        ]);
    }

    public function edit($id) {
        $brand = Brand::findOrFail($id);

        return view('brands.edit')->with([
            'brand' => $brand
        ]);
    }

    public function update(UpdateBrandRequest $request, $id) {
        $brand = Brand::findOrFail($id);

        $changes_arr['old'] = $brand->getOriginal();

        $brand->update([
            'brand' => $request->brand,
        ]);

        $changes_arr['changes'] = $brand->getChanges();

        // logs
        activity('update')
            ->performedOn($brand)
            ->withProperties($changes_arr)
            ->log(':causer.firstname :causer.lastname has updated brand :subject.brand .');

        return back()->with([
            'message_success' => 'Brand '.$brand->brand.' has been updated successfully.'
        ]);
    }
}
