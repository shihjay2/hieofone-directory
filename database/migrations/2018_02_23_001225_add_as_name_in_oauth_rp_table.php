<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAsNameInOauthRpTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('oauth_rp', function (Blueprint $table) {
            $table->string('as_name', 255)->nullable();
            $table->string('picture', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('oauth_rp', function (Blueprint $table) {
            $table->dropColumn('as_name');
            $table->dropColumn('picture');
        });
    }
}
