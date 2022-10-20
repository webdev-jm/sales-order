<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\SalesOrder;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $search = trim($request->get('search'));

        if($search != '') {
            $accounts = Account::orderBy('account_code', 'ASC')
            ->where('account_code', 'like', '%'.$search.'%')
            ->orWhere('account_name', 'like', '%'.$search.'%')
            ->orWhere('short_name', 'like', '%'.$search.'%')
            ->paginate(12)->onEachSide(1);
        } else {
            $accounts = Account::orderBy('account_code', 'ASC')
            ->paginate(12)->onEachSide(1);
        }

        $count_data = [];
        foreach($accounts as $account) {
            $count = SalesOrder::whereHas('account_login', function($query) use ($account) {
                $query->where('account_id', $account->id);
            })->count();

            $count_data[$account->id] = $count;
        }

        return view('dashboard')->with([
            'accounts' => $accounts,
            'count_data' => $count_data,
            'search' => $search,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
