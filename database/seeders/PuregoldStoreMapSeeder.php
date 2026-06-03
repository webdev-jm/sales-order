<?php

namespace Database\Seeders;

use App\Models\PuregoldStoreMap;
use Illuminate\Database\Seeder;

class PuregoldStoreMapSeeder extends Seeder
{
    public function run(): void
    {
        $maps = [
            ['store_code' => '1034', 'bevi_code' => '1034'],
            ['store_code' => '1031', 'bevi_code' => '01031'],
            ['store_code' => '1032', 'bevi_code' => '1032'],
            ['store_code' => '1021', 'bevi_code' => '01021'],
            ['store_code' => '1033', 'bevi_code' => '01033'],
            ['store_code' => '1026', 'bevi_code' => '01026'],
            ['store_code' => '1029', 'bevi_code' => '1029'],
            ['store_code' => '1035', 'bevi_code' => '1035'],
            ['store_code' => '1025', 'bevi_code' => '01025'],
            ['store_code' => '1028', 'bevi_code' => '1028'],
            ['store_code' => '1027', 'bevi_code' => '1027'],
            ['store_code' => '1037', 'bevi_code' => '01037'],
            ['store_code' => '1036', 'bevi_code' => '01036'],
            ['store_code' => '1039', 'bevi_code' => '01039'],
            ['store_code' => '1038', 'bevi_code' => '01038'],
            ['store_code' => '1041', 'bevi_code' => '01041'],
            ['store_code' => '1042', 'bevi_code' => '01042'],
            ['store_code' => '1043', 'bevi_code' => '01043'],
            ['store_code' => '1044', 'bevi_code' => '1044'],
            ['store_code' => '1052', 'bevi_code' => '1052'],
            ['store_code' => '1053', 'bevi_code' => '1053'],
            ['store_code' => '1051', 'bevi_code' => '1051'],
            ['store_code' => '1050', 'bevi_code' => '1050'],
            ['store_code' => '1049', 'bevi_code' => '1049'],
            ['store_code' => '1057', 'bevi_code' => '1057'],
            ['store_code' => '1059', 'bevi_code' => '1059'],
            ['store_code' => '1060', 'bevi_code' => '1060'],
        ];

        foreach ($maps as $map) {
            PuregoldStoreMap::firstOrCreate(['store_code' => $map['store_code']], $map);
        }
    }
}
