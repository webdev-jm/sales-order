<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class PurchaseOrderDetail extends Model
{
    use HasFactory;

    protected $table = 'purchase_order_details';

    public function getConnectionName() {
        $loggedAccount = Session::get('logged_account');
        $cacheKey = 'connection_name_' . $loggedAccount->account_id;

        $data = Cache::remember($cacheKey, 3600, function() use ($loggedAccount) {
            $stoAccount = DB::connection('sto_online_db')
                ->table('accounts')
                ->where('sms_account_id', $loggedAccount->account_id)
                ->first();

            $accountDatabase = DB::connection('sto_online_db')
                ->table('account_databases')
                ->where('account_id', $stoAccount->id)
                ->first();

            $connectionConfig = [
                'driver' => 'mysql',
                'url' => null,
                'host' => config('database.connections.mysql.host'),
                'port' => config('database.connections.mysql.port'),
                'database' => $accountDatabase->database_name,
                'username' => config('database.connections.sto_online_db.username'),
                'password' => config('database.connections.sto_online_db.password'),
                'unix_socket' => '',
                'charset' => 'utf8',
                'collation' => 'utf8_general_ci',
                'prefix' => '',
                'prefix_indexes' => true,
                'strict' => true,
                'engine' => 'InnoDB',
                'pool' => [
                    'min_connections' => 1,
                    'max_connections' => 10,
                    'max_idle_time' => 30,
                ],
            ];

            return [
                'name' => $accountDatabase->connection_name,
                'config' => $connectionConfig
            ];
        });

        Config::set('database.connections.' . $data['name'], $data['config']);

        return $data['name'];
    }
    
    public function purchase_order() {
        return $this->belongsTo('App\Models\PurchaseOrder', 'purchase_order_id', 'id');
    }
}
