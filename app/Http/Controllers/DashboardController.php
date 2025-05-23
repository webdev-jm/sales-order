<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;

// use Milon\Barcode\DNS2D;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $d = new DNS2D();

        // // Generate the barcode SVG content
        // $barcodeSVG = $d->getBarcodeSVG('https://sales-order.bevi.ph/public/images/KS%20LIFE/LIFE%20BY%20KOJIESAN%20INFOS-DESKTOP-RENEW+%20(1).jpg', 'QRCODE', 10, 10, 'black');

        // // Define the path where the SVG file will be saved
        // $filePath = 'barcode-renew.svg';

        // // Save the SVG content to a file
        // file_put_contents($filePath, $barcodeSVG);

        if(auth()->user()->can('system logs')) {
            
            $year = date('Y');
            $month = date('n');
            
            $results = DB::table('branch_logins as bl')
                ->select(
                    DB::raw('CONCAT(u.firstname, " ", u.lastname) as name'),
                    'bl.id',
                    'bl.latitude',
                    'bl.longitude',
                    'bl.time_in',
                    'bl.accuracy',
                    DB::raw('CONCAT(a.short_name, " ", b.branch_code, " ", b.branch_name) as branch')
                )
                ->join('users as u', 'u.id', '=', 'bl.user_id')
                ->join('branches as b', 'b.id', '=', 'bl.branch_id')
                ->join('accounts as a', 'a.id', '=', 'b.account_id')
                ->where(DB::raw('YEAR(time_in)'), $year)
                ->where(DB::raw('MONTH(time_in)'), $month)
                ->get();

            $data = [];
            foreach($results as $result) {
                $data[$result->name][] = [
                    $result->id,
                    (float)$result->latitude,
                    (float)$result->longitude,
                    $result->branch.' ['.$result->time_in.'] - '.$result->accuracy,
                    -6
                ];
            }

            $chart_data = [
                [
                    'allAreas' => true,
                    'name' => 'Coasline',
                    'states' => [
                        'inactive' => [
                            'opacity' => 0.2
                        ]
                    ],
                    'dataLabels' => [
                        'enabled' => true,
                        'format' => '{point.name}'
                    ],
                    'enableMouseTracking' => false,
                    'showInLegend' => false,
                    'borderColor' => 'blue',
                    'opacity' => 0.3,
                    'borderWidth' => 10
                ],
                [
                    'allAreas' => true,
                    'name' => 'Countries',
                    'states' => [
                        'inactive' => [
                            'opacity' => 1
                        ]
                    ],
                    'dataLabels' => [
                        'enabled' => false,
                    ],
                    'enableMouseTracking' => false,
                    'showInLegend' => false,
                    'borderColor' => 'rgba(0, 0, 0, 0.25)',
                ],
            ];
            foreach($data as $name => $val) {
                $chart_data[] = [
                    'name' => $name,
                    'data' => $val,
                    'type' => 'mappoint'
                ];
            }

            return view('dashboard')->with([
                'chart_data' => $chart_data
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
