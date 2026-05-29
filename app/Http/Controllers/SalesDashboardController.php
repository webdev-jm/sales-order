<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SalesDashboardController extends Controller
{
    public function index() {

        return view('pages.sales-dashboard.index');
    }
}
