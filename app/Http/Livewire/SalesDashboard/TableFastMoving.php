<?php

namespace App\Http\Livewire\SalesDashboard;

use Livewire\Component;
use GuzzleHttp\Client;

class TableFastMoving extends Component
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
            $response = $client->post(env('API_URL').'table-bevi-fast-moving-sku', [
                'headers' => $headers,
                'json' => $data,
            ]);

            if ($response->getStatusCode() == 200) {
                $data = json_decode($response->getBody());
                $this->api_data = collect($data->data);
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
        return view('livewire.sales-dashboard.table-fast-moving');
    }
}
