<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUmaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('uma', function (Blueprint $table) {
            $table->increments('id');
			$table->string('resource_set_id', 255)->nullable();
			$table->longtext('scope')->nullable();
			$table->longtext('user_access_policy_uri')->nullable();
            $table->bigInteger('as_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('uma');
    }
}
