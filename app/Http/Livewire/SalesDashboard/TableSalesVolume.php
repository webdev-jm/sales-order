<?php

namespace App\Http\Livewire\SalesDashboard;

use Livewire\Component;
use GuzzleHttp\Client;

class TableSalesVolume extends Component
{
    public $api_data;

    public function fetchData() {
        $headers = [
            'Authorization' => 'Bearer '.env('API_TOKEN'),
            'Accept' => 'application/json', // Adjust the content type as needed
            'Content-Type' => 'application/json', // Set the content type for POST data
        ];

        $data = [
            'month' => 9
        ];

        try {
            $client = new Client();
            $response = $client->post('http://127.0.0.1:8000/api/table-bevi-sales-volume', [
                'headers' => $headers,
                'json' => $data,
            ]);

            if ($response->getStatusCode() == 200) {
                $data = json_decode($response->getBody());
                $this->api_data = $data->data;
            }
        } catch(\Exception $e) {
            $this->api_data = [];
            $this->addError('apiData', 'Error fetching data from the API.');
        }
    }

    public function mount() {
        $this->api_data = array();

        $this->fetchData();
    }

    public function render()
    {
        return view('livewire.sales-dashboard.table-sales-volume');
    }
}
