<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $table = 'purchase_orders';
    protected $fillable = [
        'status'
    ];

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
                'host' => env('DB_HOST', '127.0.0.1'),
                'port' => env('DB_PORT', 3306),
                'database' => $accountDatabase->database_name,
                'username' => env('DB_USERNAME_3', 'root'),
                'password' => env('DB_PASSWORD_3', ''),
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

    public function details() {
        return $this->hasMany('App\Models\PurchaseOrderDetail');
    }
}
