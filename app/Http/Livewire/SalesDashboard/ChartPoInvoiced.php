<?php

namespace App\Http\Livewire\SalesDashboard;

use Livewire\Component;
use GuzzleHttp\Client;

class ChartPoInvoiced extends Component
{
    public $month, $chart_data;

    public function fetchData($url, $method = 'post') {
        $headers = [
            'Authorization' => 'Bearer '.env('API_TOKEN'),
            'Accept' => 'application/json', // Adjust the content type as needed
            'Content-Type' => 'application/json', // Set the content type for POST data
        ];

        $data = [
            'month' => $this->month
        ];

        $api_data = array();

        try {
            $client = new Client();
            if(strtolower($method)== 'post') {
                $response = $client->post(env('API_URL').$url, [
                    'headers' => $headers,
                    'json' => $data,
                ]);
            } else if(strtolower($method)== 'get') {
                $response = $client->get(env('API_URL').$url, [
                    'headers' => $headers,
                ]);
            }

            if ($response->getStatusCode() == 200) {
                $data = json_decode($response->getBody());
                $api_data = collect($data);
            }
        } catch(\Exception $e) {
            $api_data = [];
        }

        if(is_array($api_data)) {
            return $api_data;
        } else {
            return $api_data->toArray();
        }
    }

    public function mount() {
        $this->month = date('m');

        $this->chart_data = $this->fetchData('chart-po-invoiced-unserved', 'POST');
    }

    public function render()
    {
        return view('livewire.sales-dashboard.chart-po-invoiced');
    }
}
