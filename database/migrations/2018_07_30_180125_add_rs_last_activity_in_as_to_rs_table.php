<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRsLastActivityInAsToRsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('as_to_rs', function (Blueprint $table) {
            $table->tinyInteger('rs_last_activity');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('as_to_rs', function (Blueprint $table) {
            $table->dropColumn('rs_last_activity');
        });
    }
}
