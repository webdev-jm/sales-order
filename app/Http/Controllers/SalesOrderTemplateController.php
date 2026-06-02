<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SalesOrderTemplateController extends Controller
{
    public function index() {
        return view('pages.sales-order-templates.index');
    }
}
