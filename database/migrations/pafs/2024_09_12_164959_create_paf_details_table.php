<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePafDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('paf_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('paf_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('components')->nullable();
            $table->integer('line')->nullable();
            $table->string('stock_code')->nullable();
            $table->string('brand')->nullable();
            $table->string('category')->nullable();
            $table->timestamps();

            $table->foreign('paf_id')
                ->references('id')->on('pafs')
                ->onDelete('cascade');

            $table->foreign('product_id')
                ->references('id')->on('products')
                ->onDelete('cascade');

            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('paf_details');
    }
}


// ('2023-D-00002','1200008', 'MERCURY DRUG CORPORATION', '20% SUKI POINTS', '2023-04-01','2023-10-31','ANNUAL SUPPORT'),
// ('2023-D-00003',	'1200111', 'MERRYMART GROCERY CENTERS INC.', 'CONSUMER PROMO','2023-04-10','2023-11-30','TMP'),
// ('2023-D-00004',	'1200102', 'TOWER 6789 CORPORATION', 'CONSUMER PROMO','2023-04-10','2023-12-29','TMP'),
// ('2023-D-00005',	'1200116', 'GOLDEN DEW IMPORTATION AND DISTRIBUTION CORP.', 'SSM MAILER # 2','2023-04-15','2023-05-15','TMP'),
// ('2023-D-00008',	'1200077', '"AYAGOLD RETAILERS, INC."', 'EXTRA DISPLAY RENTAL','2023-04-10','2023-12-31','TMP'),
// ('2023-D-00009',	'1200116', 'GOLDEN DEW IMPORTATION AND DISTRIBUTION CORP.', 'SSM MAILER # 3'	,'2023-05-19','2023-06-01','TMP'),
// ('2023-D-00010',	'1200008', 'MERCURY DRUG CORPORATION', 'MST FEE', '2023-04-15','2023-12-31','ANNUAL SUPPORT'),
// ('2023-D-00011',	'1200074', 'ROBINSONS SUPERMARKET CORP.', 'Wow Tacbin - Lotion','2023-04-21','2023-10-05','TMP'),
// ('2023-D-00012',	'1200077', '"AYAGOLD RETAILERS, INC."', 'TNAP CONVENTION','2023-05-01','2023-05-30','TMP'),
// ('2023-D-00013',	'1200074', 'ROBINSONS SUPERMARKET CORP.', 'Wow End Cap - Bath','2023-04-21','2023-10-05','TMP'),
// ('2023-D-00014',	'1200074', 'ROBINSONS SUPERMARKET CORP.', 'Wellness Festival','2023-07-01','2023-07-31','TMP'),
// ('2023-D-00015',	'1200074', 'ROBINSONS SUPERMARKET CORP.', 'SaveBig - Bath','2023-07-15','2023-07-28','TMP'),
// ('2023-D-00016',	'1200074', 'ROBINSONS SUPERMARKET CORP.', 'Easy Treats - Lotion - EM','2023-05-01','2023-05-30','TMP'),
// ('2023-D-00017',	'1200074', 'ROBINSONS SUPERMARKET CORP.', 'THE MARKET PLACE - FRESH PICKS BATH CATEGORY','2023-04-21','2023-05-04','TMP'),
// ('2023-D-00019',	'1200116', 'GOLDEN DEW IMPORTATION AND DISTRIBUTION CORP.', 'SSM MAILER # 4','2023-06-14','2023-06-29','TMP'),
// ('2023-D-00020',	'1200116', 'GOLDEN DEW IMPORTATION AND DISTRIBUTION CORP.', 'ALFAMART FOCUS MAILER # 2','2023-05-01','2023-05-30','TMP'),
// ('2023-D-00021',	'1200116', 'GOLDEN DEW IMPORTATION AND DISTRIBUTION CORP.', 'SVI MAILER # 12','2023-05-19','2023-06-01','TMP'),
// ('2023-D-00022',	'1200116', 'GOLDEN DEW IMPORTATION AND DISTRIBUTION CORP.', 'SVI MAILER # 10','2023-04-21','2023-05-04','TMP'),
// ('2023-D-00023',	'1200106', 'ROBINSONS CONVENIENCE STORE INC.', 'SUMMERIFFIC ACTIVITY','2023-04-17','2023-05-15','TMP'),
// ('2023-D-00029',	'3000051', 'ADVECT MARKETING CORPORATION', 'CRISTALINO SEEDING PROGRAM/ FLUSH OUT SUPPORT FOR SOTEX STOCKS','2023-04-30','2023-06-30','TMP'),
// ('2023-D-00032',	'3000058', 'MAGIS DISTRIBUTION INC.', 'SUY SING SUKI DAY CONVENTION','2023-05-06','2023-05-07','TMP'),
// ('2023-D-00035',	'1200008', 'MERCURY DRUG CORPORATION', 'PORTAL CHARGES','2023-05-11','2023-12-31','ANNUAL SUPPORT'),
// ('2023-D-00039',	'1200023', 'WATSONS PERSONAL CARE STORES (PHILIPPINES) INC.', 'CONSUMER PROMO','2023-05-31','2023-06-30','TMP'),
// ('2023-D-00040',	'3000051', 'ADVECT MARKETING CORPORATION', 'SARI SARI BONANZA','2023-05-26','2023-11-30','TMP'),
// ('2023-D-00041',	'1200008', 'MERCURY DRUG CORPORATION', 'GC PROMO','2023-06-01','2023-12-31','ANNUAL SUPPORT'),
// ('2023-D-00042',	'1200015', 'PHILIPPINE SEVEN CORPORATION', 'NEW STORE OPENING','2023-05-17','2023-12-31','ANNUAL SUPPORT'),
// ('2023-D-00043',	'1200015', 'PHILIPPINE SEVEN CORPORATION', 'HABA FAIR','2023-08-01','2023-09-30','TMP'),
// ('2023-D-00052',	'1200116', 'GOLDEN DEW IMPORTATION AND DISTRIBUTION CORP.', 'WALTERMART MAY MAILER','2023-05-26','2023-06-08','TMP'),
// ('2023-D-00054',	'1200116', 'GOLDEN DEW IMPORTATION AND DISTRIBUTION CORP.', 'WALTERMART JUNE MAILER','2023-06-09','2023-06-22','TMP'),
// ('2023-D-00060',	'1200116', 'GOLDEN DEW IMPORTATION AND DISTRIBUTION CORP.', 'NEW STORE OPENING','2023-06-15','2023-12-31','ANNUAL SUPPORT'),
// ('2023-D-00071',	'3000040', 'SOUTHQUEST DISTRIBUTION INC.', 'CHRISTMAS CALENDAR SUPPORT','2023-10-01','2023-12-31','ANNUAL SUPPORT'),
// ('2023-S-00050',	'1200081', '"PUREGOLD PRICE CLUB, INC."', 'TNAP CONVENTION (UNDER PAF 2023-D-00012','2023-05-17','2023-05-21','TMP'),
// ('2023-S-00072',	'1200023', 'WATSONS PERSONAL CARE STORES (PHILIPPINES) INC.', 'STORE OPENING','2023-06-01','2023-12-31','ANNUAL SUPPORT')
