<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Brand;

class BrandController extends Controller
{
    public function index(Request $request) {
        $search = trim($request->get('search'));
        $brands = Brand::orderBy('id', 'DESC')
            ->when(!empty($search), function($query) {
                $query->where('brand', 'LIKE', '%' . $search . '%');
            })
            ->paginate(10)->onEachSide(1)
            ->appends(request()->query());

        return view('brands.index')->with([
            'search' => $search,
            'brands' => $brands
        ]);
    }
}
