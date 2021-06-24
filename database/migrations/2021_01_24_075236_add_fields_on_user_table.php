<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsOnUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->after('email');
            $table->longText('address')->after('phone');
            $table->unsignedBigInteger('country_id')->after('address');
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');
            $table->unsignedBigInteger('state_id')->after('country_id')->nullable();
            // $table->foreign('state_id')->references('id')->on('states')->onDelete('cascade');
            $table->string('city')->after('state_id');
            $table->string('zip')->after('city');
            $table->boolean('status')->after('zip')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIfExists('phone');
            $table->dropIfExists('address');
            $table->dropIfExists('country_id');
            $table->dropIfExists('state_id');
            $table->dropIfExists('city');
            $table->dropIfExists('zip');
            $table->dropIfExists('status');
        });
    }
}
