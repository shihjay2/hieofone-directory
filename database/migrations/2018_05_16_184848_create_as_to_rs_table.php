<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAsToRsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('as_to_rs', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('as_id');
            $table->string('rs_uri', 255)->nullable();
            $table->string('rs_name', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('as_to_rs');
    }
}
