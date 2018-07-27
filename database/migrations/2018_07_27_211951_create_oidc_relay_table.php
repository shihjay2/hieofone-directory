<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOidcRelayTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('oidc_relay', function (Blueprint $table) {
            $table->increments('id');
            $table->string('state', 255)->nullable();
            $table->string('origin_uri', 255)->nullable();
            $table->string('response_uri', 255)->nullable();
            $table->string('fhir_url', 255)->nullable();
            $table->string('fhir_auth_url', 255)->nullable();
            $table->string('fhir_token_url', 255)->nullable();
            $table->string('type', 255)->nullable();
            $table->string('cms_pid', 255)->nullable();
            $table->string('access_token', 255)->nullable();
            $table->string('patient_token', 255)->nullable();
            $table->string('refresh_token', 255)->nullable();
            $table->string('patient', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('oidc_relay');
    }
}
