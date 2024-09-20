<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Brand;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $brands = [
            'BELLIC',
            'DEFENSIL',
            'DANARRA',
            'DREAMWHITE',
            'BARE SOAP',
            'MEN PRODUCTS',
            'KOJIESAN',
            'SCENT THERAPY',
            'PUREGANICS',
            'SUNBLOCK',
            'TOP2TAIL',
            'CRISTALINO SPRING',
            'CHARMZ',
            'KONTUR',
            'LOLITA',
            'PREMIUM PRODUCTS',
            'SUN PROTECT',
            'LIFE',
            'SCRAP',
            'PREMIUM',
            'BEAUTY PRIME',
            'OTHER',
            'BANAHAW PURIFIED',
        ];

        foreach($brands as $name) {
            $brand = new Brand([
                'brand' => $name
            ]);
            $brand->save();
        }
    }
}
