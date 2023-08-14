<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        // Your XML generation logic here
        // $data = [
        //     'Orders' => [
        //         'OrderHeader' => [
        //             'CustomerPoNumber' => '0050787318-00-2',
        //             'Customer' => '1200081',
        //             'OrderDate' => '07/24/2023',
        //             'ShippingInstrs' => '',
        //             'RequestedShipDate' => '07/28/2023',
        //             'OrderComments' => 'SO-20230724-036',
        //             'AddrCode' => '1028',
        //             'Warehouse' => '',
        //         ],
        //         'OrderDetails' => [
        //             'StockLine' => [
        //                 [
        //                     'CustomerPoLine' => 1,
        //                     'StockCode' => 'KS02008',
        //                     'OrderQty' => 2,
        //                     'OrderUom' => 'CS',
        //                     'PriceUom' => 'CS',
        //                 ],
        //                 [
        //                     'CustomerPoLine' => 2,
        //                     'StockCode' => 'KS03004',
        //                     'OrderQty' => 3,
        //                     'OrderUom' => 'CS',
        //                     'PriceUom' => 'CS',
        //                 ],
        //                 [
        //                     'CustomerPoLine' => 3,
        //                     'StockCode' => 'KS03005',
        //                     'OrderQty' => 2,
        //                     'OrderUom' => 'CS',
        //                     'PriceUom' => 'CS',
        //                 ],
        //             ],
        //         ],
        //     ],
        // ];

        // $xml = $this->arrayToXml($data);

        // // Save the XML to the storage disk (e.g., 'public', 'local', etc.)
        // Storage::disk('public')->put('example'.time().'.xml', $xml);

        // return 'example.xml file created successfully.';

        if(auth()->user()->can('system logs')) {
            
            $results = DB::table('branch_logins as bl')
                ->select(
                    DB::raw('CONCAT(u.firstname, " ", u.lastname) as name'),
                    'bl.id',
                    'bl.latitude',
                    'bl.longitude',
                    DB::raw('CONCAT(a.short_name, " ", b.branch_code, " ", b.branch_name) as branch')
                )
                ->join('users as u', 'u.id', '=', 'bl.user_id')
                ->join('branches as b', 'b.id', '=', 'bl.branch_id')
                ->join('accounts as a', 'a.id', '=', 'b.account_id')
                ->where(DB::raw('YEAR(time_in)'), 2023)
                ->where(DB::raw('MONTH(time_in)'), 5)
                ->get();

            $data = [];
            foreach($results as $result) {
                $data[$result->name][] = [
                    $result->id,
                    (float)$result->latitude,
                    (float)$result->longitude,
                    $result->branch,
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

    private function arrayToXml($data)
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><SalesOrders xmlns:xsd="http://www.w3.org/2001/XMLSchema-instance" xsd:noNamespaceSchemaLocation="SORTOIDOC.XSD"></SalesOrders>');
        $this->arrayToXmlHelper($data, $xml);
        return $xml->asXML();
    }

    private function arrayToXmlHelper($data, &$xml)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if ($key === 'StockLine') {
                    // Special handling for repeated 'StockLine' key within 'OrderDetails'
                    foreach ($value as $item) {
                        $subNode = $xml->addChild($key);
                        $this->arrayToXmlHelper($item, $subNode);
                    }
                } else {
                    $subNode = $xml->addChild($key);
                    $this->arrayToXmlHelper($value, $subNode);
                }
            } else {
                $xml->addChild("$key", htmlspecialchars("$value"));
            }
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
