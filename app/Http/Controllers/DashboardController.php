<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use App\Models\UserBranchSchedule;
use App\Models\User;

// use Milon\Barcode\DNS2D;

set_time_limit(300);
class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        // $d = new DNS2D();

        // // Generate the barcode SVG content
        // $barcodeSVG = $d->getBarcodeSVG('https://sales-order.bevi.ph/public/images/KS%20LIFE/LIFE%20BY%20KOJIESAN%20INFOS-DESKTOP-RENEW+%20(1).jpg', 'QRCODE', 10, 10, 'black');

        // // Define the path where the SVG file will be saved
        // $filePath = 'barcode-renew.svg';

        // // Save the SVG content to a file
        // file_put_contents($filePath, $barcodeSVG);

        if(auth()->user()->can('system logs')) {

            $date_from = trim($request->get('date_from'));
            $date_to = trim($request->get('date_to'));
            $user_id = trim($request->get('user_id'));

            $chart_data = [];
            $branch_data = [];
            if(!empty($date_from) || !empty($date_to) || !empty($user_id)) {

                $results = DB::table('branch_logins as bl')
                    ->select(
                        DB::raw('CONCAT(u.firstname, " ", u.lastname) as name'),
                        'bl.id',
                        'bl.latitude',
                        'bl.longitude',
                        'bl.time_in',
                        'bl.accuracy',
                        DB::raw('CONCAT(a.short_name, " ", b.branch_code, " ", b.branch_name) as branch'),
                        'bl.branch_id'
                    )
                    ->join('users as u', 'u.id', '=', 'bl.user_id')
                    ->join('branches as b', 'b.id', '=', 'bl.branch_id')
                    ->join('accounts as a', 'a.id', '=', 'b.account_id')
                    ->when(!empty($date_from), function($query) use($date_from) {
                        $query->where(DB::raw('DATE(time_in)'), '>=', $date_from);
                    })
                    ->when(!empty($date_to), function($query) use($date_to) {
                        $query->where(DB::raw('DATE(time_in)'), '<=', $date_to);
                    })
                    ->when(!empty($user_id), function($query) use($user_id) {
                        $query->where('u.id', $user_id);
                    })
                    ->get();

                foreach($results as $result) {
                    // Actual login marker
                    $chart_data[] = [
                        'lat' => (float)$result->latitude,
                        'lon' => (float)$result->longitude,
                        'z' => (float)str_replace('m', '', $result->accuracy),
                        'time_in' => $result->time_in,
                        'time_out' => $result->time_out,
                        'accuracy' => $result->accuracy,
                        'branch' => $result->branch,
                        'user' => $result->name,
                        'color' => '#ff1100ff', // Blue for actual login
                    ];

                    // Branch address marker
                    // $branch_address = BranchAddress::where('branch_id', $result->branch_id)->first();
                    // if(!empty($branch_address)) {
                    //     $chart_data[] = [
                    //         'lat' => (float)$branch_address->latitude,
                    //         'lon' => (float)$branch_address->longitude,
                    //         'z' => 10,
                    //         'branch' => $result->branch,
                    //         'color' => '#f02c2cff', // Green for branch address
                    //     ];
                    // }
                }

                // get user branch schedules
                $schedules = UserBranchSchedule::with('branch')
                    ->where('source', 'activity-plan')
                    ->when(!empty($date_from), function($query) use($date_from) {
                        $query->where('date', '>=', $date_from);
                    })
                    ->when(!empty($date_to), function($query) use($date_to) {
                        $query->where('date', '<=', $date_to);
                    })
                    ->when(!empty($user_id), function($query) use($user_id) {
                        $query->where('user_id', $user_id);
                    })
                    ->get();

                foreach($schedules as $schedule) {
                    $branch_address = $schedule->branch->addresses->first();
                    if(!empty($branch_address)) {
                        $branch_data[] = [
                            'lat' => (float)$branch_address->latitude,
                            'lon' => (float)$branch_address->longitude,
                            'name' => $schedule->branch->branch_code.' '.$schedule->branch->branch_name,
                        ];
                    }
                }
            }

            $users = User::orderBy('firstname', 'ASC')
                ->whereHas('branch_logins')
                ->get();

            return view('dashboard')->with([
                'chart_data' => $chart_data,
                'branch_data' => $branch_data,
                'date_from' => $date_from,
                'date_to' => $date_to,
                'user_id' => $user_id,
                'users' => $users,
            ]);
        } else {
            return view('dashboard');
        }
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
