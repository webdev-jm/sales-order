<?php

namespace App\Http\Livewire\SalesDashboard;

use Livewire\Component;
use GuzzleHttp\Client;

class Stats extends Component
{
    public $sales, $target, $sales_performance, $time_par, $growth, $export, $rdg, $nkag;
    public $month;

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

        $this->sales = $this->fetchData('stat-sales');
        $this->target = $this->fetchData('stat-target');
        $this->sales_performance = 0;
        if(!empty($this->sales['total_sales']) && !empty($this->target['target'])) {
            $this->sales_performance = ($this->sales['total_sales'] / $this->target['target']) * 100;
        }
        $this->time_par = $this->fetchData('stat-time-par');
        $this->growth = $this->fetchData('stat-growth');
        $this->export = $this->fetchData('stat-export', 'GET');
        $this->rdg = $this->fetchData('stat-rdg', 'GET');
        $this->nkag = $this->fetchData('stat-nka', 'GET');

    }

    public function render()
    {
        return view('livewire.sales-dashboard.stats');
    }
}
