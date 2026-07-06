<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ConvertBranchLoginsTextColumnsToUtf8mb4 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE `branch_logins` MODIFY `action_points` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL');
        DB::statement('ALTER TABLE `branch_logins` MODIFY `accuracy` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL');
        DB::statement('ALTER TABLE `branch_logins` MODIFY `time_out_accuracy` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE `branch_logins` MODIFY `action_points` TEXT CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NULL');
        DB::statement('ALTER TABLE `branch_logins` MODIFY `accuracy` TEXT CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL');
        DB::statement('ALTER TABLE `branch_logins` MODIFY `time_out_accuracy` TEXT CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NULL');
    }
}
